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
class Project extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['name', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formativeActions()
    {
        return $this->hasMany('App\Models\FormativeAction', 'projects_id');
    }
}
