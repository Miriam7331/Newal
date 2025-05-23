<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use App\Models\FormativeAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class StudentController extends Controller
{

    public function index()
    {
        return Inertia::render('Dashboard/Student', [
            'students' => Inertia::lazy(fn() => Student::all()),
            'formativeActions' => Inertia::lazy(fn() => FormativeAction::with(['course', 'module', 'center'])->get()),
        ]);
    }


    public function loadItems()
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = Student::with(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'documents', 'notes.user', 'records.user']);

        if ($deleted) {
            $query->onlyTrashed();
        }

        if (!empty($search)) {
            foreach ($search as $key => $value) {
                if (!empty($value)) {
                    if ($key == 'formative_actions' && $value == 'con') {
                        $query->whereHas('formativeActions');
                    } else if ($key == 'formative_actions' && $value != 'con') {
                        $query->whereDoesntHave('formativeActions');
                    } else {
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

        if ($itemsPerPage == -1) {
            $itemsPerPage = $query->count();
        }

        $items = $query->paginate($itemsPerPage);

        return [
            'tableData' => [
                'items' => $items->items(),
                'itemsLength' => $items->total(),
                'itemsPerPage' => $items->perPage(),
                'page' => $items->currentPage(),
                'sortBy' => $sortBy,
                'search' => $search,
                'deleted' => $deleted,
            ]
        ];
    }

    public function store()
    {
        $student = Student::create(
            Request::validate([
                'name'      => ['required', 'max:191'],
                'email'     => ['required', 'email', 'max:191'],
                'level'     => ['nullable', 'integer', 'between:1,3'],
                'dni'       => ['nullable', 'unique:students,dni', 'regex:/^([0-9]{8}[A-Z])|[XYZ][0-9]{7}[A-Z]$/'],
                'ssn'       => ['nullable', 'regex:/^[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}$/'],
                'phone'     => ['required', 'integer'],
                'address'   => ['nullable', 'max:191'],
                'cp'        => ['nullable', 'digits:5'],
                'city'      => ['nullable', 'max:191'],
                'province'  => ['nullable', 'max:191'],
                'island'    => ['nullable', 'max:191'],
                'gender'    => ['nullable', 'in:Masculino,Femenino,Otro'],
                'birthdate' => ['nullable', 'date'],
                'consent'   => ['boolean'],
                'disability' => ['nullable', 'integer', 'between:0,100'],
            ])
        );

        $student->load(['formativeActions', 'documents', 'notes.user', 'records.user']);

        return Redirect::back()->with(['success' => 'Estudiante creado.', 'item' => $student, 'itemType' => 'student']);
    }

    public function update(Student $student)
    {
        $student->update(
            Request::validate([
                'name'      => ['required', 'max:191'],
                'email'     => ['required', 'email', 'max:191'],
                'level'     => ['nullable', 'integer', 'between:1,3'],
                'dni'       => ['nullable', 'unique:students,dni,' . $student->id, 'regex:/^([0-9]{8}[A-Z])|[XYZ][0-9]{7}[A-Z]$/'],
                'ssn'       => ['nullable', 'regex:/^[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}$/'],
                'phone'     => ['required', 'integer'],
                'address'   => ['nullable', 'max:191'],
                'cp'        => ['nullable', 'digits:5'],
                'city'      => ['nullable', 'max:191'],
                'province'  => ['nullable', 'max:191'],
                'island'    => ['nullable', 'max:191'],
                'gender'    => ['nullable', 'in:Masculino,Femenino,Otro'],
                'birthdate' => ['nullable', 'date'],
                'consent'   => ['boolean'],
                'disability' => ['nullable', 'integer', 'between:0,100'],
            ])
        );

        $student->load(['formativeActions', 'documents', 'notes.user', 'records.user']);

        return Redirect::back()->with(['success' => 'Estudiante editado.', 'item' => $student, 'itemType' => 'student']);
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return Redirect::back()->with('success', 'Estudiante movido a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $student = Student::onlyTrashed()->findOrFail($id);
        $student->forceDelete();

        return Redirect::back()->with('success', 'Estudiante eliminado de forma permanente.');
    }

    public function restore($id)
    {
        $student = Student::onlyTrashed()->findOrFail($id);
        $student->restore();

        return Redirect::back()->with('success', 'Estudiante restaurado.');
    }

    public function exportExcel()
    {
        $items = Student::all();

        return  ['itemsExcel' => $items];
    }

    public function convertToComparableTime($hour)
    {
        $parts = explode(':', $hour);
        return (int)$parts[0] * 60 + (int)$parts[1];
    }

    public function rangesOverlap($range1, $range2)
    {
        list($start1, $end1) = explode('-', $range1);
        list($start2, $end2) = explode('-', $range2);

        $start1 = $this->convertToComparableTime($start1);
        $end1 = $this->convertToComparableTime($end1);
        $start2 = $this->convertToComparableTime($start2);
        $end2 = $this->convertToComparableTime($end2);

        return $start1 < $end2 && $start2 < $end1;
    }

    public function addFormativeAction($id)
    {
        $request = Request::all();

        $formativeAction = FormativeAction::find($request["formative_actions_id"]);

        if (!Auth::user()->formativeActions->contains(Request::get('formative_actions_id')) && !Auth::user()->centers->contains($formativeAction->first()->centers_id) && !Auth::user()->admin) {
            return Redirect::back()->with('error', 'Error al vincular la acción formativa. No tiene permisos para vincular la acción formativa.');
        }
        try {

            $student = Student::find($id);

            $studentFormativeActions = $student->formativeActions()->whereNot('status', 'Abandonado')->whereNot('status', 'No apto')->get();

            $student->load(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'records.user']);



            $oneDay = 86400; // 1 día en segundos

            foreach ($studentFormativeActions as $studentFormativeAction) {
                // Obtenemos fechas de inicio/fin reales
                $studentStartDate = $studentFormativeAction->max_inscription_date
                    ? $studentFormativeAction->max_inscription_date
                    : $studentFormativeAction->start_date;

                $startDate = $formativeAction->max_inscription_date
                    ? $formativeAction->max_inscription_date
                    : $formativeAction->start_date;

                // Convertimos a timestamps
                $studentStartTs = strtotime($studentStartDate);
                $studentEndTs   = strtotime($studentFormativeAction->end_date);
                $newStartTs     = strtotime($startDate);
                $newEndTs       = strtotime($formativeAction->end_date);

                // Sumamos/restamos 1 día de margen
                // Ajusta según tu lógica deseada:
                //   - Si quieres permitir que estén "casi pegados" sin considerarse solapados, 
                //     puedes restar 1 día al inicio y sumar 1 día al fin, por ejemplo.
                $studentStartTsMargin = $studentStartTs + $oneDay;
                $studentEndTsMargin   = $studentEndTs   - $oneDay;
                $newStartTsMargin     = $newStartTs     + $oneDay;
                $newEndTsMargin       = $newEndTs       - $oneDay;

                // Ahora comprobamos solapamiento con el margen aplicado
                if ($studentStartTsMargin <= $newEndTsMargin && $studentEndTsMargin >= $newStartTsMargin) {
                    // Si además quieres comprobar solapamiento de horarios concretos, 
                    // deberías ajustar también tu función rangesOverlap para reflejar ese margen (si procede).
                    if ($this->rangesOverlap($studentFormativeAction->schedule, $formativeAction->schedule)) {
                        return Redirect::back()
                            ->with([
                                'error' => 'Error al vincular la acción formativa. El estudiante ya está en otra AF en fecha/hora coincidente.',
                                'item' => $student,
                                'itemType' => 'student'
                            ]);
                    }
                }
            }

            $student->formativeActions()->attach($request["formative_actions_id"], ['status' => $request["status"]]);

            if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                $student->load(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'records.user']);
                return Redirect::back()->with(['success' => 'Acción formativa vinculada correctamente.', 'item' => $student, 'itemType' => 'student']);
            } else {
                $formativeAction->load(['course', 'course.modules', 'module', 'students.modules']);
                return Redirect::back()->with(['success' => 'Acción formativa vinculada correctamente.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
            }
        } catch (\Throwable $th) {
            return Redirect::back()->with('error', 'Error al vincular la acción formativa.');
        }
    }

    public function removeFormativeAction($id)
    {

        $request = Request::all();

        $formativeAction = FormativeAction::find($request["formative_actions_id"]);

        if (!Auth::user()->formativeActions->contains(Request::get('formative_actions_id')) && !Auth::user()->centers->contains($formativeAction->first()->centers_id) && !Auth::user()->admin) {
            return Redirect::back()->with('error', 'Error al vincular la acción formativa. No tiene permisos para vincular la acción formativa.');
        }
        try {

            $student = Student::find($id);
            $student->formativeActions()->detach($request["formative_actions_id"]);

            if ($request["formative_actions_id"]) {
                DB::table('students_has_modules')
                    ->where('students_id', $id)
                    ->where('formative_actions_id', $request["formative_actions_id"])
                    ->delete();
            }

            if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                $student->load(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'records.user']);
                return Redirect::back()->with(['success' => 'Acción formativa desvinculada correctamente.', 'item' => $student, 'itemType' => 'student']);
            } else {
                $formativeAction = FormativeAction::find($request["formative_actions_id"]);
                $formativeAction->load(['course', 'course.modules', 'module', 'students.modules']);
                return Redirect::back()->with(['success' => 'Acción formativa desvinculada correctamente.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
            }
        } catch (\Throwable $th) {
            return Redirect::back()->with('error', 'Error al desvincular la acción formativa.');
        }
    }

    public function changeFormativeActionStatus($id)
    {
        $request = Request::all();

        $formativeAction = FormativeAction::find($request["formative_actions_id"]);

        if (!Auth::user()->formativeActions->contains(Request::get('formative_actions_id')) && !Auth::user()->centers->contains($formativeAction->centers_id) && !Auth::user()->admin) {
            return Redirect::back()->with('error', 'Error al vincular la acción formativa. No tiene permisos para vincular la acción formativa.');
        }

        $student = Student::find($id);

        if ($formativeAction->courses_id) {
            $modules = $student->formativeActions()->where('formative_actions_id', $request["formative_actions_id"])->first()->course->modules;

            $allModulesApproved = true;
            foreach ($modules as $module) {
                $moduleApproved = $student->modules()->wherePivot('formative_actions_id', $request["formative_actions_id"])->wherePivot('modules_id', $module->id)->first();
                if (!$moduleApproved || $moduleApproved->pivot->status !== "Apto" && $moduleApproved->pivot->status !== "Convalidado") {
                    $allModulesApproved = false;
                }
            }
        } else if ($formativeAction->modules_id) {
            $allModulesApproved = true;
        }

        if ($allModulesApproved && $request["status"] === "Apto" && $formativeAction->end_date) {

            DB::table('formative_actions_has_students')
                ->where('students_id', $id)
                ->where('formative_actions_id', $request["formative_actions_id"])
                ->update(['status' => $request["status"]]);

            if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                $student->load(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'records.user']);
                return Redirect::back()->with(['success' => 'Acción formativa aprobada correctamente', 'item' => $student, 'itemType' => 'student']);
            } else {
                $formativeAction->load(['course', 'course.modules', 'module', 'students.modules']);
                return Redirect::back()->with(['success' => 'Acción formativa aprobada correctamente', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
            }
        } else if ($request["status"] === "Apto" && $formativeAction->end_date) {

            if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                $student->load(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'records.user']);
                return Redirect::back()->with(['error' => 'Error al aprobar la acción formativa. Compruebe que todos los módulos están aprobados.', 'item' => $student, 'itemType' => 'student']);
            } else {
                $formativeAction->load(['course', 'course.modules', 'module', 'students.modules']);
                return Redirect::back()->with(['error' => 'Error al aprobar la acción formativa. Compruebe que todos los módulos están aprobados.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
            }
        } else if ($request["status"] === "No apto" && $formativeAction->end_date) {

            DB::table('formative_actions_has_students')
                ->where('students_id', $id)
                ->where('formative_actions_id', $request["formative_actions_id"])
                ->update(['status' => $request["status"]]);


            if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                $student->load(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'records.user']);
                return Redirect::back()->with(['success' => 'Acción formativa suspendida correctamente', 'item' => $student, 'itemType' => 'student']);
            } else {
                $formativeAction->load(['course', 'course.modules', 'module', 'students.modules']);
                return Redirect::back()->with(['success' => 'Acción formativa suspendida correctamente', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
            }
        } else if ($request["status"] == "Convalidado" && $formativeAction->end_date) {
            DB::table('formative_actions_has_students')
                ->where('students_id', $id)
                ->where('formative_actions_id', $request["formative_actions_id"])
                ->update(['status' => $request["status"]]);

            if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                $student->load(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'records.user']);
                return Redirect::back()->with(['success' => 'Acción formativa convalidada correctamente', 'item' => $student, 'itemType' => 'student']);
            } else {
                $formativeAction->load(['course', 'course.modules', 'module', 'students.modules']);
                return Redirect::back()->with(['success' => 'Acción formativa convalidada correctamente', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
            }
        } else if ($request["status"] == "Abandonado") {
            DB::table('formative_actions_has_students')
                ->where('students_id', $id)
                ->where('formative_actions_id', $request["formative_actions_id"])
                ->update(['status' => $request["status"]]);

            if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                $student->load(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'records.user']);
                return Redirect::back()->with(['success' => 'Acción formativa abandonada correctamente', 'item' => $student, 'itemType' => 'student']);
            } else {
                $formativeAction->load(['course', 'course.modules', 'module', 'students.modules']);
                return Redirect::back()->with(['success' => 'Acción formativa abandonada correctamente', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
            }
        } else if (!$formativeAction->end_date) {

            if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                $student->load(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'records.user']);
                return Redirect::back()->with(['error' => 'Error al modificar la acción formativa. Compruebe que la fecha de finalización está rellena.', 'item' => $student, 'itemType' => 'student']);
            } else {
                $formativeAction->load(['course', 'course.modules', 'module', 'students.modules']);
                return Redirect::back()->with(['error' => 'Error al modificar la acción formativa. Compruebe que la fecha de finalización está rellena.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
            }
        }
    }


    public function changeModuleStatus($id)
    {

        $request = Request::all();

        $formativeAction = FormativeAction::find($request["formative_actions_id"]);

        if (!Auth::user()->formativeActions->contains(Request::get('formative_actions_id')) && !Auth::user()->centers->contains($formativeAction->centers_id) && !Auth::user()->admin) {
            return Redirect::back()->with('error', 'Error al vincular la acción formativa. No tiene permisos para vincular la acción formativa.');
        }
        try {

            $student = Student::find($id);

            $module = $student->modules()
                ->wherePivot('formative_actions_id', $request["formative_actions_id"])
                ->wherePivot('modules_id', $request["modules_id"])
                ->first();

            if ($module) {
                if ($request["status"]) {
                    DB::table('students_has_modules')
                        ->where('students_id', $id)
                        ->where('formative_actions_id', $request["formative_actions_id"])
                        ->where('modules_id', $request["modules_id"])
                        ->update(['status' => $request["status"]]);
                } else {
                    DB::table('students_has_modules')
                        ->where('students_id', $id)
                        ->where('formative_actions_id', $request["formative_actions_id"])
                        ->where('modules_id', $request["modules_id"])
                        ->delete();
                }
            } else {
                $student->modules()->attach($request["modules_id"], [
                    'formative_actions_id' => $request["formative_actions_id"],
                    'status' => $request["status"]
                ]);
            }

            if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                $student->load(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'records.user']);
            } else {
                $formativeAction = FormativeAction::find($request["formative_actions_id"]);
                $formativeAction->load(['course', 'course.modules', 'module', 'students.modules']);
            }

            if ($request["status"] == 'Apto') {
                if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                    return Redirect::back()->with(['success' => 'Módulo aprobado correctamente.', 'item' => $student, 'itemType' => 'student']);
                } else {
                    return Redirect::back()->with(['success' => 'Módulo aprobado correctamente.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
                }
            } else if ($request["status"] == 'No apto') {
                if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                    return Redirect::back()->with(['success' => 'Módulo desaprobado correctamente.', 'item' => $student, 'itemType' => 'student']);
                } else {
                    return Redirect::back()->with(['success' => 'Módulo desaprobado correctamente.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
                }
            } else if ($request["status"] == 'Convalidado') {
                if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                    return Redirect::back()->with(['success' => 'Módulo convalidado correctamente.', 'item' => $student, 'itemType' => 'student']);
                } else {
                    return Redirect::back()->with(['success' => 'Módulo convalidado correctamente.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
                }
            } else {
                if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                    return Redirect::back()->with(['success' => 'Módulo reseteado correctamente.', 'item' => $student, 'itemType' => 'student']);
                } else {
                    return Redirect::back()->with(['success' => 'Módulo reseteado correctamente.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
                }
            }
            if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
                return Redirect::back()->with(['success' => 'Módulo modificado correctamente.', 'item' => $student, 'itemType' => 'student']);
            } else {
                return Redirect::back()->with(['success' => 'Módulo modificado correctamente.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
            }
        } catch (\Throwable $th) {
            return Redirect::back()->with('error', 'Error al aprobar el módulo.');
        }
    }

    public function addDiploma()
    {
        $data = Request::validate([
            'students_id' => ['required', 'exists:students,id'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:1024'],
            'formative_actions_id' => ['required', 'exists:formative_actions,id'],
        ]);

        $file = Request::file('file');
        $originalName = $file->getClientOriginalName();
        $fileContent = file_get_contents($file->getRealPath());

        // Encriptar el contenido del archivo
        $encryptedContent = Crypt::encrypt($fileContent);

        // Generar un nombre único para el archivo
        $uniqueFileName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

        // Guardar el archivo encriptado con un nombre único
        $path = 'diplomas/' . $uniqueFileName;
        Storage::put($path, $encryptedContent);

        DB::table('formative_actions_has_students')
            ->where('students_id', $data['students_id'])
            ->where('formative_actions_id', $data['formative_actions_id'])
            ->update(['file' => $path, 'original_name' => $originalName]);

        $request = Request::all();

        $formativeAction = FormativeAction::find($data['formative_actions_id']);
        $student = Student::find($data['students_id']);

        $formativeAction->load(['course', 'course.modules', 'module', 'students.modules']);
        $student->load(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'records.user']);

        if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
            return Redirect::back()->with(['success' => 'Diploma añadido.', 'item' => $student, 'itemType' => 'student']);
        } else {
            return Redirect::back()->with(['success' => 'Diploma añadido.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
        }
    }

    public function downloadDiploma($students_id, $formative_actions_id)
    {

        $diploma = DB::table('formative_actions_has_students')
            ->where('students_id', $students_id)
            ->where('formative_actions_id', $formative_actions_id)
            ->first();

        $filePath = $diploma->file;
        $originalName = $diploma->original_name;

        if (Storage::exists($filePath)) {
            // Obtener el contenido encriptado del archivo
            $encryptedContent = Storage::get($filePath);

            // Desencriptar el contenido del archivo
            $decryptedContent = Crypt::decrypt($encryptedContent);

            // Retornar la respuesta de descarga con el contenido desencriptado y el nombre original
            return response($decryptedContent)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="' . $originalName . '"');
        } else {
            abort(404, 'El archivo no existe.');
        }
    }

    public function deleteDiploma($students_id, $formative_actions_id)
    {
        $diploma = DB::table('formative_actions_has_students')
            ->where('students_id', $students_id)
            ->where('formative_actions_id', $formative_actions_id);

        $filePath = $diploma->first()->file;

        $diploma->update(['file' => null, 'original_name' => null]);

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        $request = Request::all();

        $formativeAction = FormativeAction::find($formative_actions_id);
        $student = Student::find($students_id);

        $formativeAction->load(['course', 'course.modules', 'module', 'students.modules']);
        $student->load(['modules', 'formativeActions', 'formativeActions.center', 'formativeActions.course.modules', 'formativeActions.module', 'records.user']);

        if (!array_key_exists("origin", $request) || $request["origin"] != "formativeAction") {
            return Redirect::back()->with(['success' => 'Diploma borrado.', 'item' => $student, 'itemType' => 'student']);
        } else {
            return Redirect::back()->with(['success' => 'Diploma borrado.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
        }
    }

    public function loadAutocompleteItems()
    {
        $search = Request::get('search', '');

        $items = Student::whereRaw("CONCAT(name, ' - ', dni) LIKE '%$search%'")->limit(6)->get();

        return ['autocompleteItems' => $items];
    }

    public function exportToPdf(Student $student)
    {
        // Cargar el estudiante con sus relaciones
        $student->load([
            'formativeActions', 
            'formativeActions.center', 
            'formativeActions.course', 
            'formativeActions.module'
        ]);

        // Generar el PDF
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.student', [
            'student' => $student,
        ]);

        // Descargar el PDF
        return $pdf->download('estudiante-' . $student->id . '.pdf');
    }
}
