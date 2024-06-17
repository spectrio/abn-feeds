<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cacheScroller extends Model
{
    protected $connection = 'feeds';
    protected $table = 'cacheScroller';
    protected $primaryKey = 'scrollerID';
    
    public $incrementing = true;
    public $timestamps = true;
    
    protected $fillable = ['scrollerID', 'accountID', 'channelName', 'scrollerType', 'scrollerMessage', 'scrollerStart', 'scrollerEnd'];
}
