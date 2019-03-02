<?php

namespace App;

use \Noodlehaus\Config as BaseConfig;

class Config
{
    public static function get($key)
    {
        $conf = new BaseConfig(BASE_PATH . '/config');
        return $conf->get($key);
    }
}