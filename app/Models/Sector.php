<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property FormativeAction[] $formativeActions
 */
class Sector extends Model
{
    protected $connection = 'mysql2';
    use SoftDeletes;
    /**
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formativeActions()
    {
        return $this->hasMany('App\Models\FormativeAction', 'sectors_id');
    }
}
