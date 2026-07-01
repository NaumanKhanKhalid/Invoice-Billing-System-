<?php

namespace App\Http\Controllers;

use App\Models\CoachingCourse;
use App\Models\CoachingBatch;
use Illuminate\Http\Request;

class CoachingCourseController extends Controller
{
    public function index()
    {
        $courses = CoachingCourse::withCount('batches')->latest()->get();
        $batches = CoachingBatch::with('course')->withCount(['students' => fn($q) => $q->where('status', 'active')])->latest()->get();
        return view('coaching.courses', compact('courses', 'batches'));
    }

    public function storeCourse(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'monthly_fee' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);
        CoachingCourse::create($data + ['is_active' => true]);
        return back()->with('success', 'Course added.');
    }

    public function updateCourse(Request $request, CoachingCourse $course)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'monthly_fee' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);
        $course->update($data);
        return back()->with('success', 'Course updated.');
    }

    public function destroyCourse(CoachingCourse $course)
    {
        if ($course->batches()->exists()) {
            return back()->with('error', 'Cannot delete — batches exist for this course.');
        }
        $course->delete();
        return back()->with('success', 'Course deleted.');
    }

    public function storeBatch(Request $request)
    {
        $data = $request->validate([
            'course_id'    => 'required|exists:coaching_courses,id',
            'name'         => 'required|string|max:100',
            'timing'       => 'nullable|string|max:50',
            'days'         => 'nullable|string|max:100',
            'teacher_name' => 'nullable|string|max:100',
            'capacity'     => 'nullable|integer|min:1|max:200',
        ]);
        CoachingBatch::create($data + ['is_active' => true, 'capacity' => $data['capacity'] ?? 20]);
        return back()->with('success', 'Batch added.');
    }

    public function updateBatch(Request $request, CoachingBatch $batch)
    {
        $data = $request->validate([
            'course_id'    => 'required|exists:coaching_courses,id',
            'name'         => 'required|string|max:100',
            'timing'       => 'nullable|string|max:50',
            'days'         => 'nullable|string|max:100',
            'teacher_name' => 'nullable|string|max:100',
            'capacity'     => 'nullable|integer|min:1|max:200',
            'is_active'    => 'boolean',
        ]);
        $batch->update($data);
        return back()->with('success', 'Batch updated.');
    }

    public function destroyBatch(CoachingBatch $batch)
    {
        if ($batch->students()->exists()) {
            return back()->with('error', 'Cannot delete — students enrolled in this batch.');
        }
        $batch->delete();
        return back()->with('success', 'Batch deleted.');
    }
}
