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
     * @param array{0: GdImage, 1: int, 2: int, 3: int} $data
     * @psalm-suppress PossiblyUnusedMethod
     * size не меньше 64 сделать и кратным 64
     * ПОМЕНЯТЬ ПРОВЕРКУ РАЗМЕРА БЛОКА
     */
    public static function front(array $data, int $size): GdImage
    {
        [$gdImage, $x, $y, $fraction] = $data;
        $isSlim = parent::checkSkinSlimFromImage($gdImage);
        $canvas = parent::create_canvas_transparent($size, $size * 2);
        /** @var int $f_part */
        $f_part = $fraction / 2;

        $block_size = (int) ($size / 2); // 128 -> 64
        $block_size_1_2 = (int) ($block_size / 2); // 32
        
        $block_size_3_2 = $block_size_1_2 * 3; // 96
        $block_size_under = self::size_under($block_size); // 58
        $block_size_indent = (int) (($block_size - $block_size_under) / 2); // 3
        $block_size_indent_1_2 = (int) ($block_size_indent / 2); // 1.5 ERROR
        $canvas_arm_right = GDUtils::create_canvas_transparent($block_size_1_2, $block_size_3_2);
        $canvas_arm_left = GDUtils::create_canvas_transparent($block_size_1_2, $block_size_3_2);
        $canvas_leg_right = GDUtils::create_canvas_transparent($block_size_1_2, $block_size_3_2);
        $canvas_leg_left = GDUtils::create_canvas_transparent($block_size_1_2, $block_size_3_2);

        // BODY 1
        imagecopyresized(
            $canvas,
            $gdImage,
            $block_size_1_2 + $block_size_indent,
            $block_size,
            $f_part * 5,
            $f_part * 5,
            $block_size_under,
            $block_size_3_2 - $block_size_indent,
            $f_part * 2,
            $f_part * 3
        );
        // ============= LEG'S ============= //
        // RL 1
        imagecopyresized(
            $canvas_leg_right,
            $gdImage,
            $block_size_indent_1_2,
            $block_size_indent_1_2,
            $f_part * 1,
            $f_part * 5,
            imagesx($canvas_leg_right) - ($block_size_indent_1_2 * 2),
            imagesy($canvas_leg_right) - ($block_size_indent_1_2 * 2),
            $f_part * 1,
            $f_part * 3
        );
        if ($x === $y) {
            // LL 1
            imagecopyresized(
                $canvas_leg_left,
                $gdImage,
                $block_size_indent_1_2,
                $block_size_indent_1_2,
                $f_part * 5,
                $f_part * 13,
                imagesx($canvas_leg_left) - ($block_size_indent_1_2 * 2),
                imagesy($canvas_leg_left) - ($block_size_indent_1_2 * 2),
                $f_part * 1,
                $f_part * 3
            );
            // RL 2
            imagecopyresized(
                $canvas_leg_right,
                $gdImage,
                0,
                0,
                $f_part * 1,
                $f_part * 9,
                imagesx($canvas_leg_right),
                imagesy($canvas_leg_right),
                $f_part * 1,
                $f_part * 3
            );
            // LL 2
            imagecopyresized(
                $canvas_leg_left,
                $gdImage,
                0,
                0,
                $f_part * 1,
                $f_part * 13,
                imagesx($canvas_leg_left),
                imagesy($canvas_leg_left),
                $f_part * 1,
                $f_part * 3
            );
        } else {
            imagecopy($canvas_leg_left, $canvas_leg_right, 0, 0, 0, 0, imagesx($canvas_leg_right), imagesy($canvas_leg_right));
            imageflip($canvas_leg_left, IMG_FLIP_HORIZONTAL);
        }
        imagecopy(
            $canvas,
            $canvas_leg_right,
            $block_size_1_2 + $block_size_indent_1_2,
            $block_size + $block_size_3_2 - $block_size_indent - $block_size_indent_1_2,
            0,
            0,
            imagesx($canvas_leg_right),
            imagesy($canvas_leg_right)
        );
        imagecopy(
            $canvas,
            $canvas_leg_left,
            $block_size - $block_size_indent_1_2,
            $block_size + $block_size_3_2 - $block_size_indent - $block_size_indent_1_2,
            0,
            0,
            imagesx($canvas_leg_left),
            imagesy($canvas_leg_left)
        );

        // ============= ARM'S ============= //
        if (!$isSlim) {
            // RA 1
            imagecopyresized(
                $canvas_arm_right,
                $gdImage,
                $block_size_indent_1_2,
                $block_size_indent_1_2,
                $f_part * 11,
                $f_part * 5,
                imagesx($canvas_arm_right) - ($block_size_indent_1_2 * 2),
                imagesy($canvas_arm_right) - ($block_size_indent_1_2 * 2),
                $f_part * 1,
                $f_part * 3
            );
        } else {
            // RA 1 SLIM | ALWAYS X === Y
            imagecopyresized(
                $canvas_arm_right,
                $gdImage,
                $block_size_indent_1_2 + ($block_size_1_2 / 4),
                $block_size_indent_1_2,
                $f_part * 11,
                $f_part * 5,
                ($block_size_1_2 / 4 * 3) - $block_size_indent,
                imagesy($canvas_arm_right) - ($block_size_indent_1_2 * 2),
                $f_part / 4 * 3,
                $f_part * 3
            );
        }

        if ($x === $y) {
            if (!$isSlim) {
                // LA 1
                imagecopyresized(
                    $canvas_arm_left,
                    $gdImage,
                    $block_size_indent_1_2,
                    $block_size_indent_1_2,
                    $f_part * 9,
                    $f_part * 13,
                    $block_size_1_2 - $block_size_indent,
                    $block_size_3_2 - $block_size_indent,
                    $f_part * 1,
                    $f_part * 3
                );
                // RA 2
                imagecopyresized(
                    $canvas_arm_right,
                    $gdImage,
                    0,
                    0,
                    $f_part * 11,
                    $f_part * 9,
                    imagesx($canvas_arm_right),
                    imagesy($canvas_arm_right),
                    $f_part * 1,
                    $f_part * 3
                );
                // LA 2
                imagecopyresized(
                    $canvas_arm_left,
                    $gdImage,
                    0,
                    0,
                    $f_part * 13,
                    $f_part * 13,
                    imagesx($canvas_arm_left),
                    imagesy($canvas_arm_left),
                    $f_part * 1,
                    $f_part * 3
                );
            } else {
                // LA 1 SLIM
                imagecopyresized(
                    $canvas_arm_left,
                    $gdImage,
                    $block_size_indent_1_2,
                    $block_size_indent_1_2,
                    $f_part * 9,
                    $f_part * 13,
                    ($block_size_1_2 / 4 * 3) - $block_size_indent,
                    $block_size_3_2 - $block_size_indent,
                    $f_part / 4 * 3,
                    $f_part * 3
                );
                // RA 2 SLIM
                imagecopyresized(
                    $canvas_arm_right,
                    $gdImage,
                    $block_size_1_2 / 4,
                    0,
                    $f_part * 13,
                    $f_part * 13,
                    imagesx($canvas_arm_right) / 4 * 3,
                    imagesy($canvas_arm_right),
                    $f_part / 4 * 3,
                    $f_part * 3
                );
                // LA 2 SLIM
                imagecopyresized(
                    $canvas_arm_left,
                    $gdImage,
                    0,
                    0,
                    $f_part * 13,
                    $f_part * 13,
                    imagesx($canvas_arm_left) / 4 * 3,
                    imagesy($canvas_arm_left),
                    $f_part / 4 * 3,
                    $f_part * 3
                );
            }
        } else {
            imagecopy($canvas_arm_left, $canvas_arm_right, 0, 0, 0, 0, imagesx($canvas_arm_right), imagesy($canvas_arm_right));
            imageflip($canvas_arm_left, IMG_FLIP_HORIZONTAL);
        }
        imagecopy(
            $canvas,
            $canvas_arm_right,
            $block_size_indent + $block_size_indent_1_2,
            $block_size - $block_size_indent_1_2,
            0,
            0,
            imagesx($canvas_arm_right),
            imagesy($canvas_arm_right)
        );
        imagecopy(
            $canvas,
            $canvas_arm_left,
            $block_size + $block_size_1_2 - $block_size_indent - $block_size_indent_1_2,
            $block_size - $block_size_indent_1_2,
            0,
            0,
            imagesx($canvas_arm_left),
            imagesy($canvas_arm_left)
        );
        if ($x === $y) {
            // BODY 2
            imagecopyresized(
                $canvas,
                $gdImage,
                $block_size_1_2 + ((int) ($block_size_indent / 4)),
                $block_size,
                $f_part * 5,
                $f_part * 9,
                $block_size - $block_size_indent_1_2,
                $block_size_3_2,
                $f_part * 2,
                $f_part * 3
            );
        }


        // AVATAR
        imagecopy($canvas, self::avatar($data, $block_size), $block_size_1_2, $block_size_indent, 0, 0, $block_size, $block_size);
        return $canvas;
    }
    /**
     * @param array{0: GdImage, 1: int} $data
     * @psalm-suppress PossiblyUnusedMethod
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
     * @param array{0: GdImage, 1: int, 2: int, 3: int} $data
     */
    public static function avatar(array $data, int $size): GdImage
    {
        [$image, $_, $_, $fraction] = $data;
        $canvas = GDUtils::create_canvas_transparent($size, $size);
        $size_under = self::size_under($size);
        /** @var int $size_indent */
        $size_indent = ($size - $size_under) / 2;
        imagecopyresized(
            $canvas,
            $image,
            $size_indent,
            $size_indent,
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
    /** Коэфициент от блока */
    private static function size_under(int $size): int
    {
        $size_under = (int)floor($size / 1.1);
        if ($size_under % 2 !== 0) $size_under--;
        return $size_under;
    }
    /**
     * @psalm-suppress PossiblyUnusedMethod
     * @param int $size - размер одного пикселя
     */
    public static function cape_resize(string $data, int $size): GdImage
    {
        $image = imagecreatefromstring($data);
        $width = imagesx($image);
        /** @var int $fraction */
        $fraction = $width / 64;
        $canvas = GDUtils::create_canvas_transparent($size * 22, $size * 17);
        imagecopyresized(
            $canvas,
            $image,
            0,
            0,
            0,
            0,
            $size * 22,
            $size * 17,
            $fraction * 22,
            $fraction * 17
        );
        return $canvas;
    }
}
