<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $fillable = ['name', 'email', 'password', 'bio'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed', 'is_admin' => 'boolean', 'is_banned' => 'boolean']; }
    public function listings() { return $this->hasMany(Listing::class); }
    public function reviews() { return $this->hasMany(Review::class, 'seller_id'); }
    public function favorites() { return $this->belongsToMany(Listing::class, 'favorites'); }
}
