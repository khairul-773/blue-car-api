<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partytransaction extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'transaction_at',
        'showroom_id',
        'party_code',
        'relation',
        'credit',
        'debit',
        'commission',
        'transaction_method',
        'transaction_type',
        'remark',
        'transaction_by',
        'paid_by',
        'status',
    ];

    public function party()
    {
        return $this->belongsTo(Supplier::class, 'party_code', 'code');
    }

    public function showroom()
    {
        return $this->belongsTo(Showroom::class, 'showroom_id');
    }
}
