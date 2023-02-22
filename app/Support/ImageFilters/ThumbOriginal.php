<?php

namespace App\Support\ImageFilters;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

class ThumbOriginal implements FilterInterface
{
    public function applyFilter(Image $image)
    {
        return $image;
    }
}