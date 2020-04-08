<?php

namespace App\Http\Service\Base;

use Illuminate\Support\Facades\Http;

abstract class HttpService
{
    protected $BASE_URL;

    protected function getReq($path)
    {
        return Http::get($this->BASE_URL . $path);
    }
}
