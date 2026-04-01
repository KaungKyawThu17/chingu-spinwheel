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
        'is_required',
        'has_other',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'has_other' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function questionOptions(): HasMany
    {
        return $this->hasMany(SurveyQuestionOption::class)->orderBy('order');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function getOptionPairsAttribute(): array
    {
        $options = $this->relationLoaded('questionOptions')
            ? $this->questionOptions->where('is_active', true)
            : $this->questionOptions()->where('is_active', true)->get();
        $pairs = [];

        foreach ($options as $option) {
            $value = $option->value ?? null;
            $label = $option->label ?? $value;

            if ($value === null) {
                continue;
            }

            $pairs[] = [
                'value' => (string) $value,
                'label' => (string) $label,
            ];
        }

        return $pairs;
    }
}
