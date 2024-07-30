<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'model', 'category_id', 'brand_id', 'purchase_price', 'sale_price', 'low_level', 'status'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            $productCount = Product::withTrashed()->count();
            $newCode = str_pad($productCount + 1, 5, '0', STR_PAD_LEFT);

            // Check if code is unique, if not generate a random unique code
            if (Product::where('code', $newCode)->exists()) {
                do {
                    $newCode = mt_rand(10000, 99999);
                } while (Product::where('code', $newCode)->exists());
            }

            $product->code = $newCode;
        });
    }
}
