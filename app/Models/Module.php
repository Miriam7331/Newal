<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property FormativeAction[] $formativeActions
 */
class Module extends Model
{
    protected $connection = 'mysql2';
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['code', 'name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formativeActions()
    {
        return $this->hasMany('App\Models\FormativeAction', 'modules_id');
    }

    /**
     * The courses associated with the module.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function courses()
    {
        return $this->belongsToMany(
            'App\Models\Course',
            'modules_has_courses', // Tabla pivote
            'modules_id', // Llave foránea en la tabla pivote para Module
            'courses_id' // Llave foránea en la tabla pivote para Course
        );
    }
}
