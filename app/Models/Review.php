<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['order_id', 'author_id', 'seller_id', 'rating', 'body'];
    public function author() { return $this->belongsTo(User::class, 'author_id'); }
}
