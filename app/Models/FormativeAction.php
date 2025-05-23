<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @property integer $¡d
 * @property integer $courses_id
 * @property integer $sectors_id
 * @property integer $teachers_id
 * @property integer $pages_id
 * @property boolean $promote
 * @property string $schedule
 * @property string $start_date
 * @property string $end_date
 * @property integer $price
 * @property string $type
 * @property boolean $visibility
 * @property string $image
 * @property string $requirements
 * @property Course $course
 * @property Web $web
 * @property Sector $sector
 * @property Teacher $teacher
 * @property ModulesHasFormativeAction[] $modulesHasFormativeActions
 */
class FormativeAction extends Model
{
    use SoftDeletes;
    /**
     * @var array
     */
    protected $fillable = ['courses_id', 'sectors_id', 'teachers_id', 'centers_id', 'modules_id', 'entities_id', 'receiver', 'schedule', 'start_date', 'end_date', 'price', 'type', 'requirements', 'islands', 'code', 'min_quota', 'min_quota_to_end', 'max_quota', 'projects_id', 'max_inscription_date'];

    //Castear promote y visibility a boolean
    protected $casts = [
        'promote' => 'boolean',
        'visibility' => 'boolean',
        'courses_id' => 'integer',
        'sectors_id' => 'integer',
        'teachers_id' => 'integer',
        'centers_id' => 'integer',
        'modules_id' => 'integer',
        'entities_id' => 'integer',
        'projects_id' => 'integer',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Create record when a new formative action is created
        static::created(function ($formativeAction) {
            static::createRecord($formativeAction, 'created');
        });

        // Create record when a formative action is updated
        static::updated(function ($formativeAction) {
            static::createRecord($formativeAction, 'updated');
        });

        // Create record when a formative action is deleted (soft delete)
        static::deleted(function ($formativeAction) {
            static::createRecord($formativeAction, 'deleted');
        });

        // Create record when a formative action is restored
        static::restored(function ($formativeAction) {
            static::createRecord($formativeAction, 'restored');
        });
    }

    /**
     * Create a record for the given formative action and action type
     *
     * @param FormativeAction $formativeAction
     * @param string $action
     * @return void
     */
    protected static function createRecord($formativeAction, $action)
    {
        Record::create([
            'user_id' => Auth::id() ?? 1, // Default to 1 if no authenticated user
            'element_id' => $formativeAction->id,
            'model' => 'App\Models\FormativeAction', // Asegurarse de que es un string
            'action' => $action
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo('App\Models\Course', 'courses_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function module()
    {
        return $this->belongsTo('App\Models\Module', 'modules_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sector()
    {
        return $this->belongsTo('App\Models\Sector', 'sectors_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo('App\Models\Teacher', 'teachers_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * */
    public function center()
    {
        return $this->belongsTo('App\Models\Center', 'centers_id');
    }

    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'formative_actions_has_students',
            'formative_actions_id',
            'students_id'
        )->withPivot('status', 'id', 'file', 'original_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            'App\Models\User',
            'users_has_formative_actions',
            'formative_actions_id',
            'users_id'
        );
    }

    public function modules()
    {
        $database = $this->getConnection()->getDatabaseName();

        return $this->belongsToMany(
            'App\Models\Module',
            "$database.modules_has_formative_actions",
            'formative_actions_id',
            'modules_id'
        );
    }

    public function entity()
    {
        return $this->belongsTo('App\Models\Entity', 'entities_id');
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'projects_id');
    }

    /**
     * Get the recordable relationship
     */
    public function records()
    {
        return $this->morphMany(Record::class, 'recordable', 'model', 'element_id');
    }

    public function formativeActionHasStudent()
    {
        return $this->hasMany('App\Models\FormativeActionHasStudent', 'formative_actions_id');
    }
}
