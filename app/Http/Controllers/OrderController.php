<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request, Listing $listing)
    {
        $order = DB::transaction(function () use ($listing, $request) {
            $listing = Listing::whereKey($listing->id)->lockForUpdate()->firstOrFail();
            if ($listing->user_id === $request->user()->id || $listing->status !== 'active' || $listing->stock < 1) {
                throw ValidationException::withMessages(['order' => 'Невозможно оформить этот заказ.']);
            }
            $listing->decrement('stock');
            return Order::create(['listing_id' => $listing->id, 'buyer_id' => $request->user()->id, 'seller_id' => $listing->user_id, 'price' => $listing->price, 'status' => 'pending_payment']);
        });
        return redirect('/orders/'.$order->id.'/checkout')->with('success', 'Заказ создан. Выберите результат тестовой оплаты.');
    }

    public function checkout(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);
        abort_unless($order->status === 'pending_payment', 409);
        return view('app', ['page' => 'checkout', 'payload' => ['order' => $order->load('listing')]]);
    }

    public function pay(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);
        $data = $request->validate(['outcome' => 'required|in:success,decline']);
        DB::transaction(function () use ($order, $data) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'pending_payment', 409);
            $locked->payments()->create(['reference' => (string) Str::uuid(), 'kind' => $data['outcome'] === 'success' ? 'charge' : 'decline', 'amount' => $locked->price]);
            if ($data['outcome'] === 'success') $locked->update(['status' => 'awaiting_delivery']);
        });
        return $data['outcome'] === 'success'
            ? redirect('/dashboard')->with('success', 'Тестовая оплата подтверждена. Реальные деньги не списаны.')
            : back()->withErrors(['payment' => 'Тестовая оплата отклонена. Попробуйте ещё раз или отмените заказ.']);
    }

    public function deliver(Request $request, Order $order)
    {
        abort_unless($order->seller_id === $request->user()->id, 403);
        $data = $request->validate(['delivery_note' => 'required|string|max:10000']);
        $changed = Order::whereKey($order->id)->where('status', 'awaiting_delivery')->update($data + ['status' => 'delivered', 'delivered_at' => now()]);
        abort_unless($changed, 409);
        return back()->with('success', 'Заказ передан покупателю');
    }

    public function complete(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);
        abort_unless(Order::whereKey($order->id)->where('status', 'delivered')->update(['status' => 'completed', 'completed_at' => now()]), 409);
        return back()->with('success', 'Заказ подтверждён');
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);
        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($locked->status, ['pending_payment', 'awaiting_delivery'], true), 409);
            if ($locked->status === 'awaiting_delivery') {
                $locked->payments()->create(['reference' => (string) Str::uuid(), 'kind' => 'refund', 'amount' => $locked->price]);
            }
            $locked->update(['status' => 'cancelled']);
            Listing::whereKey($locked->listing_id)->increment('stock');
        });
        return back()->with('success', 'Заказ отменён');
    }

    public function review(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id && $order->status === 'completed', 403);
        $data = $request->validate(['rating' => 'required|integer|between:1,5', 'body' => 'nullable|string|max:3000']);
        $order->review()->firstOrCreate([], $data + ['author_id' => $request->user()->id, 'seller_id' => $order->seller_id]);
        return back()->with('success', 'Отзыв сохранён');
    }

    public function dispute(Request $request, Order $order)
    {
        abort_unless(in_array($request->user()->id, [$order->buyer_id, $order->seller_id], true) && in_array($order->status, ['awaiting_delivery', 'delivered'], true), 403);
        $data = $request->validate(['reason' => 'required|string|max:5000']);
        DB::transaction(function () use ($order, $request, $data) {
            abort_unless(Order::whereKey($order->id)->whereIn('status', ['awaiting_delivery', 'delivered'])->update(['status' => 'disputed']), 409);
            $order->dispute()->create($data + ['opened_by' => $request->user()->id]);
        });
        return back()->with('success', 'Спор открыт');
    }
}
