<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * @property integer $id
 * @property integer $companies_id
 * @property string $dni
 * @property string $name
 * @property string $surnames
 * @property string $email
 * @property integer $phone
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Company $company
 */

class CompanyContact extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['companies_id', 'dni', 'name', 'surname', 'email', 'phone'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'companies_id');
    }
}
