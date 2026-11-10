<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'parent_id'];
    public function listings() { return $this->hasMany(Listing::class); }
    public function children() { return $this->hasMany(self::class, 'parent_id'); }
}
