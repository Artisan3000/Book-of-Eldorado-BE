<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\FeaturedOffering;
use App\Models\Recommendation;
use App\Models\Event;

class StudentExploreController extends Controller
{
    public function getExplore($id)
    {
        // Check if student exists
        $student = Student::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json($this->buildExplore($student));
    }

    /**
     * Student: get authenticated student's explore payload
     */
    public function getMyExplore(Request $request)
    {
        $user = $request->user();
        if (! $user) return response()->json(['message' => 'Unauthenticated'], 401);

        $student = Student::where('user_id', $user->id)->first();
        if (! $student) return response()->json(['message' => 'Student profile not found'],404);

        return response()->json($this->buildExplore($student));
    }

    /**
     * Build explore payload for a student (featured, recommended, events)
     */
    private function buildExplore(Student $student)
    {
        // Fetch featured, recommended, and events
        $featured = FeaturedOffering::all()->map(function ($f) {
            return [
                'id' => $f->id,
                'title' => $f->title,
                'description' => $f->description,
                'price' => (float) $f->price,
                'period' => $f->period ?? null,
            ];
        });

        // Optional: filter recommendations by student interests later
        $recommended = Recommendation::where('student_id', $student->id)
            ->orWhereNull('student_id') // global recommendations
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'title' => $r->title,
                    'description' => $r->description,
                    'price' => (float) $r->price,
                ];
            });

        $events = Event::whereDate('date', '>=', now())
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($e) {
                // Defensive formatting: some rows may have raw strings
                try {
                    $date = \Illuminate\Support\Carbon::parse($e->date)->format('Y-m-d');
                } catch (\Throwable $ex) {
                    $date = null;
                }

                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'date' => $date,
                    'location' => $e->location,
                ];
            });

        return [
            'featured' => $featured,
            'recommended' => $recommended,
            'events' => $events,
        ];
    }
}
