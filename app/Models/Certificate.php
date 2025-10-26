<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'student_id',
        'title',
        'badge',
        'file_path',
        'share_url',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
