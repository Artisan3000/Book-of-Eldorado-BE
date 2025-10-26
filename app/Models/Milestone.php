<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $fillable = [
        'student_id',
        'title',
        'description',
        'status',
        'completed_at',
        'due_date',
    ];

    /**
     * Cast date fields to Carbon instances.
     * Use $casts for modern Laravel versions.
     */
    protected $casts = [
        'completed_at' => 'datetime',
        'due_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
