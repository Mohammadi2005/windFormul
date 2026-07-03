<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Formula extends Model
{
    protected $fillable = [
        'window_type_id',
        'name',
        'code',
        'output_variable_id',
        'expression_json',
        'execution_order',
        'is_active',
    ];

    protected $casts = [
        'expression_json' => 'array',
        'is_active' => 'boolean',
    ];

    public static function getFormula(string $code, int $windowTypeId): ?self
    {
        return self::where('code', $code)
            ->where('window_type_id', $windowTypeId)
            ->where('is_active', 1)
            ->first();
    }
}