<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'instructor_id',
        'price',
        'thumbnail',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ===================================================================
     *  RELATIONSHIPS
     * =================================================================== */

    /**
     * Get the instructor who created the course.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get all lessons for the course, ordered by position.
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    /**
     * Get all quizzes in the course.
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * Get all projects assigned in the course.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get all discussions related to the course.
     */
    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }

    /**
     * Get all students enrolled in the course.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'course_student')
                    ->withPivot(['progress', 'status', 'last_accessed'])
                    ->withTimestamps();
    }

    /* ===================================================================
     *  SCOPES
     * =================================================================== */

    /**
     * Scope: Only published courses.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope: Only draft courses.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: Free courses.
     */
    public function scopeFree(Builder $query): Builder
    {
        return $query->where('price', 0);
    }

    /**
     * Scope: Paid courses.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('price', '>', 0);
    }

    /* ===================================================================
     *  ACCESSORS & MUTATORS
     * =================================================================== */

    /**
     * Get the thumbnail URL with fallback.
     */
    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail
            ? asset('storage/' . $this->thumbnail)
            : asset('images/default-course-thumbnail.jpg');
    }

    /**
     * Check if course is free.
     */
    public function getIsFreeAttribute(): bool
    {
        return $this->price == 0;
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->is_free ? 'Free' : '$' . number_format($this->price, 2);
    }

    /**
     * Get total number of lessons.
     */
    public function getLessonsCountAttribute(): int
    {
        return $this->lessons()->count();
    }

    /**
     * Get total enrolled students count.
     */
    public function getEnrolledCountAttribute(): int
    {
        return $this->students()->count();
    }

    /* ===================================================================
     *  CUSTOM METHODS
     * =================================================================== */

    /**
     * Enroll a student in the course.
     */
    public function enroll(Student $student, array $pivotData = []): void
    {
        $default = [
            'progress' => 0.00,
            'status' => 'active',
            'last_accessed' => now(),
        ];

        $this->students()->attach($student->id, array_merge($default, $pivotData));
    }

    /**
     * Check if a student is enrolled.
     */
    public function isEnrolled(Student $student): bool
    {
        return $this->students()->where('student_id', $student->id)->exists();
    }

    /**
     * Update student's progress.
     */
    public function updateProgress(Student $student, float $progress): bool
    {
        if (!$this->isEnrolled($student)) {
            return false;
        }

        $progress = min(1.00, max(0.00, $progress)); // Clamp 0.00 - 1.00

        return $this->students()
            ->updateExistingPivot($student->id, [
                'progress' => $progress,
                'last_accessed' => now(),
            ]);
    }

    /**
     * Get average progress of all students.
     */
    public function getAverageProgressAttribute(): float
    {
        return round(
            $this->students()->avg('progress') ?? 0,
            2
        );
    }
}