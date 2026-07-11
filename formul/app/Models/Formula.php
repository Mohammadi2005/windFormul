<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Formula extends Model
{
    protected $fillable = [
        'window_type_id',
        'output_variable_id',
        'expression_json',
        'execution_order',
        'is_active',
    ];

    protected $casts = [
        'expression_json' => 'array',
        'condition_json' => 'array',
        'is_active' => 'boolean',
    ];

    public static function getFormula(string $code, int $windowTypeId): ?self
    {
        return self::where('code', $code)
            ->where('window_type_id', $windowTypeId)
            ->where('is_active', 1)
            ->first();
    }

    public function resultVariable(){
        return $this->hasone(variable::class, 'id', 'output_variable_id');
    }

    public function windowType(){
        return $this->hasone(windowType::class, 'id', 'window_type_id');
    }

    public function dependencies()
    {
        return $this->hasMany(FormulaDependency::class);
    }
}