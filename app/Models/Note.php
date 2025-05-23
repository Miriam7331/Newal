<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property integer $students_id
 * @property string $note
 * @property integer $users_id
 */
class Note extends Model
{
    /**
     * @var string
     */
    protected $table = 'notes';

    /**
     * @var array
     */
    protected $fillable = ['students_id', 'note', 'users_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo('App\Models\Student', 'students_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'users_id');
    }

}
