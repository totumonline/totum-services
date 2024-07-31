<?php

namespace services\common;

use Psr\Log\LoggerInterface;

class CustomContentLoader implements \Mpdf\File\LocalContentLoaderInterface, \Psr\Log\LoggerAwareInterface
{

    public function load($path)
    {
        throw new \Exception('Loading resources from file system denied');
    }

    public function setLogger(LoggerInterface $logger): void
    {
        // TODO: Implement setLogger() method.
    }
}