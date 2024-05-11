<?php

namespace Microwin7\PHPUtils\Configs;

class TextureConfig
{
    protected const array SKIN_SIZE = [['w' => 64, 'h' => 64], ['w' => 64, 'h' => 32]];
    protected const array CAPE_SIZE = [['w' => 64, 'h' => 32]];
    protected const array SKIN_SIZE_HD = [
        ['w' => 128, 'h' => 64], ['w' => 128, 'h' => 128],
        ['w' => 256, 'h' => 128], ['w' => 256, 'h' => 256],
        ['w' => 512, 'h' => 256], ['w' => 512, 'h' => 512],
        ['w' => 1024, 'h' => 512], ['w' => 1024, 'h' => 1024]
    ];
    protected const array CAPE_SIZE_HD = [
        ['w' => 128, 'h' => 64], ['w' => 256, 'h' => 128],
        ['w' => 512, 'h' => 256], ['w' => 1024, 'h' => 512]
    ];
}
