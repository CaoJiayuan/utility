<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/15
 * Time: 下午4:43
 */

namespace CaoJiayuan\Utility\Image;


use Intervention\Image\ImageManager;

class Merger
{
    const MODE_MIN = 'min';
    const MODE_MAX = 'max';

    const DIRECTION_VERTICAL = 0;
    const DIRECTION_HORIZON = 1;


    /**
     * @var int
     */
    protected $columns;
    protected $manager;
    protected $direction;

    public function __construct($direction = Merger::DIRECTION_VERTICAL)
    {
        $this->manager = new ImageManager();
        $this->direction = $direction;
    }

    /**
     * @param $images
     * @param string|int $mode
     * @return \Intervention\Image\Image
     */
    public function merge($images, $mode = Merger::MODE_MIN)
    {
        if ($this->direction == static::DIRECTION_VERTICAL) {
            return $this->mergeVertical($images, $mode);
        } else {
            return $this->mergeHorizon($images, $mode);
        }
    }

    /**
     * @param Image[] $images
     * @param int|string $mode
     * @return \Intervention\Image\Image
     */
    public function mergeVertical($images, $mode = Merger::MODE_MIN)
    {
        /** @var Image[] $images */
        $images = $this->formatImages($images);

        $widths = array_map(function ($img) {
            /** @var Image $img */
            return $img->getWidth();
        }, $images);

        if (is_numeric($mode)) {
            $width = $mode;
        } else {
            $width = $mode == Merger::MODE_MIN ? min($widths) : max($widths);
        }

        $height = 0;

        foreach($images as $image) {
            $height += $image->getHeight() * ($width / $image->getWidth());
        }

        $canvas = $this->manager->canvas($width, $height);

        $top = 0;
        foreach($images as $k => $image) {
            $image->zoomByWidth($width);
            $canvas->insert($image->getImage(), 'top-left', 0, $top);
            $top += $image->getHeight();
        }

        return $canvas;
    }

    /**
     * @param Image[] $images
     * @param int|string $mode
     * @return \Intervention\Image\Image
     */
    public function mergeHorizon($images, $mode = Merger::MODE_MIN)
    {
        /** @var Image[] $images */
        $images = $this->formatImages($images);

        $heights = array_map(function ($img) {
            /** @var Image $img */
            return $img->getHeight();
        }, $images);

        if (is_numeric($mode)) {
            $height = $mode;
        } else {
            $height = $mode == Merger::MODE_MIN ? min($heights) : max($heights);
        }
        $width = 0;

        foreach($images as $image) {
            $width += $image->getWidth() * ($height / $image->getHeight());
        }

        $canvas = $this->manager->canvas($width, $height);

        $left = 0;
        foreach($images as $k => $image) {
            $image->zoomByHeight($height);
            $canvas->insert($image->getImage(), 'top-left', $left, 0);
            $left += $image->getWidth();
        }

        return $canvas;
    }

    protected function formatImages($images)
    {
        if (is_array($images)) {
            return array_map(function ($img) {
                return $this->formatImage($img);
            }, $images);
        } else {
            return $this->formatImage($images);
        }
    }

    protected function formatImage($img)
    {
        if (!$img instanceof Image) {
            return Image::make($img);
        }

        return $img;
    }
}
