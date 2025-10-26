<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Lesson extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'title',
        'type',             // e.g., video, text, quiz, downloadable
        'duration_seconds',
        'content',          // HTML, markdown, or JSON
        'order',            // display order
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration_seconds' => 'integer',
        'order'            => 'integer',
        'content'          => 'string',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /* ===================================================================
     *  RELATIONSHIPS
     * =================================================================== */

    /**
     * The course this lesson belongs to.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Students who have completed this lesson.
     * Requires `lesson_completions` pivot table.
     */
    public function completedBy(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'lesson_completions')
                    ->withPivot('completed_at')
                    ->withTimestamps();
    }

    /* ===================================================================
     *  SCOPES
     * =================================================================== */

    /**
     * Scope: Video lessons only.
     */
    public function scopeVideo(Builder $query): Builder
    {
        return $query->where('type', 'video');
    }

    /**
     * Scope: Text lessons only.
     */
    public function scopeText(Builder $query): Builder
    {
        return $query->where('type', 'text');
    }

    /**
     * Scope: Lessons with duration.
     */
    public function scopeHasDuration(Builder $query): Builder
    {
        return $query->whereNotNull('duration_seconds')->where('duration_seconds', '>', 0);
    }

    /* ===================================================================
     *  ACCESSORS & MUTATORS
     * =================================================================== */

    /**
     * Get formatted duration: MM:SS or H:MM:SS.
     */
    public function getDurationFormattedAttribute(): ?string
    {
        if (!$this->duration_seconds) {
            return null;
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Get duration in seconds (alias).
     */
    public function getDurationAttribute(): ?int
    {
        return $this->duration_seconds !== null ? (int) $this->duration_seconds : null;
    }

    /**
     * Check if lesson is a video.
     */
    public function getIsVideoAttribute(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Check if lesson is text-based.
     */
    public function getIsTextAttribute(): bool
    {
        return $this->type === 'text';
    }

    /* ===================================================================
     *  CUSTOM METHODS
     * =================================================================== */

    /**
     * Mark lesson as completed by a student.
     */
    public function markAsCompleted(Student $student): void
    {
        $this->completedBy()->syncWithoutDetaching([
            $student->id => ['completed_at' => now()]
        ]);
    }

    /**
     * Check if a student has completed this lesson.
     */
    public function isCompletedBy(Student $student): bool
    {
        return $this->completedBy()
                    ->where('student_id', $student->id)
                    ->exists();
    }

    /**
     * Get completion percentage for this lesson across all students.
     */
    public function getCompletionRateAttribute(): float
    {
        $totalStudents = $this->course->students()->count();
        if ($totalStudents === 0) {
            return 0.0;
        }

        $completed = $this->completedBy()->count();
        return round(($completed / $totalStudents) * 100, 1);
    }

    /**
     * Get next lesson in the course (by order).
     */
    public function next(): ?Lesson
    {
        return $this->course->lessons()
                    ->where('order', '>', $this->order)
                    ->orderBy('order')
                    ->first();
    }

    /**
     * Get previous lesson in the course.
     */
    public function previous(): ?Lesson
    {
        return $this->course->lessons()
                    ->where('order', '<', $this->order)
                    ->orderBy('order', 'desc')
                    ->first();
    }
}