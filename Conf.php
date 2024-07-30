<?php

namespace services\config;

use services\common\ConfigParent;

class Conf extends ConfigParent
{
    protected static ?Conf $Conf = null;

    protected $host;
    static function init($host = null)
    {
        return static::$Conf ?? (static::$Conf = new static($host));
    }


}