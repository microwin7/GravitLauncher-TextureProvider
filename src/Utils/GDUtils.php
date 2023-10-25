<?php

namespace Microwin7\TextureProvider\Utils;

use TypeError;
use Microwin7\TextureProvider\Configs\Config;

class GDUtils
{
    public static function skin_resize($data)
    {
        if (Config::SKIN_RESIZE) {
            [$image, $x, $y, $fraction] = self::pre_calculation($data);
            if ($x / 2 == $y) {
                $canvas = self::create_canvas_transparent($x, $x);
                imagecopy($canvas, $image, 0, 0, 0, 0, $x, $y);
                $f_part = $fraction / 2;

                $left_leg = $left_arm = self::create_canvas_transparent($f_part * 3, $f_part * 3); // 12x12
                imagecopy($left_leg, $image, 0, 0, 0, $f_part * 5, $f_part * 3, $fraction * 4); // 0, 20 >> 12, 32
                imageflip($left_leg, IMG_FLIP_HORIZONTAL);
                imagecopy($canvas, $left_leg, $fraction * 2, $f_part * 13, 0, 0, $f_part * 3, $f_part * 3);

                $left_leg2 = $left_arm2 = self::create_canvas_transparent($f_part, $f_part * 3); // 4x12
                imagecopy($left_leg2, $image, 0, 0, $f_part * 3, $f_part * 5, $fraction * 2, $fraction * 4); // 12, 20 >> 16, 32
                imageflip($left_leg2, IMG_FLIP_HORIZONTAL);
                imagecopy($canvas, $left_leg2, $f_part * 7, $f_part * 13, 0, 0, $f_part, $f_part * 3);

                imagecopy($left_arm, $image, 0, 0, $fraction * 5, $f_part * 5, $f_part * 13, $fraction * 4); // 40, 20 >> 52, 32
                imageflip($left_arm, IMG_FLIP_HORIZONTAL);
                imagecopy($canvas, $left_arm, $fraction * 4, $f_part * 13, 0, 0, $f_part * 3, $f_part * 3);

                imagecopy($left_arm2, $image, 0, 0, $f_part * 13, $f_part * 5, $fraction * 7, $fraction * 4); // 52, 20 >> 56, 32
                imageflip($left_arm2, IMG_FLIP_HORIZONTAL);
                imagecopy($canvas, $left_arm2, $f_part * 11, $f_part * 13, 0, 0, $f_part, $f_part * 3);

                $square = $square2 = $square3 = $square4 = self::create_canvas_transparent($f_part, $f_part); //4x4
                imagecopy($square, $image, 0, 0, $f_part, $fraction * 2, $fraction, $f_part * 5); // 4, 16 >> 8, 20
                imageflip($square, IMG_FLIP_HORIZONTAL);
                imagecopy($canvas, $square, $f_part * 5, $fraction * 6, 0, 0, $f_part, $f_part);

                imagecopy($square2, $image, 0, 0, $fraction, $fraction * 2, $f_part * 3, $f_part * 5); // 8, 16 >> 12, 20
                imageflip($square2, IMG_FLIP_HORIZONTAL);
                imagecopy($canvas, $square2, $fraction * 3, $fraction * 6, 0, 0, $f_part, $f_part);

                imagecopy($square3, $image, 0, 0, $f_part * 11, $fraction * 2, $fraction * 6, $f_part * 5); // 44, 16 >> 48, 20
                imageflip($square3, IMG_FLIP_HORIZONTAL);
                imagecopy($canvas, $square3, $f_part * 9, $fraction * 6, 0, 0, $f_part, $f_part);

                imagecopy($square4, $image, 0, 0, $fraction * 6, $fraction * 2, $f_part * 13, $f_part * 5); // 48, 16 >> 52, 20
                imageflip($square4, IMG_FLIP_HORIZONTAL);
                imagecopy($canvas, $square4, $fraction * 5, $fraction * 6, 0, 0, $f_part, $f_part);

                ob_start();
                imagepng($canvas);
                $data = ob_get_clean();
                ob_end_clean();
            }
        }
        return $data;
    }
    public static function slim(string $data): bool
    {
        try {
            $image = @imagecreatefromstring($data);
            $fraction = imagesx($image) / 8;
            $x = $fraction * 6.75;
            $y = $fraction * 2.5;
            $rgba = imagecolorsforindex($image, imagecolorat($image, $x, $y));
            if ($rgba["alpha"] === 127)
                return true;
            else return false;
        } catch (TypeError $e) {
            throw new TypeError('Error read data. This file is not an image.');
        }
    }
    public static function pre_calculation($data)
    {
        try {
            $image = @imagecreatefromstring($data);
            $x = imagesx($image);
            $y = imagesy($image);
            $fraction = $x / 8;
            return [$image, $x, $y, $fraction];
        } catch (TypeError $e) {
            throw new TypeError('Error read data. This file is not an image.');
        }
    }
    public static function create_canvas_transparent($width, $height): \GdImage
    {
        ini_set('gd.png_ignore_warning', 0);
        $canvas = imagecreatetruecolor($width, $height);
        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagesavealpha($canvas, TRUE);
        return $canvas;
    }
}
