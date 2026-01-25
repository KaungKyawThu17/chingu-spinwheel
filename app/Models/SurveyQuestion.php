<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'type',
        'options',
        'is_required',
        'has_other',
        'order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'has_other' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function getOptionPairsAttribute(): array
    {
        $options = $this->options ?? [];
        $pairs = [];

        foreach ($options as $option) {
            if (is_array($option)) {
                $value = $option['value'] ?? null;
                $label = $option['label'] ?? $value;

                if ($value === null) {
                    continue;
                }

                $pairs[] = [
                    'value' => (string) $value,
                    'label' => (string) $label,
                ];
            } else {
                $pairs[] = [
                    'value' => (string) $option,
                    'label' => (string) $option,
                ];
            }
        }

        return $pairs;
    }
}
