<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Dispute;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        return view('app', ['page' => 'admin', 'payload' => [
            'users' => User::latest()->limit(100)->get(), 'listings' => Listing::with('user')->latest()->limit(100)->get(),
            'disputes' => Dispute::with(['order.buyer', 'order.seller'])->latest()->limit(100)->get(),
            'categories' => Category::orderBy('name')->get(),
        ]]);
    }

    public function category(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'slug' => 'required|alpha_dash|max:255|unique:categories,slug', 'parent_id' => 'nullable|exists:categories,id']);
        Category::create($data); return back()->with('success', 'Категория добавлена');
    }

    public function ban(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403);
        $user->update(['is_banned' => !$user->is_banned]);
        return back()->with('success', 'Статус пользователя изменён');
    }

    public function listing(Request $request, Listing $listing)
    {
        $data = $request->validate(['status' => 'required|in:active,paused,hidden']);
        $listing->update($data); return back()->with('success', 'Объявление модерировано');
    }

    public function resolve(Request $request, Dispute $dispute)
    {
        $data = $request->validate(['resolution' => 'required|string|max:5000', 'decision' => 'required|in:completed,cancelled']);
        DB::transaction(function () use ($dispute, $data) {
            $dispute = Dispute::whereKey($dispute->id)->lockForUpdate()->firstOrFail();
            abort_unless($dispute->status === 'open', 409);
            $order = Order::whereKey($dispute->order_id)->lockForUpdate()->firstOrFail();
            $dispute->update(['status' => 'resolved', 'resolution' => $data['resolution']]);
            $order->update(['status' => $data['decision'], 'completed_at' => $data['decision'] === 'completed' ? now() : null]);
            if ($data['decision'] === 'cancelled') {
                $order->payments()->create(['reference' => (string) Str::uuid(), 'kind' => 'refund', 'amount' => $order->price]);
                Listing::whereKey($order->listing_id)->increment('stock');
            }
        });
        return back()->with('success', 'Спор разрешён (финансовых операций нет)');
    }
}
