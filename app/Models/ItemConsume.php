<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DianujHashidsTrait;

class  ItemConsume extends Model
{
    use HasFactory, DianujHashidsTrait;

    protected $table = 'shade_item_consume';

    public function category(){
    
        return $this->belongsTo(Category::class, 'category_id', 'id');
    
    }

    public function Shade(){
    
        return $this->belongsTo(Shade::class, 'shade_id', 'id');
    
    }

    public function item(){
     
        return $this->belongsTo(Item::class, 'item_id', 'id');
    
    }
}
