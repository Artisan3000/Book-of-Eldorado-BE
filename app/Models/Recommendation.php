<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $fillable = ['student_id', 'title', 'description', 'price'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
