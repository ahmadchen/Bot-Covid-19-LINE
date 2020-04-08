<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidCache extends Model
{
    protected $table = "covid_caches";
    protected $fillable = ['key', 'value'];
}
