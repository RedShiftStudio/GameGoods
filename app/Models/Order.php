<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['listing_id', 'buyer_id', 'seller_id', 'price', 'status', 'delivery_note', 'delivered_at', 'completed_at'];
    protected function casts(): array { return ['delivered_at' => 'datetime', 'completed_at' => 'datetime']; }
    public function listing() { return $this->belongsTo(Listing::class); }
    public function buyer() { return $this->belongsTo(User::class, 'buyer_id'); }
    public function seller() { return $this->belongsTo(User::class, 'seller_id'); }
    public function dispute() { return $this->hasOne(Dispute::class); }
    public function review() { return $this->hasOne(Review::class); }
    public function payments() { return $this->hasMany(DemoPayment::class); }
}
