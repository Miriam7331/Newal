<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormativeActionHasStudent extends Pivot
{
    public $incrementing = true;
    use SoftDeletes;

    protected $table = 'formative_actions_has_students';

    protected $fillable = [
        'formative_actions_id',
        'students_id',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'students_id');
    }

    public function internship()
    {
        return $this->hasMany(Internship::class, 'formative_actions_has_students_id');
    }

    public function formativeAction()
    {
        return $this->belongsTo(FormativeAction::class, 'formative_actions_id');
    }
}
