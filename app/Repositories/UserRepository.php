<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Arr;
use Artel\Support\Repositories\BaseRepository;

/**
 * @property  User $model
*/
class UserRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(User::class);
    }
}
