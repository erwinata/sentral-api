<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
		protected $fillable = ['id', 'name', 'price', 'image', 'order', 'visible'];

		protected $casts = ['id' => 'string'];
}
