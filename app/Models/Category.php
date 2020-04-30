<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    const TABLE = 'categories';

    protected $table = self::TABLE;

    protected $fillable = ['name', 'description'];



    //
}
