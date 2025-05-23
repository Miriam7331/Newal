<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property integer $entities_id
 * @property string $name
 * @property Entity $entity
 * @property Island $island
 * @property FormativeAction[] $formativeActions
 */
class Center extends Model
{

    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['entities_id', 'name', 'island'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entity()
    {
        return $this->belongsTo('App\Models\Entity', 'entities_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formativeActions()
    {
        return $this->hasMany('App\Models\FormativeAction', 'centers_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'users_has_centers', 'centers_id', 'users_id');
    }
}
