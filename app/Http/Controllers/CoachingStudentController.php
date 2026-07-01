<?php

namespace App\Http\Controllers;

use App\Models\CoachingBatch;
use App\Models\CoachingStudent;
use Illuminate\Http\Request;

class CoachingStudentController extends Controller
{
    public function index(Request $request)
    {
        $query = CoachingStudent::with('batch.course');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"));
        }

        $students = $query->orderBy('name')->paginate(30)->withQueryString();
        $batches  = CoachingBatch::with('course')->where('is_active', true)->get();

        return view('coaching.students.index', compact('students', 'batches'));
    }

    public function create()
    {
        $batches = CoachingBatch::with('course')->where('is_active', true)->get();
        return view('coaching.students.create', compact('batches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'batch_id'        => 'required|exists:coaching_batches,id',
            'name'            => 'required|string|max:100',
            'phone'           => 'nullable|string|max:20',
            'guardian_name'   => 'nullable|string|max:100',
            'guardian_phone'  => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:300',
            'enrollment_date' => 'required|date',
            'custom_fee'      => 'nullable|numeric|min:0',
            'discount_percent'=> 'nullable|integer|min:0|max:100',
            'notes'           => 'nullable|string|max:500',
        ]);

        CoachingStudent::create($data + ['status' => 'active']);
        return redirect()->route('coaching.students.index')->with('success', $data['name'] . ' enrolled successfully.');
    }

    public function show(CoachingStudent $student)
    {
        $student->load('batch.course', 'fees');
        return view('coaching.students.show', compact('student'));
    }

    public function edit(CoachingStudent $student)
    {
        $batches = CoachingBatch::with('course')->where('is_active', true)->get();
        return view('coaching.students.edit', compact('student', 'batches'));
    }

    public function update(Request $request, CoachingStudent $student)
    {
        $data = $request->validate([
            'batch_id'        => 'required|exists:coaching_batches,id',
            'name'            => 'required|string|max:100',
            'phone'           => 'nullable|string|max:20',
            'guardian_name'   => 'nullable|string|max:100',
            'guardian_phone'  => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:300',
            'enrollment_date' => 'required|date',
            'custom_fee'      => 'nullable|numeric|min:0',
            'discount_percent'=> 'nullable|integer|min:0|max:100',
            'status'          => 'required|in:active,completed,dropped',
            'notes'           => 'nullable|string|max:500',
        ]);

        $student->update($data);
        return redirect()->route('coaching.students.show', $student)->with('success', 'Student updated.');
    }

    public function destroy(CoachingStudent $student)
    {
        $student->delete();
        return redirect()->route('coaching.students.index')->with('success', 'Student removed.');
    }
}
