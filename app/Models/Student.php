<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
  use HasApiTokens, HasFactory, Notifiable;
  protected $guarded = [];

  protected $hidden = [
    'created_at',
    'updated_at',
    'student_pin',
    'remember_token',
  ];
}
