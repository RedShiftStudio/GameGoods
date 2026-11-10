<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function inbox(Request $request)
    {
        $id = $request->user()->id;
        $peerIds = Message::where('sender_id', $id)->pluck('recipient_id')
            ->merge(Message::where('recipient_id', $id)->pluck('sender_id'))->unique()->values();
        $peers = User::whereIn('id', $peerIds)->orderBy('name')->get();
        return view('app', ['page' => 'inbox', 'payload' => ['peers' => $peers]]);
    }

    public function index(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id || $user->is_banned, 404);
        $id = $request->user()->id;
        Message::where('sender_id', $user->id)->where('recipient_id', $id)->whereNull('read_at')->update(['read_at' => now()]);
        $messages = Message::where(fn ($q) => $q->where('sender_id', $id)->where('recipient_id', $user->id))
            ->orWhere(fn ($q) => $q->where('sender_id', $user->id)->where('recipient_id', $id))->orderByDesc('id')->limit(100)->get()->reverse()->values();
        return view('app', ['page' => 'messages', 'payload' => ['peer' => $user, 'messages' => $messages]]);
    }

    public function store(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id || $user->is_banned, 404);
        $data = $request->validate(['body' => 'required|string|max:5000', 'order_id' => 'nullable|integer|exists:orders,id']);
        if (!empty($data['order_id'])) {
            $order = Order::findOrFail($data['order_id']);
            abort_unless(($order->buyer_id === $request->user()->id && $order->seller_id === $user->id) || ($order->seller_id === $request->user()->id && $order->buyer_id === $user->id), 403);
        }
        Message::create($data + ['sender_id' => $request->user()->id, 'recipient_id' => $user->id]);
        return back()->with('success', 'Сообщение отправлено');
    }
}
