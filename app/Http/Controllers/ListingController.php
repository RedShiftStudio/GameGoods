<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    private function fields(Request $request): array
    {
        return $request->validate([
            'category_id' => 'required|exists:categories,id', 'title' => 'required|string|max:255',
            'description' => 'required|string|max:20000', 'price' => 'required|numeric|min:0.01|max:9999999999',
            'game' => 'nullable|string|max:100', 'kind' => 'required|in:item,account,currency,service,other',
            'stock' => 'required|integer|min:0|max:1000000', 'image' => 'nullable|image|max:5120',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->fields($request);
        if ($request->hasFile('image')) $data['image'] = $request->file('image')->store('listings', 'public');
        else unset($data['image']);
        $listing = $request->user()->listings()->create($data);
        return redirect('/listings/'.$listing->id)->with('success', 'Объявление опубликовано');
    }

    public function update(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);
        $data = $this->fields($request);
        if ($request->hasFile('image')) {
            if ($listing->image) \Illuminate\Support\Facades\Storage::disk('public')->delete($listing->image);
            $data['image'] = $request->file('image')->store('listings', 'public');
        } else unset($data['image']);
        $listing->update($data);
        return back()->with('success', 'Объявление обновлено');
    }

    public function status(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);
        $data = $request->validate(['status' => 'required|in:active,paused']);
        $listing->update($data);
        return back()->with('success', 'Статус изменён');
    }

    public function favorite(Request $request, Listing $listing)
    {
        $request->user()->favorites()->toggle($listing->id);
        return back()->with('success', 'Избранное обновлено');
    }
}
