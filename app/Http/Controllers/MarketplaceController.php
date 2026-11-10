<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::with(['user', 'category'])->where('status', 'active')->where('stock', '>', 0);
        if ($request->filled('q')) $query->where(fn ($q) => $q->where('title', 'like', '%'.addcslashes($request->q, '%_\\').'%')->orWhere('game', 'like', '%'.addcslashes($request->q, '%_\\').'%'));
        if ($request->filled('category')) $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        if ($request->filled('kind')) $query->where('kind', $request->kind);
        if ($request->filled('min')) $query->where('price', '>=', max(0, (float) $request->min));
        if ($request->filled('max')) $query->where('price', '<=', max(0, (float) $request->max));
        $sort = ['cheap' => ['price', 'asc'], 'expensive' => ['price', 'desc'], 'oldest' => ['created_at', 'asc']][$request->query('sort')] ?? ['created_at', 'desc'];
        return view('app', ['page' => 'catalog', 'payload' => ['listings' => $query->orderBy(...$sort)->paginate(18)->withQueryString(), 'categories' => Category::orderBy('name')->get()]]);
    }

    public function listing(Listing $listing)
    {
        abort_unless($listing->status === 'active' || auth()->id() === $listing->user_id || auth()->user()?->is_admin, 404);
        return view('app', ['page' => 'listing', 'payload' => ['listing' => $listing->load(['user', 'category']), 'reviews' => $listing->user->reviews()->with('author')->latest()->limit(20)->get()]]);
    }

    public function seller(User $user)
    {
        return view('app', ['page' => 'seller', 'payload' => ['seller' => $user, 'listings' => $user->listings()->where('status', 'active')->latest()->paginate(18), 'reviews' => $user->reviews()->with('author')->latest()->limit(30)->get()]]);
    }

    public function dashboard(Request $request)
    {
        $u = $request->user();
        return view('app', ['page' => 'dashboard', 'payload' => [
            'listings' => $u->listings()->latest()->get(),
            'orders' => \App\Models\Order::with(['listing', 'buyer', 'seller', 'dispute', 'review', 'payments'])->where(fn ($q) => $q->where('buyer_id', $u->id)->orWhere('seller_id', $u->id))->latest()->get(),
            'favorites' => $u->favorites()->with('user')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]]);
    }
}
