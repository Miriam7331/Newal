<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property string $name
 * @property string $url
 * @property string $ratio
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Center[] $centers
 */
class Entity extends Model
{

    use SoftDeletes;
    /**
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function centers()
    {
        return $this->hasMany('App\Models\Center', 'entities_id');
    }
}
