<?php

namespace App\Models;

use App\Models\Book;
use Illuminate\Database\Eloquent\Model;

// belajar pake softDeletes
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'categories';
    protected $guarded = [];

    public function books(){
        return $this->belongsToMany('App\Models\Book');
    }
}
