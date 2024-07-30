<?php

namespace services\common;

use Psr\Http\Message\RequestInterface;

class CustomHttpClient implements \Mpdf\Http\ClientInterface
{

    public function sendRequest(RequestInterface $request)
    {
        throw new \Exception('Loading resources from web denied');
    }
}