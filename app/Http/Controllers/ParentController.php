<?php

namespace App\Http\Controllers;

use App\Models\ParentModel;
use App\Models\Student;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function index()
    {
        $parents = ParentModel::with('student')->get();
        return view('parents.index', compact('parents'));
    }

    public function create()
    {
        $students = Student::where('status', 'active')->get();
        return view('parents.create', compact('students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id|unique:parents,student_id',
            'name' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
        ]);

        ParentModel::create($validated);

        return redirect()->route('parents.index')->with('success', 'Data orang tua berhasil ditambahkan');
    }

    public function edit(ParentModel $parent)
    {
        $students = Student::where('status', 'active')->get();
        return view('parents.edit', compact('parent', 'students'));
    }

    public function update(Request $request, ParentModel $parent)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id|unique:parents,student_id,' . $parent->id,
            'name' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
        ]);

        $parent->update($validated);

        return redirect()->route('parents.index')->with('success', 'Data orang tua berhasil diupdate');
    }

    public function destroy(ParentModel $parent)
    {
        $parent->delete();
        return redirect()->route('parents.index')->with('success', 'Data orang tua berhasil dihapus');
    }
}
