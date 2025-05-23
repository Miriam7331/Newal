<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @property integer $id
 * @property string $cif
 * @property string $name
 * @property string $email
 * @property integer $phone
 * @property string $address
 * @property string $number
 * @property string $floor
 * @property string $door
 * @property Municipality $municipality
 * @property integer $cp
 * @property Island $islandRelation
 * @property CompanyDocument[] $companyDocuments
 * @property CompanyContact[] $companyContacts
 */
class Company extends Model
{

    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'cif',
        'name',
        'email',
        'phone',
        'address',
        'number',
        'floor',
        'door',
        'municipality',
        'cp',
        'island'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents()
    {
        return $this->hasMany('App\Models\CompanyDocument', 'companies_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany('App\Models\CompanyContact', 'companies_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function internships()
    {
        return $this->hasMany('App\Models\Internship', 'internships_id');
    }
}
