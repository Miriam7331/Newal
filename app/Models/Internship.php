<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $start_date
 * @property integer $end_date
 * @property string $schedule
 * @property integer $companies_id
 * @property integer $formative_actions_has_students_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */

class Internship extends Model
{

    /**
     * @var array
     */
    protected $fillable = ['start_date', 'end_date', 'schedule', 'formative_actions_has_students_id', 'companies_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'companies_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents()
    {
        return $this->hasMany('App\Models\InternshipDocument', 'internships_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function formativeActionHasStudent()
    {
        return $this->belongsTo(FormativeActionHasStudent::class, 'formative_actions_has_students_id');
    }
}
