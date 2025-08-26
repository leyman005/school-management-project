<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Http\Requests\StudentRequest;

class StudentController extends Controller
{
  // Controller methods will go here

  public function index()
  {
    // Fetch all students from the database
    $students = Student::all();
    return response()->json($students, 200);
  }

  public function show($id)
  {
    // Fetch a single student by ID
    $student = Student::find($id);
    if ($student) {
      return response()->json($student, 200);
    } else {
      return response()->json(['message' => 'Student not found'], 404);
    }
  }

  public function store(StudentRequest $request)
  {
    // Validate and create a new student
    $validated_data = $request->validated();

    $student = Student::create($validated_data);
    return response()->json($student, 201);
  }

  public function update(StudentRequest $request, $id)
  {
    // Validate and update an existing student
    $validated_data = $request->validated();

    $student = Student::find($id);
    if ($student) {
      $student->update($validated_data);
      return response()->json($student, 200);
    } else {
      return response()->json(['message' => 'Student not found'], 404);
    }
  }

  public function destroy($id)
  {
    // Delete a student by ID
    $student = Student::find($id);
    if ($student) {
      $student->delete();
      return response()->json(['message' => 'Student deleted successfully'], 200);
    } else {
      return response()->json(['message' => 'Student not found'], 404);
    }
  }
}
