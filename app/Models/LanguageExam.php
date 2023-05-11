<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageExam extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'language',
        'level',
        'type',
        'date'
    ];
}
