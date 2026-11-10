<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = ['order_id', 'opened_by', 'reason', 'resolution', 'status'];
    public function order() { return $this->belongsTo(Order::class); }
}
