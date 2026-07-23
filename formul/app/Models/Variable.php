<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Variable extends Model
{
    protected $fillable = [
        'title',
        'code',
        'type',
        'default_value',
        'window_type_id'
    ];

    protected $casts = [
        'default_value'=>'float'
    ];
}