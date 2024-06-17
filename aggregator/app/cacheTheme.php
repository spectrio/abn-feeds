<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cacheTheme extends Model
{
    protected $connection = 'feeds';
    protected $table = 'cacheTheme';
    protected $primaryKey = 'channelName';
    
    public $incrementing = true;
    public $timestamps = true;
    
    protected $fillable = ['channelName', 'channelTheme'];
}
