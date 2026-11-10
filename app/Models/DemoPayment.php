<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoPayment extends Model
{
    protected $fillable = ['reference', 'kind', 'amount'];
    public function order() { return $this->belongsTo(Order::class); }
}
