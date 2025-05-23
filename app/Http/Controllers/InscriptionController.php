<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Student;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use App\Models\Course;

class InscriptionController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard/Inscription');
    }

    public function loadItems()
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = Inscription::with('course');

        $database = env('DB_DATABASE2');

        $query->join("$database.courses", 'inscriptions.courses_id', '=', 'courses.id')->select("courses.name AS course_name", "inscriptions.*");

        if ($deleted) {
            $query->onlyTrashed();
        }

        if (!empty($search)) {
            foreach ($search as $key => $value) {
                if (!empty($value)) {
                    if ($key === 'course_name') {
                        $query->where('courses.name', 'LIKE', '%' . $value . '%');
                    } else if ($key === 'created_at') {
                        $query->whereRaw("DATE_FORMAT(inscriptions.created_at, '%d/%m/%Y') LIKE '%$value%'");
                    } else if ($key == "advertising") {
                        if (strpos('si', strtolower($value)) !== false) {
                            $query->where($key, '=', 1);
                        } else if (strpos('no', strtolower($value)) !== false) {
                            $query->where($key, '=', 0);
                        } else {
                            $query->whereRaw('false');
                        }
                    } else if ($key) {
                        $query->where($key, 'LIKE', '%' . $value . '%');
                    }
                }
            }
        }
        if (!empty($sortBy)) {
            foreach ($sortBy as $sort) {
                if (isset($sort['key']) && isset($sort['order'])) {
                    $query->orderBy($sort['key'], $sort['order']);
                }
            }
        } else {
            $query->orderBy("id", "desc");
        }

        // Se paginan las inscripciones sin unir la tabla de estudiantes
        if ($itemsPerPage == -1) {
            $itemsPerPage = $query->count();
        }
        $items = $query->paginate($itemsPerPage);

        // A partir del paginado, extraemos los valores únicos de email, phone y dni (evitando dni nulos)
        $emails = $items->pluck('email')->filter()->unique();
        $phones = $items->pluck('phone')->filter()->unique();
        $dnis   = $items->pluck('dni')->filter()->unique();

        // Consultamos la tabla de estudiantes solo para este conjunto de valores
        $students = Student::with(['documents', 'formativeActions', 'notes.user'])
            ->where(function ($q) use ($emails, $phones, $dnis) {
                $q->whereIn('email', $emails)
                    ->orWhereIn('phone', $phones)
                    ->orWhereIn('dni', $dnis);
            })
            ->get();

        // Para facilitar la asignación, creamos índices basados en cada campo de comparación
        $studentsByEmail = $students->keyBy('email');
        $studentsByPhone = $students->keyBy('phone');
        $studentsByDni   = $students->filter(function ($student) {
            return !empty($student->dni);
        })->keyBy('dni');

        // Asociamos cada inscripción con el estudiante correspondiente, comprobando en cada campo
        $items->getCollection()->transform(function ($inscription) use ($studentsByEmail, $studentsByPhone, $studentsByDni) {
            $student = null;
            if ($inscription->email && isset($studentsByEmail[$inscription->email])) {
                $student = $studentsByEmail[$inscription->email];
            } elseif ($inscription->phone && isset($studentsByPhone[$inscription->phone])) {
                $student = $studentsByPhone[$inscription->phone];
            } elseif (!empty($inscription->dni) && isset($studentsByDni[$inscription->dni])) {
                $student = $studentsByDni[$inscription->dni];
            }
            $inscription->student = $student;
            return $inscription;
        });

        return [
            'tableData' => [
                'items'         => $items->items(),
                'itemsLength'   => $items->total(),
                'itemsPerPage'  => $items->perPage(),
                'page'          => $items->currentPage(),
                'sortBy'        => $sortBy,
                'search'        => $search,
                'deleted'       => $deleted,
            ],
        ];
    }

    public function store()
    {
        Inscription::create(
            Request::validate([
                'courses_id' => ['required'],
                'web' => ['required', 'max:191'],
                'island' => ['required', 'max:191'],
                'name' => ['required', 'max:191'],
                'email' => ['required', 'email', 'max:191'],
                'phone' => ['required', 'integer'],
                'dni' => ['nullable', 'max:191'],
                'advertising' => ['boolean'],
            ])
        );

        return Redirect::back()->with('success', 'Inscripción creada.');
    }

    public function update(Inscription $inscription)
    {
        $inscription->update(
            Request::validate([
                'courses_id' => ['required'],
                'web' => ['required', 'max:191'],
                'island' => ['required', 'max:191'],
                'name' => ['required', 'max:191'],
                'email' => ['required', 'email', 'max:191'],
                'phone' => ['required', 'integer'],
                'dni' => ['nullable', 'max:191'],
                'advertising' => ['boolean'],
            ])
        );

        return Redirect::back()->with('success', 'Inscripción actualizada.');
    }

    public function destroy(Inscription $inscription)
    {
        $inscription->delete();

        return Redirect::back()->with('success', 'Inscripción movida a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $inscription = Inscription::onlyTrashed()->findOrFail($id);
        $inscription->forceDelete();

        return Redirect::back()->with('success', 'Inscripción eliminada de forma permanente.');
    }

    public function restore($id)
    {
        $inscription = Inscription::onlyTrashed()->findOrFail($id);
        $inscription->restore();

        return Redirect::back()->with('success', 'Inscripción restaurada.');
    }

    public function exportExcel()
    {

        $items = Inscription::all();

        return  ['itemsExcel' => $items];
    }
}
