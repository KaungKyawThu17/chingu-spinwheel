<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
            'has_spun',
            'prize',
            'event_id',
            'name',
            'phone',
            'age',
            'gender',
            'job_title',
            'drink_time',
            'drink_place',
            'drink_whom',
            'choose_reason',
            'drink_meal_important',
            'drink_meal_type',
            'drink_meal_type_other',
            'drink_flavor',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }
}
