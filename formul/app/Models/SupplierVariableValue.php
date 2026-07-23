<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class SupplierVariableValue extends Model
{
    protected $fillable = [
        'supplier_id',
        'variable_id',
        'value',
    ];


}