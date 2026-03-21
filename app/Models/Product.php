<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
class Product extends Model
{
    protected $fillable = [
        'category_id',
        'product_name',
        'quantity',
        'image',
        'purchase_price',
        'sale_price',
        'currency',
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
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
