<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'article_product',
        'size_product',
        'color_product',
        'exists_product'
    ];
}
