<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Showroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_code',
        'manager',
        'mobile',
        'mobile_two',
        'address',
        'location',
    ];
}
