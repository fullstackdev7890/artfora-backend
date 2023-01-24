<?php

namespace App\Models;

use Artel\Support\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use ModelTrait;

    protected $fillable = [
        'email',
        'token',
    ];

    protected $hidden = ['pivot'];
}
