<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Artel\Support\Traits\ModelTrait;

class SellerSubscription extends Model
{
    use HasFactory;
    use ModelTrait;
    protected $fillable = [
        "seller_id",
        "subscription_id",
        "price_id",
        "stripe_status",
        "start_date",
        "end_date",
        "created_at",
        "updated_at",    
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }
}
