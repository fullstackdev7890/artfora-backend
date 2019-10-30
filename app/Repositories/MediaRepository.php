<?php

namespace App\Repositories;

use App\Models\Media;
use RonasIT\Support\Repositories\BaseRepository;

/**
 * @property  Media $model
 */
class MediaRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(Media::class);
    }

    public function getSearchResults()
    {
        $this->query->applyMediaPermissionRestrictions();

        return parent::getSearchResults();
    }
}
