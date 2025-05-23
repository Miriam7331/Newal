<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $companies_id
 * @property string $file
 * @property string $original_name
 * @property string $description
 * @property string $expiration_date
 * @property Company $company
 */
class CompanyDocument extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['companies_id', 'file', 'original_name', 'description', 'expiration_date'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'companies_id');
    }
}
