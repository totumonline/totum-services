<?php

namespace services\common;

use Shuchkin\SimpleXLSX;

class XlsxParser
{
    protected SimpleXLSX|false $xlsx;

    public function __construct($filepath)
    {
        if (!($this->xlsx = SimpleXLSX::parse($filepath))) {
            throw new \Exception(SimpleXLSX::parseError());
        }
    }

    function getRows()
    {
        return $this->xlsx->rows();
    }

    function getObject(): SimpleXLSX
    {
        return $this->xlsx;
    }
}