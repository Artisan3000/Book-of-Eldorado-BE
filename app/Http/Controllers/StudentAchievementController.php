<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Student;

class StudentAchievementController extends Controller
{
    public function getAchievements($id)
    {
        $student = Student::with(['milestones','certificates'])->find($id);
        if(!$student) return response()->json(['message'=>'Student not found'],404);

        return response()->json($this->buildAchievements($student));
    }

    /**
     * Student: get authenticated student's achievements
     */
    public function getMyAchievements(Request $request)
    {
        $user = $request->user();
        if (! $user) return response()->json(['message' => 'Unauthenticated'], 401);

        $student = Student::with(['milestones','certificates'])->where('user_id', $user->id)->first();
        if (! $student) return response()->json(['message'=>'Student profile not found'],404);

        return response()->json($this->buildAchievements($student));
    }

    /**
     * Build achievements payload for a given student
     */
    private function buildAchievements(Student $student)
    {
        $milestones = $student->milestones->map(function($m) {
            // Defensive date formatting: some records may be raw strings, so parse safely.
            $completedAt = null;
            if (!empty($m->completed_at)) {
                try {
                    $completedAt = \Illuminate\Support\Carbon::parse($m->completed_at)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $completedAt = null;
                }
            }

            $dueDate = null;
            if (!empty($m->due_date)) {
                try {
                    $dueDate = \Illuminate\Support\Carbon::parse($m->due_date)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $dueDate = null;
                }
            }

            return [
                'id'=>$m->id,
                'title'=>$m->title,
                'description'=>$m->description,
                'status'=>$m->status,
                'date'=>$m->status === 'completed' ? $completedAt : ($dueDate ? 'Due '.$dueDate : null)
            ];
        });

        $certificates = $student->certificates->map(function($c) {
            return [
                'id'=>$c->id,
                'title'=>$c->title,
                'badge'=>$c->badge ?? '🏅',
                'file_url'=>$c->file_path ? url('uploads/certificates/'.$c->file_path) : null,
                'share_url'=>$c->share_url
            ];
        });

        return ['milestones'=>$milestones,'certificates'=>$certificates];
    }
}
