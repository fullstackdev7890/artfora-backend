<?php

namespace App\Support\ImageFilters;

use Intervention\Image\Image;

class Thumb354X540 extends Thumb
{
    protected function getSize()
    {
        return [354, 540];
    }

    protected function addWatermark(Image $image)
    {
        // This image size doesn't require a watermark
        return $image;
    }
}