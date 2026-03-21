<?php

namespace App\Models;
use App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'item_name',
        'cate_name',
        'description',
        'status',
    ];
    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->update_at = null;
        });
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
