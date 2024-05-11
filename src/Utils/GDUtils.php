<?php

namespace Microwin7\TextureProvider\Utils;

use GdImage;
use Microwin7\PHPUtils\Utils\GDUtils as UtilsGDUtils;
use Microwin7\TextureProvider\Config;

class GDUtils extends UtilsGDUtils
{
    public static function skin_resize(string $data): string
    {
        if (Config::SKIN_RESIZE()) {
            [$image, $x, $y, $fraction] = self::pre_calculation($data);
            if ($x / 2 == $y) {
                $canvas = self::create_canvas_transparent($x, $x);
                imagecopy($canvas, $image, 0, 0, 0, 0, $x, $y);
                /** @var int $f_part */
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
            }
        }
        return $data;
    }
    /**
     * @param array{0: GdImage, 1: int} $data
     */
    public static function front(array $data, int $size): GdImage
    {
        // Создано пока что только для скинов по шаблону 64x32
        [$image, $fraction] = $data;
        $canvas = parent::create_canvas_transparent($size, $size * 2);
        /** @var int $f_part */
        $f_part = $fraction / 2;
        $canvas_front = GDUtils::create_canvas_transparent($fraction * 2, $fraction * 4);
        $canvas_arm = GDUtils::create_canvas_transparent($f_part, $f_part * 3);
        $canvas_leg = $canvas_arm;
        // Head
        imagecopy($canvas_front, $image, $f_part, 0, $fraction, $fraction, $fraction, $fraction);
        //Helmet
        imagecopy($canvas_front, $image, $f_part, 0, $fraction * 5, $fraction, $fraction, $fraction);
        // Torso
        imagecopy($canvas_front, $image, $f_part, $f_part * 2, $f_part * 5, $f_part * 5, $f_part * 2, $f_part * 3);
        //Left Arm
        imagecopy($canvas_arm, $image, 0, 0, $f_part * 11, $f_part * 5, $f_part, $f_part * 3);
        imagecopy($canvas_front, $canvas_arm, 0, $f_part * 2, 0, 0, $f_part, $f_part * 3);
        //Right Arm
        imageflip($canvas_arm, IMG_FLIP_HORIZONTAL);
        imagecopy($canvas_front, $canvas_arm, $f_part * 3, $f_part * 2, 0, 0, $f_part, $f_part * 3);
        //Left Leg
        imagecopy($canvas_leg, $image, 0, 0, $f_part, $f_part * 5, $f_part, $f_part * 3);
        imagecopy($canvas_front, $canvas_leg, $f_part, $f_part * 5, 0, 0, $f_part, $f_part * 3);
        //Right Leg
        imageflip($canvas_leg, IMG_FLIP_HORIZONTAL);
        imagecopy($canvas_front, $canvas_leg, $f_part * 2, $f_part * 5, 0, 0, $f_part, $f_part * 3);
        //Resize
        imagecopyresized($canvas, $canvas_front, 0, 0, 0, 0,   $size, $size * 2, $fraction * 2, $fraction * 4);
        return $canvas;
    }
    /**
     * @param array{0: GdImage, 1: int} $data
     */
    public static function back(array $data, int $size): GdImage
    {
        // Создано пока что только для скинов по шаблону 64x32
        [$image, $fraction] = $data;
        $canvas = GDUtils::create_canvas_transparent($size, $size * 2);
        /** @var int $f_part */
        $f_part = $fraction / 2;
        $canvas_back = GDUtils::create_canvas_transparent($fraction * 2, $fraction * 4);
        $canvas_arm = GDUtils::create_canvas_transparent($f_part, $f_part * 3);
        $canvas_leg = $canvas_arm;
        // Head
        imagecopy($canvas_back, $image, $f_part, 0, $fraction * 3, $fraction, $fraction, $fraction);
        //Helmet
        imagecopy($canvas_back, $image, $f_part, 0, $fraction * 7, $fraction, $fraction, $fraction);
        // Torso
        imagecopy($canvas_back, $image, $f_part, $f_part * 2, $f_part * 8, $f_part * 5, $f_part * 2, $f_part * 3);
        //Left Arm
        imagecopy($canvas_arm, $image, 0, 0, $f_part * 13, $f_part * 5, $f_part, $f_part * 3);
        imagecopy($canvas_back, $canvas_arm, $f_part * 3, $f_part * 2, 0, 0, $f_part, $f_part * 3);
        //Right Arm
        imageflip($canvas_arm, IMG_FLIP_HORIZONTAL);
        imagecopy($canvas_back, $canvas_arm, 0, $f_part * 2, 0, 0, $f_part, $f_part * 3);
        //Left Leg
        imagecopy($canvas_leg, $image, 0, 0, $f_part * 3, $f_part * 5, $f_part, $f_part * 3);
        imagecopy($canvas_back, $canvas_leg, $f_part * 2, $f_part * 5, 0, 0, $f_part, $f_part * 3);
        //Right Leg
        imageflip($canvas_leg, IMG_FLIP_HORIZONTAL);
        imagecopy($canvas_back, $canvas_leg, $f_part, $f_part * 5, 0, 0, $f_part, $f_part * 3);
        //Resize
        imagecopyresized($canvas, $canvas_back, 0, 0, 0, 0,   $size, $size * 2, $fraction * 2, $fraction * 4);
        return $canvas;
    }
    /**
     * @param array{0: GdImage, 1: int} $data
     */
    public static function avatar(array $data, int $size): GdImage
    {
        [$image, $fraction] = $data;
        $canvas = GDUtils::create_canvas_transparent($size, $size);
        $size_under = (int)floor($size / 1.1);
        if ($size_under % 2 !== 0) $size_under--;
        /** @var int $size_part */
        $size_part = ($size - $size_under) / 2;
        imagecopyresized(
            $canvas,
            $image,
            $size_part,
            $size_part,
            $fraction,
            $fraction,
            $size_under,
            $size_under,
            $fraction,
            $fraction
        );
        imagecopyresized(
            $canvas,
            $image,
            0,
            0,
            $fraction * 5,
            $fraction,
            $size,
            $size,
            $fraction,
            $fraction
        );
        return $canvas;
    }
    public static function cape_resize(string $data, int $size): GdImage
    {
        $image = imagecreatefromstring($data);
        $width = imagesx($image);
        /** @var int $fraction */
        $fraction = $width / 64;
        $canvas = GDUtils::create_canvas_transparent($size * 22, $size * 17);
        imagecopyresized($canvas, $image, 0, 0, 0, 0, $size * 22, $size * 17, $fraction * 22, $fraction * 17);
        return $canvas;
    }
}
