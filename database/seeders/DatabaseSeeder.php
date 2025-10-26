<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Discussion;
use App\Models\Event;
use App\Models\FeaturedOffering;
use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Quiz;
use App\Models\Recommendation;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ------------------------------------------------------------------
        // 1. ADMIN USER (Safe: update or create)
        // ------------------------------------------------------------------
        $admin = User::updateOrCreate(
            ['email' => 'admin3@example.com'],
            [
                'name'     => 'Super Admin',
                'password' => bcrypt('admin123'),
                'role'     => 'admin',
            ]
        );
        $admin->assignRole('admin');

        // ------------------------------------------------------------------
        // 2. INSTRUCTOR + STUDENT (Safe)
        // ------------------------------------------------------------------
        $instructorUser = User::updateOrCreate(
            ['email' => 'jordan@example.com'],
            [
                'name'     => 'Jordan Rivera',
                'password' => bcrypt('password'),
                'role'     => 'instructor',
            ]
        );
        $instructorUser->assignRole('instructor');

        $instructor = Instructor::updateOrCreate(
            ['user_id' => $instructorUser->id],
            []
        );

        $studentUser = User::updateOrCreate(
            ['email' => 'maria@student.test'],
            [
                'name'     => 'Maria',
                'password' => bcrypt('password'),
                'role'     => 'student',
            ]
        );
        $studentUser->assignRole('student');

        $student = Student::updateOrCreate(
            ['user_id' => $studentUser->id],
            [
                'streak'      => 6,
                'total_hours' => 42,
            ]
        );

        // ------------------------------------------------------------------
        // 3. COURSES (by slug)
        // ------------------------------------------------------------------
        $course1 = Course::updateOrCreate(
            ['slug' => 'foundation'],
            [
                'title'         => 'Foundation',
                'description'   => 'Beginner barbering course',
                'instructor_id' => $instructorUser->id,
                'price'         => 0,
                'thumbnail'     => 'foundation.jpg',
                'status'        => 'published',
            ]
        );

        $course2 = Course::updateOrCreate(
            ['slug' => 'refinement'],
            [
                'title'         => 'Refinement',
                'description'   => 'Advanced techniques',
                'instructor_id' => $instructorUser->id,
                'price'         => 85,
                'thumbnail'     => 'refinement.jpg',
                'status'        => 'published',
            ]
        );

        // ------------------------------------------------------------------
        // 4. ENROLLMENTS (sync without duplicates)
        // ------------------------------------------------------------------
        $student->courses()->syncWithoutDetaching([
            $course1->id => [
                'progress'       => 0.75,
                'status'         => 'active',
                'last_accessed'  => now()->subDays(3),
            ],
            $course2->id => [
                'progress'       => 0.40,
                'status'         => 'in-progress',
                'last_accessed'  => now()->subDays(10),
            ],
        ]);

        // ------------------------------------------------------------------
        // 5. LESSONS (by title + course)
        // ------------------------------------------------------------------
        Lesson::updateOrCreate(
            ['course_id' => $course2->id, 'title' => 'Precision Fades'],
            [
                'type'             => 'video',
                'duration_seconds' => 752,
                'order'            => 1,
            ]
        );

        Lesson::updateOrCreate(
            ['course_id' => $course2->id, 'title' => 'Consultation & Reading Clients'],
            [
                'type'  => 'pdf',
                'order' => 2,
            ]
        );

        // ------------------------------------------------------------------
        // 6. QUIZ & PROJECT
        // ------------------------------------------------------------------
        Quiz::updateOrCreate(
            ['course_id' => $course2->id, 'title' => 'Fade Techniques Quiz'],
            []
        );

        Project::updateOrCreate(
            ['course_id' => $course2->id, 'title' => 'Client Transformation Project'],
            ['due_date' => '2025-10-30']
        );

        // ------------------------------------------------------------------
        // 7. DISCUSSION (by user + content)
        // ------------------------------------------------------------------
        Discussion::updateOrCreate(
            [
                'course_id'  => $course2->id,
                'user_id'    => $studentUser->id,
                'content'    => 'Loved this technique!',
            ],
            ['created_at' => now()->subDays(3)]
        );

        // ------------------------------------------------------------------
        // 8. MILESTONES
        // ------------------------------------------------------------------
        Milestone::updateOrCreate(
            ['student_id' => $student->id, 'title' => 'Foundation'],
            [
                'description'  => 'Completed all beginner modules.',
                'status'       => 'completed',
                'completed_at' => '2025-08-01',
            ]
        );

        Milestone::updateOrCreate(
            ['student_id' => $student->id, 'title' => 'Refinement'],
            [
                'description' => 'Currently in progress.',
                'status'     => 'in-progress',
                'due_date'   => '2025-10-30',
            ]
        );

        // ------------------------------------------------------------------
        // 9. CERTIFICATE
        // ------------------------------------------------------------------
        Certificate::updateOrCreate(
            ['student_id' => $student->id, 'course_id' => $course1->id],
            [
                'title'     => 'Foundation Certificate',
                'badge'     => 'Graduation',
                'file_path' => 'foundation.pdf',
                'share_url' => 'https://linkedin.com/share?cert=foundation',
            ]
        );

        // ------------------------------------------------------------------
        // 10. FEATURED OFFERINGS
        // ------------------------------------------------------------------
        FeaturedOffering::updateOrCreate(
            ['title' => 'Blueprint Membership'],
            [
                'description' => 'Exclusive mentorship and live Q&As.',
                'price'       => 120,
                'period'      => 'year',
            ]
        );

        FeaturedOffering::updateOrCreate(
            ['title' => 'Mastery Program'],
            [
                'description' => 'Advanced barbering techniques.',
                'price'       => 85,
            ]
        );

        // ------------------------------------------------------------------
        // 11. RECOMMENDATION
        // ------------------------------------------------------------------
        Recommendation::updateOrCreate(
            ['title' => 'Color Grading with DaVinci Resolve'],
            [
                'description' => 'Because you liked Mastering Lighting...',
                'price'       => 65,
            ]
        );

        // ------------------------------------------------------------------
        // 12. EVENT
        // ------------------------------------------------------------------
        Event::updateOrCreate(
            ['title' => 'Workshop: Texture & Technique Live'],
            [
                'date'      => '2025-11-12',
                'location'  => 'Brooklyn Studio',
            ]
        );
    }
}