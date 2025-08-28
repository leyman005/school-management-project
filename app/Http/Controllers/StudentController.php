<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Http\Requests\StudentRequest;

/**
 * Student management controller
 * Handles CRUD operations for students
 */

class StudentController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    // Fetch all students from the database
    $students = Student::all();
    return response()->json($students, 200);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StudentRequest $request)
  {
    // Validate and create a new student
    $validated_data = $request->validated();

    $student = Student::create($validated_data);
    return response()->json($student, 201);
  }

  /**
   * Display the specified resource.
   */
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

  /**
   * Update the specified resource in storage.
   */
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

  /**
   * Remove the specified resource from storage.
   */
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
