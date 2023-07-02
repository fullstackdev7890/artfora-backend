<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Artel\Support\Traits\ModelTrait;

class OrderItem extends Model
{
    use HasFactory;
    use ModelTrait;

    protected $fillable = [
        "order_id", 
        "prod_title",
        "prod_artist",
        "prod_height",
        "prod_width",
        "prod_depth",
        "prod_weight",
        "prod_colour",
        "quantity", 
        "price",
        'prod_id'
    ];
}
