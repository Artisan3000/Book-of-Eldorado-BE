<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Recommendation;

class StudentOverviewController extends Controller
{
    /**
     * Admin: Get a compact overview for a student (summary + recent courses + recommendations + achievements)
     */
    public function getOverview($id)
    {
        $student = Student::with(['user', 'courses', 'certificates'])->find($id);
        if (! $student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json($this->buildOverview($student));
    }

    /**
     * Student: Get overview for the authenticated student
     */
    public function getMyOverview(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $student = Student::with(['user', 'courses', 'certificates'])->where('user_id', $user->id)->first();
        if (! $student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        return response()->json($this->buildOverview($student));
    }

    /**
     * Build the overview payload for a Student model instance
     */
    private function buildOverview(Student $student)
    {
        // Student summary
        $summary = [
            'id' => $student->id,
            'name' => $student->user?->name,
            'email' => $student->user?->email,
            'streak' => (int) ($student->streak ?? 0),
            'total_hours' => (int) ($student->total_hours ?? 0),
            'courses_in_progress' => $student->courses->filter(function ($c) {
                $prog = $c->pivot->progress ?? 0;
                $status = $c->pivot->status ?? null;
                return ($status === 'active') || ($prog > 0 && $prog < 1);
            })->count(),
            'certificates_earned' => $student->certificates->count(),
        ];

        // Recent courses (by last_accessed pivot, fallback to progress)
        $recent = $student->courses->sortByDesc(function ($c) {
            return $c->pivot->last_accessed ?? ($c->pivot->progress ?? 0);
        })->take(2)->map(function ($c) {
            return [
                'id' => $c->id,
                'title' => $c->title,
                'slug' => $c->slug,
                'progress' => (float) ($c->pivot->progress ?? 0),
            ];
        })->values();

        // Recommendations: student-specific first, then global; include description as reason
        $recommendations = Recommendation::where('student_id', $student->id)
            ->orWhereNull('student_id')
            ->orderByRaw('student_id IS NULL')
            ->limit(5)
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'title' => $r->title,
                    'price' => $r->price !== null ? (float) $r->price : null,
                    'reason' => $r->description ?? null,
                ];
            });

        // Achievements: map certificates (id, title, date)
        $achievements = $student->certificates->map(function ($c) {
            return [
                'id' => $c->id,
                'title' => $c->title,
                'date' => $c->created_at?->format('Y-m-d'),
            ];
        });

        return [
            'student' => $summary,
            'recent_courses' => $recent,
            'recommendations' => $recommendations,
            'achievements' => $achievements,
        ];
    }
}
