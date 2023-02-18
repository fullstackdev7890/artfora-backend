<?php

namespace App\Models;

use Artel\Support\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use ModelTrait, SoftDeletes;

    const APPROVED_STATUS = 'Approved';
    const REJECTED_STATUS = 'Rejected';
    const PENDING_STATUS = 'Pending';

    const COMMON_VISIBILITY_LEVEL = 0;
    const NUDITY_VISIBILITY_LEVEL = 1;
    const EROTIC_VISIBILITY_LEVEL = 2;
    const PORNO_VISIBILITY_LEVEL = 3;

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
        'weight' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)
            ->with('avatar_image');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function media()
    {
        return $this->belongsToMany(Media::class);
    }
}
