<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class FormulaDependency extends Model
{
    protected $fillable = [
        'formula_id',
        'variable_id',
        'type',
    ];

    public function formula()
    {
        return $this->belongsTo(Formula::class);
    }
}