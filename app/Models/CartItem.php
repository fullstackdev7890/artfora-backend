<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Artel\Support\Traits\ModelTrait;
class CartItem extends Model
{
    use HasFactory;
    use ModelTrait;
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    
}
