<?php

namespace App\Repositories;

use App\Models\Setting;
use Artel\Support\Repositories\BaseRepository;

/**
 * @property  Setting $model
*/
class SettingRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(Setting::class);
    }

    public function getSearchResults()
    {
        $this->query->applySettingPermissionRestrictions();

        return parent::getSearchResults();
    }
}
