<?php

namespace App\Models;

use Artel\Support\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use ModelTrait, SoftDeletes;

    const SEARCH_QUERY_FIELDS = [
        'author', 'title', 'description', 'tags', 'user.username', 'user.tagname'
    ];

    const APPROVED_STATUS = 'Approved';
    const REJECTED_STATUS = 'Rejected';
    const PENDING_STATUS = 'Pending';

    const COMMON_VISIBILITY_LEVEL = 1;
    const NUDITY_VISIBILITY_LEVEL = 2;
    const EROTIC_VISIBILITY_LEVEL = 3;
    const PORNO_VISIBILITY_LEVEL = 4;

    const STATUSES = [
        self::APPROVED_STATUS,
        self::REJECTED_STATUS,
        self::PENDING_STATUS,
    ];

    const VISIBILITY_LEVELS = [
        self::COMMON_VISIBILITY_LEVEL,
        self::NUDITY_VISIBILITY_LEVEL,
        self::EROTIC_VISIBILITY_LEVEL,
        self::PORNO_VISIBILITY_LEVEL,
    ];

    protected $fillable = [
        'width',
        'height',
        'depth',
        'price_in_euro',
        'shipping_in_euro',
        
        'price',
        'user_id',
        'category_id',
        'weight',
        'author',
        'title',
        'slug',
        'description',
        'status',
        'tags',
        'visibility_level',
        'is_ai_safe',
    ];

    protected $hidden = ['pivot'];

    protected $casts = [
        'data' => 'array',
        'is_ai_safe' => 'boolean',
        'price_in_euro' => 'float',
        'shipping_in_euro' => 'float',
        'width' => 'float',
        'height' => 'float',
        'depth' => 'float',
        'weight' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)
            ->with(['avatar_image', 'background_image']);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function media()
    {
        return $this->belongsToMany(Media::class);
    }
}
