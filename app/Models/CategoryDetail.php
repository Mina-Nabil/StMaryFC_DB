<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryDetail extends Model
{
    protected $table = 'categories_details';
    protected $fillable = [
        'attendance', 'payment'
    ];

    public $timestamps = false;


    ///relations
    public function playersCatogory() : BelongsTo
    {
        return $this->belongsTo(PlayersCatogory::class, 'players_category_id');
    }
}
