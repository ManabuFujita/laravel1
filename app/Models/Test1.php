<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test1 extends Model
{
    use HasFactory;
    protected $fillable = ['location', 'lat', 'lon']; //保存したいカラム名が1つの場合
}
