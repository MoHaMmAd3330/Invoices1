<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'description',
        'Product_name',
        'section_id',
        'created_at',
        'updated_at',

    ];

    public function scopeSelection($query){
        return $query ->select( 'id',
            'description',
            'Product_name',
            'section_id'

        );
    }

    public function sections(){
        return $this-> belongsTo('App\Models\Sections','section_id','id');
    }
}
