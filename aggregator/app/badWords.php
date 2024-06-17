<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class badWords extends Model
{
	protected $connection = 'feeds';
	protected $table = 'bad_words';
	
	public $incrementing = true;
	public $timestamps = false;
}
