<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $table = 'favorite_user';

    protected $dates = ['date'];

    protected $guarded = [];

    public function produto(){
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

}
