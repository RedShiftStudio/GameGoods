<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['sender_id', 'recipient_id', 'order_id', 'body', 'read_at'];
    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
}
