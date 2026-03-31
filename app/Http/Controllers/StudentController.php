<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
       public function index(Request $request)
    {
        $query = Student::with('parent');

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $students = $query->orderBy('name', 'asc')->paginate(15);

        return view('students.index', compact('students'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nisn' => 'required|unique:students',
            'name' => 'required',
            'class' => 'required',
            'fingerprint_uid' => 'nullable',
            'device_user_id' => 'nullable',
            'status' => 'required|in:active,inactive',
        ]);

        Student::create($validated);

        return redirect()->route('students.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'nisn' => 'required|unique:students,nisn,' . $student->id,
            'name' => 'required',
            'class' => 'required',
            'fingerprint_uid' => 'nullable',
            'device_user_id' => 'nullable',
            'status' => 'required|in:active,inactive',
        ]);

        $student->update($validated);

        return redirect()->route('students.index')->with('success', 'Siswa berhasil diupdate');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Siswa berhasil dihapus');
    }
}
