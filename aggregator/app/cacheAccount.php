<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cacheAccount extends Model
{
    protected $connection = 'feeds';
    protected $table = 'cacheAccount';
    protected $primaryKey = 'accountID';

    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = ['accountID', 'accountName', 'accountCity', 'accountZip'];
}
