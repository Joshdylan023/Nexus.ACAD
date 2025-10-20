<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HRFieldMapping extends Model
{
    use HasFactory;

    protected $table = 'hr_field_mappings';

    protected $fillable = [
        'hr_integration_id',
        'entity_type',
        'source_field',
        'target_field',
        'transform_function',
        'default_value',
        'is_required'
    ];

    protected $casts = [
        'is_required' => 'boolean'
    ];

    public function integration()
    {
        return $this->belongsTo(HRIntegration::class, 'hr_integration_id');
    }
}
