<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'showroom_id', 'date', 'name', 'contact_person', 
        'mobile', 'address', 'initial_balance', 'status',
    ];
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            do {
                $maxCode = static::withTrashed()->max('code');
                $numericPart = $maxCode ? (int)str_replace('SI-', '', $maxCode) : 0;
                $nextCode = 'SI-' . str_pad($numericPart + 1, 4, '0', STR_PAD_LEFT);
            } while (static::withTrashed()->where('code', $nextCode)->exists());

            $supplier->code = $nextCode;
        });
    }
}
