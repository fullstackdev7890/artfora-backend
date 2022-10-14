<?php

namespace App\Repositories;

use App\Models\Setting;
use Artel\Support\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @property  Setting $model
*/
class SettingRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(Setting::class);
    }

    public function getSearchResults(): LengthAwarePaginator
    {
        $this->query->applySettingPermissionRestrictions();

        return parent::getSearchResults();
    }
}
