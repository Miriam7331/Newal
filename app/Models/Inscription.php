<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property integer $formative_actions_id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $page
 * @property boolean $advertising
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property FormativeAction $formativeAction
 */
class Inscription extends Model
{
    protected $connection = 'mysql3';
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['name', 'email', 'phone', 'advertising', 'courses_id', 'web', 'island', 'dni'];

    //Castear advertising a boolean
    protected $casts = [
        'advertising' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo('App\Models\Course', 'courses_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Comprobar si existe un estudiante con el mismo dni (si no es null), email o teléfono
            $student = Student::where(function ($query) use ($model) {
                $query->where('email', $model->email)
                    ->orWhere('phone', $model->phone);

                // Solo aplicar el where de dni si el dni no es null
                if (!is_null($model->dni)) {
                    $query->orWhere('dni', $model->dni);
                }
            })->first();

            // Si coincide el email o el teléfono, pero el dni es null, entonces se actualiza el dni
            if ($student && is_null($student->dni)) {
                $student->dni = $model->dni;
                $student->save();
            }

            // Si ya existe el estudiante, no se crea uno nuevo

            if (!$student) {
                Student::create([
                    'name' => $model->name,
                    'email' => $model->email,
                    'dni' => $model->dni,
                    'phone' => $model->phone,
                    'island' => $model->island,
                ]);
            }
        });

        static::updated(function ($model) {
            // Comprobar si existe un estudiante con el mismo dni (si no es null), email o teléfono
            $student = Student::where(function ($query) use ($model) {
                $query->where('email', $model->email)
                    ->orWhere('phone', $model->phone);

                // Solo aplicar el where de dni si el dni no es null
                if (!is_null($model->dni)) {
                    $query->orWhere('dni', $model->dni);
                }
            })->first();

            // Si coincide el email o el teléfono, pero el dni es null, entonces se actualiza el dni
            if ($student && is_null($student->dni)) {
                $student->dni = $model->dni;
                $student->save();
            }

            // Si ya existe el estudiante, no se crea uno nuevo

            if (!$student) {
                Student::create([
                    'name' => $model->name,
                    'email' => $model->email,
                    'dni' => $model->dni,
                    'phone' => $model->phone,
                    'island' => $model->island,
                ]);
            }
        });
    }
}
