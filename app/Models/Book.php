<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;
    protected $table = 'books';
    protected $guarded = [];
    use SoftDeletes;
    
    public function categories(){
        return $this->belongsToMany('App\Models\Category');
    }


    public function orders(){
        return $this->belongsToMany('App\Models\Order');
    }
}
