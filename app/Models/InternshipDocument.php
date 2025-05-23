<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $internships_id
 * @property string $file
 * @property string $original_name
 * @property string $description
 * @property string $expiration_date
 * @property Internship $internship
 */

class InternshipDocument extends Model
{

    /**
     * @var array
     */
    protected $fillable = ['internships_id', 'file', 'original_name', 'description', 'expiration_date'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function internship()
    {
        return $this->belongsTo('App\Models\Internship', 'internships_id');
    }
}
