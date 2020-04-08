<?php

namespace App\Repositories;

use App\CovidCache;

class CovidCacheRepository
{
    private $model;

    public function __construct(CovidCache $model)
    {
        $this->model = $model;
    }

    public function get($key)
    {
        return $this->model->where('key', $key)->first();
    }

    public function set($key, $value)
    {
        return $this->model->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
