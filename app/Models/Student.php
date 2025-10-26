<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'streak',
        'total_hours',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
        'total_hours' => 'decimal:2',
    ];

    /* ------------------------------------------------------------------ */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_student')
                    ->withPivot('progress', 'status', 'last_accessed')
                    ->withTimestamps();
    }

    /**
     * Milestones (achievements) for the student.
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    /**
     * Certificates awarded to the student.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}