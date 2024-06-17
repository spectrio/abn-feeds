<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cacheDevice extends Model
{
    protected $connection = 'feeds';
    protected $table = 'abnDeviceCache';
    protected $primaryKey = 'id';
    
    protected $fillable = [];
}
