<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $students_id
 * @property string $file
 * @property string $original_name
 * @property string $description
 * @property string $expiration_date
 * @property Student $student
 */
class Document extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['students_id', 'file', 'original_name', 'description', 'expiration_date'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo('App\Models\Student', 'students_id');
    }
}
