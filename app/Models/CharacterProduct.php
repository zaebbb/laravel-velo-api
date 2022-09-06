<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacterProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'description_product',
        'instruction_product',
        'technology_product',
        'brand_info_product'
    ];
}
