<?php

namespace App\Support\ImageFilters;

use Intervention\Image\Facades\Image as ImageFacade;
use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;
use ImagickDraw;
use Imagick;

class ThumbOriginalWithWatermark implements FilterInterface
{
    public function applyFilter(Image $image)
    {
        $newImage = ImageFacade::make($image);

        $text = config('app.name');

        $draw = new ImagickDraw();

        $draw->setFont('Arial');
        $draw->setFontSize(20);
        $draw->setFillColor('#00000001');

        $draw->setGravity(Imagick::GRAVITY_SOUTHEAST);

        return $newImage->annotateImage($draw, 10, 12, 0, $text);
    }
}