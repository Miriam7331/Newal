<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property boolean $level
 * @property string $dni
 * @property string $ssn
 * @property integer $phone
 * @property string $address
 * @property string $cp
 * @property string $city
 * @property string $province
 * @property string $island
 * @property string $gender
 * @property string $birthdate
 * @property string $disability
 * @property boolean $consent
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Document[] $documents
 */
class Student extends Model
{
    use SoftDeletes;
    /**
     * @var array
     */
    protected $fillable = ['name', 'email', 'level', 'dni', 'ssn', 'phone', 'address', 'cp', 'city', 'province', 'island', 'gender', 'birthdate', 'disability', 'consent'];

    protected $casts = [
        'consent' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Create record when a new student is created
        static::created(function ($student) {
            static::createRecord($student, 'created');
        });

        // Create record when a student is updated
        static::updated(function ($student) {
            static::createRecord($student, 'updated');
        });

        // Create record when a student is deleted (soft delete)
        static::deleted(function ($student) {
            static::createRecord($student, 'deleted');
        });

        // Create record when a student is restored
        static::restored(function ($student) {
            static::createRecord($student, 'restored');
        });
    }

    /**
     * Create a record for the given student and action type
     *
     * @param Student $student
     * @param string $action
     * @return void
     */
    protected static function createRecord($student, $action)
    {
        Record::create([
            'user_id' => Auth::id() ?? 1, // Default to 1 if no authenticated user
            'element_id' => $student->id,
            'model' => 'App\Models\Student', // Asegurarse de que es un string
            'action' => $action
        ]);
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['matchingInscriptions'] = $this->matchingInscriptions;
        return $array;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents()
    {
        return $this->hasMany('App\Models\Document', 'students_id');
    }

    /**
     * The formative actions that the student is enrolled in.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function formativeActions()
    {
        return $this->belongsToMany('App\Models\FormativeAction', 'formative_actions_has_students', 'students_id', 'formative_actions_id')
            ->withPivot('status', 'id', 'file', 'original_name')
            ->withTimestamps(); // Si quieres incluir también los timestamps
    }

    /**
     * The modules associated with the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function modules()
    {
        $database = $this->getConnection()->getDatabaseName();

        return $this->belongsToMany(
            'App\Models\Module',
            "$database.students_has_modules", // Tabla pivote
            'students_id', // Llave foránea en la tabla pivote para Student
            'modules_id' // Llave foránea en la tabla pivote para Module
        )->withPivot(['status', 'formative_actions_id']) // Incluyendo campos adicionales
            ->withTimestamps(); // Si quieres incluir también los timestamps
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany('App\Models\Note', 'students_id');
    }

    /**
     * Get the recordable relationship
     */
    public function records()
    {
        return $this->morphMany(Record::class, 'recordable', 'model', 'element_id');
    }

    public function getMatchingInscriptionsAttribute()
    {
        $query = Inscription::with('course');

        $database = env('DB_DATABASE2');

        $query->join("$database.courses", 'inscriptions.courses_id', '=', 'courses.id')->select("courses.name AS course_name", "inscriptions.*");


        $query->where(function ($query) {
            $query->where('email', $this->email)
                ->orWhere('phone', $this->phone);

            // Solo aplicar el where de dni si el dni no es null
            if (!is_null($this->dni)) {
                $query->orWhere('dni', $this->dni);
            }
        });

        return $query->get();
    }
}
