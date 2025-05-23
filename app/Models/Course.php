<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property integer $professional_families_id
 * @property string $name
 * @property string $description
 * @property integer $level
 * @property string $code
 * @property ProfessionalFamily $professionalFamily
 * @property FormativeAction[] $formativeActions
 * @property ModulesHasCourse[] $modulesHasCourses
 */
class Course extends Model
{
    protected $connection = 'mysql2';
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['professional_families_id', 'name', 'description', 'level', 'code'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function professionalFamily()
    {
        return $this->belongsTo('App\Models\ProfessionalFamily', 'professional_families_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formativeActions()
    {
        return $this->hasMany('App\Models\FormativeAction', 'courses_id');
    }

    /**
     * The modules associated with the course.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function modules()
    {
        return $this->belongsToMany(
            'App\Models\Module',
            'modules_has_courses', // Tabla pivote
            'courses_id', // Llave foránea en la tabla pivote para Course
            'modules_id' // Llave foránea en la tabla pivote para Module
        );
    }
}
