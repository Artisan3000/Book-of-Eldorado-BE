<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Support\Carbon;

class StudentCourseController extends Controller
{
    // === STUDENT: Get MY courses ===
    public function getMyCourses(Request $request)
    {
        $student = $request->user()->studentProfile;

        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        return $this->formatCoursesResponse($student);
    }

    // === STUDENT: Get MY course details ===
    public function getMyCourseDetails(Request $request, $slug)
    {
        $student = $request->user()->studentProfile;
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        return $this->getCourseDetailsLogic($student->id, $slug);
    }

    // === ADMIN: Get ANY student's courses ===
    public function getCourses($id)
    {
        $student = Student::with('courses')->find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return $this->formatCoursesResponse($student);
    }

    // === ADMIN: Get ANY course details ===
    public function getCourseDetails(Request $request, $id, $slug)
    {
        return $this->getCourseDetailsLogic($id, $slug);
    }

    // === REUSABLE: Format courses list ===
    private function formatCoursesResponse($student)
    {
        $courses = $student->courses->map(function ($course) {
            $val = $course->pivot->last_accessed ?? null;
            if (! $val) {
                $lastAccessed = null;
            } else {
                // If already a Carbon instance, format it. If it's a string, try to parse.
                if ($val instanceof Carbon) {
                    $lastAccessed = $val->toIso8601String();
                } else {
                    try {
                        $lastAccessed = Carbon::parse($val)->toIso8601String();
                    } catch (\Throwable $e) {
                        // If parsing fails, return null to avoid breaking the response.
                        $lastAccessed = null;
                    }
                }
            }

            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'thumbnail' => $course->thumbnail ? asset('uploads/' . $course->thumbnail) : null,
                'progress' => (float) ($course->pivot->progress ?? 0),
                'status' => $course->pivot->status ?? 'inactive',
                'last_accessed' => $lastAccessed,
            ];
        });

        return response()->json(['courses' => $courses]);
    }

    // === REUSABLE: Course details logic ===
    private function getCourseDetailsLogic($studentId, $slug)
    {
        $course = Course::with([
                'instructor',
                'lessons',
                'quizzes',
                'projects',
                'discussions.user',
                'students'
            ])
            ->where('slug', $slug)
            ->first();

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $pivot = $course->students->firstWhere('id', $studentId);

        $data = [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'description' => $course->description,
            'instructor' => [
                'name' => $course->instructor?->name,
                'avatar' => $course->instructor?->avatar 
                    ? asset('uploads/instructors/' . $course->instructor->avatar) 
                    : null,
            ],
            'progress' => $pivot ? (float) $pivot->pivot->progress : 0.0,
            'lessons' => $course->lessons->map(function ($l) use ($studentId) {
                $completed = $l->completedBy()->where('student_id', $studentId)->exists();
                return [
                    'id' => $l->id,
                    'title' => $l->title,
                    'type' => $l->type,
                    // use the Eloquent accessor property (getDurationFormattedAttribute)
                    'duration' => $l->duration_formatted,
                    'completed' => $completed,
                ];
            }),
            'quizzes' => $course->quizzes->map(function ($q) use ($studentId) {
                $completed = $q->completedBy()->where('student_id', $studentId)->exists();
                return [
                    'id' => $q->id,
                    'title' => $q->title,
                    'completed' => $completed,
                ];
            }),
            'projects' => $course->projects->map(function ($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->title,
                    'due_date' => $p->due_date?->format('Y-m-d'),
                ];
            }),
            'discussions' => $course->discussions->map(function ($d) {
                return [
                    'id' => $d->id,
                    'author' => $d->user?->name ?? 'Unknown',
                    'content' => $d->content,
                    'timestamp' => $d->created_at->toIso8601String(),
                ];
            }),
        ];

        return response()->json(['course' => $data]);
    }
}