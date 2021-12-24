<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];
    use HasFactory;

    public function product()
    {
        return $this->hasMany('App\Models\Product');
    }
    public function product_category()
    {
        return $this->hasMany(Category::class, 'parent_id','id');
    }
    public function product_sub()
    {
        return $this->hasMany(Category::class, 'id','parent_id');
        // return $this->belongsToMany(Category::class, 'id','category_id');
    }
}
