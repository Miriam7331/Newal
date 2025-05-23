<?php

namespace App\Http\Controllers;

use App\Models\FormativeAction;
use App\Models\Course;
use App\Models\Module;
use App\Models\Sector;
use App\Models\Teacher;
use App\Models\Center;
use App\Models\Entity;
use App\Models\User;
use App\Models\Student;
use App\Models\Project;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use App\Models\ProfessionalFamily;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class FormativeActionController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard/FormativeAction', [
            'courses' => Inertia::lazy(fn() => Course::all()),
            'modules' => Inertia::lazy(fn() => Module::all()),
            'sectors' => Inertia::lazy(fn() => Sector::all()),
            'teachers' => Inertia::lazy(fn() => Teacher::all()),
            'centers' => Inertia::lazy(fn() => Center::all()),
            'entities' => Inertia::lazy(fn() => Entity::all()),
            'professionalFamilies' => Inertia::lazy(fn() => ProfessionalFamily::all()),
            'users' => Inertia::lazy(fn() => User::all()),
            'students' => Inertia::lazy(fn() => Student::all()),
            'projects' => Inertia::lazy(fn() => Project::all()),
            'companies' => Inertia::lazy(fn() => Company::all()),
        ]);
    }

    public function getFormativeActionsByProject($project)
    {
        return Inertia::render('Dashboard/FormativeAction', [
            'courses' => Inertia::lazy(fn() => Course::all()),
            'modules' => Inertia::lazy(fn() => Module::all()),
            'sectors' => Inertia::lazy(fn() => Sector::all()),
            'teachers' => Inertia::lazy(fn() => Teacher::all()),
            'centers' => Inertia::lazy(fn() => Center::all()),
            'entities' => Inertia::lazy(fn() => Entity::all()),
            'professionalFamilies' => Inertia::lazy(fn() => ProfessionalFamily::all()),
            'users' => Inertia::lazy(fn() => User::all()),
            'students' => Inertia::lazy(fn() => Student::all()),
            'projects' => Inertia::lazy(fn() => Project::all()),
            'project' => Project::findOrFail($project),
            'companies' => Inertia::lazy(fn() => Company::all()),
        ]);
    }

    public function loadItems()
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = FormativeAction::with(['course', 'sector', 'project', 'teacher', 'center', 'users', 'course.modules', 'module', 'modules', 'entity', 'students.modules', 'records.user', 'formativeActionHasStudent.internship.company', 'formativeActionHasStudent.internship.documents', 'formativeActionHasStudent.student']);

        $database = env('DB_DATABASE2');

        $query->leftJoin("$database.courses", 'formative_actions.courses_id', '=', 'courses.id');
        $query->leftJoin("$database.modules", 'formative_actions.modules_id', '=', 'modules.id');
        $query->join("$database.sectors", 'formative_actions.sectors_id', '=', 'sectors.id');
        $query->join('centers', 'formative_actions.centers_id', '=', 'centers.id');
        $query->join('entities', 'formative_actions.entities_id', '=', 'entities.id');
        $query->leftJoin('projects', 'formative_actions.projects_id', '=', 'projects.id');
        $query->select(
            DB::raw("COALESCE(courses.name, modules.name) AS course_or_module_name"),
            "sectors.name AS sector_name",
            "centers.name AS center_name",
            "entities.name AS entity_name",
            "projects.name AS project_name",
            "formative_actions.*"
        );

        if ($deleted) {
            $query->onlyTrashed();
        }

        if (!empty($search)) {
            foreach ($search as $key => $value) {
                if (!empty($value)) {
                    if ($key === 'code') {
                        $query->where('formative_actions.code', 'LIKE', '%' . $value . '%');
                    } elseif ($key === 'course_or_module_name') {
                        $query->where(function ($query) use ($value) {
                            $query->where('courses.name', 'LIKE', '%' . $value . '%')
                                ->orWhere('modules.name', 'LIKE', '%' . $value . '%');
                        });
                    } else if ($key === 'sector_name') {
                        $query->where('sectors.name', 'LIKE', '%' . $value . '%');
                    } else if ($key === 'center_name') {
                        $query->where('centers.name', 'LIKE', '%' . $value . '%');
                    } else if ($key === 'entity_name') {
                        $query->where('entities.name', 'LIKE', '%' . $value . '%');
                    } else if ($key === 'project_name') {
                        $query->where('projects.name', 'LIKE', '%' . $value . '%');
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

        if (!Auth::user()->centers->contains(Request::get('centers_id')) && !Auth::user()->admin) {
            return Redirect::back()->with('error', 'No tienes permisos para crear una acción formativa en este centro.');
        }

        $formativeAction = FormativeAction::create(
            Request::validate([
                'code' => ['required', 'max:191'],
                'islands' => ['required', 'max:191'],
                'courses_id' => ['nullable'],
                'modules_id' => ['nullable'],
                'sectors_id' => ['required'],
                'teachers_id' => ['nullable'],
                'centers_id' => ['required'],
                'entities_id' => ['required'],
                'min_quota' => ['nullable', 'integer'],
                'min_quota_to_end' => ['nullable', 'integer'],
                'max_quota' => ['nullable', 'integer'],
                'schedule' => ['nullable', 'max:45'],
                'receiver' => ['nullable', 'max:45'],
                'start_date' => ['nullable', 'date'],
                'max_inscription_date' => ['nullable', 'date', 'after:start_date'],
                'end_date' => ['nullable', 'date'],
                'price' => ['required', 'integer'],
                'type' => ['nullable', 'max:45'],
                'requirements' => ['nullable'],
                'projects_id' => ['nullable'],
            ])
        );

        $formativeAction->load(['course', 'sector', 'teacher', 'center', 'users', 'modules', 'records.user', 'formativeActionHasStudent.internship.company', 'formativeActionHasStudent.student', 'formativeActionHasStudent.internship.documents']);

        return Redirect::back()->with(['success' => 'Acción Formativa creada.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
    }

    public function update(FormativeAction $formativeAction)
    {
        if (!Auth::user()->centers->contains(Request::get('centers_id')) && !Auth::user()->admin) {
            return Redirect::back()->with('error', 'No tienes permisos para crear una acción formativa en este centro.');
        }
        $formativeAction->update(
            Request::validate([
                'code' => ['required', 'max:191'],
                'islands' => ['required', 'max:191'],
                'courses_id' => ['nullable'],
                'modules_id' => ['nullable'],
                'sectors_id' => ['required'],
                'teachers_id' => ['nullable', 'exists:teachers,id'],
                'centers_id' => ['required', 'exists:centers,id'],
                'entities_id' => ['required', 'exists:entities,id'],
                'min_quota' => ['nullable', 'integer'],
                'min_quota_to_end' => ['nullable', 'integer'],
                'max_quota' => ['nullable', 'integer'],
                'schedule' => ['nullable', 'max:45'],
                'receiver' => ['nullable', 'max:45'],
                'start_date' => ['required', 'date'],
                'max_inscription_date' => ['nullable', 'date', 'after:start_date'],
                'end_date' => ['required', 'date'],
                'price' => ['required', 'integer'],
                'type' => ['nullable', 'max:45'],
                'requirements' => ['nullable'],
                'projects_id' => ['nullable'],
            ])
        );

        $formativeAction->load(['course', 'project', 'sector', 'teacher', 'center', 'users', 'modules', 'records.user', 'formativeActionHasStudent.internship.company', 'formativeActionHasStudent.student', 'formativeActionHasStudent.internship.documents']);

        return Redirect::back()->with(['success' => 'Acción Formativa actualizada.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
    }

    public function destroy(FormativeAction $formativeAction)
    {
        if (!Auth::user()->centers->contains($formativeAction->centers_id) && !Auth::user()->admin) {
            return Redirect::back()->with('error', 'No tienes permisos para eliminar una acción formativa en este centro.');
        }
        $formativeAction->delete();

        return Redirect::back()->with('success', 'Acción Formativa movida a la papelera.');
    }

    public function destroyPermanent($id)
    {
        if (!Auth::user()->centers->contains(FormativeAction::onlyTrashed()->findOrFail($id)->centers_id) && !Auth::user()->admin) {
            return Redirect::back()->with('error', 'No tienes permisos para eliminar una acción formativa en este centro.');
        }
        $formativeAction = FormativeAction::onlyTrashed()->findOrFail($id);

        $formativeAction->forceDelete();

        return Redirect::back()->with('success', 'Acción Formativa eliminada de forma permanente.');
    }

    public function restore($id)
    {
        if (!Auth::user()->centers->contains(FormativeAction::onlyTrashed()->findOrFail($id)->centers_id) && !Auth::user()->admin) {
            return Redirect::back()->with('error', 'No tienes permisos para eliminar una acción formativa en este centro.');
        }
        $formativeAction = FormativeAction::onlyTrashed()->findOrFail($id);
        $formativeAction->restore();

        return Redirect::back()->with('success', 'Acción Formativa restaurada.');
    }

    public function exportExcel()
    {
        $query = FormativeAction::with(['course', 'project', 'sector', 'teacher', 'records.user']);

        $database = env('DB_DATABASE2');

        $query->join("$database.courses", 'formative_actions.courses_id', '=', 'courses.id');
        $query->join('sectors', 'formative_actions.sectors_id', '=', 'sectors.id');
        $query->join('projects', 'formative_actions.projects_id', '=', 'projects.id');
        $query->join('centers', 'formative_actions.centers_id', '=', 'centers.id')
            ->select("courses.name AS course_name", "sectors.name AS sector_name", "centers.name AS center_name", "formative_actions.*");

        $items = $query->get();

        return  ['itemsExcel' => $items];
    }

    public function addUser($id)
    {
        if (!Auth::user()->centers->contains(FormativeAction::findOrFail($id)->centers_id) && !Auth::user()->admin) {
            return Redirect::back()->with('error', 'No tienes permisos para añadir un usuario a esta acción formativa.');
        }
        try {
            $formativeAction = FormativeAction::findOrFail($id);
            $formativeAction->users()->attach(Request::validate([
                'users_id' => ['required', 'exists:users,id'],
            ]));

            $formativeAction->load(['course', 'project', 'sector', 'teacher', 'center', 'users', 'modules', 'records.user']);

            return Redirect::back()->with(['success' => 'Usuario añadido a la acción formativa.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
        } catch (\Throwable $th) {
            return Redirect::back()->with('error', 'Error al añadir el usuario a la acción formativa.');
        }
    }

    public function removeUser($id)
    {
        if (!Auth::user()->centers->contains(FormativeAction::findOrFail($id)->centers_id) && !Auth::user()->admin) {
            return Redirect::back()->with('error', 'No tienes permisos para eliminar un usuario de esta acción formativa.');
        }
        try {
            $formativeAction = FormativeAction::findOrFail($id);
            $formativeAction->users()->detach(Request::validate([
                'users_id' => ['required', 'exists:users,id'],
            ]));

            $formativeAction->load(['course', 'project', 'sector', 'teacher', 'center', 'users', 'modules', 'records.user']);

            return Redirect::back()->with(['success' => 'Usuario eliminado de la acción formativa.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
        } catch (\Throwable $th) {
            return Redirect::back()->with('error', 'Error al eliminar el usuario de la acción formativa.');
        }
    }

    public function addModule($id)
    {
        if (!Auth::user()->centers->contains(FormativeAction::findOrFail($id)->centers_id)) {
            return Redirect::back()->with('error', 'No tienes permisos para añadir un modulo a esta acción formativa.');
        }
        try {
            $formativeAction = FormativeAction::findOrFail($id);
            $formativeAction->modules()->attach(Request::validate([
                'modules_id' => ['required'],
            ]));

            $formativeAction->load(['course', 'project', 'sector', 'teacher', 'center', 'users', 'modules', 'records.user']);

            return Redirect::back()->with(['success' => 'Modulo añadido a la acción formativa.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
        } catch (\Throwable $th) {
            return Redirect::back()->with('error', 'Error al añadir el modulo a la acción formativa.');
        }
    }

    public function removeModule($id)
    {
        if (!Auth::user()->centers->contains(FormativeAction::findOrFail($id)->centers_id)) {
            return Redirect::back()->with('error', 'No tienes permisos para eliminar un modulo de esta acción formativa.');
        }
        try {
            $formativeAction = FormativeAction::findOrFail($id);
            $formativeAction->modules()->detach(Request::validate([
                'modules_id' => ['required'],
            ]));

            $formativeAction->load(['course', 'project', 'sector', 'teacher', 'center', 'users', 'modules', 'records.user']);

            return Redirect::back()->with(['success' => 'Modulo eliminado de la acción formativa.', 'item' => $formativeAction, 'itemType' => 'formativeAction']);
        } catch (\Throwable $th) {
            return Redirect::back()->with('error', 'Error al eliminar el modulo de la acción formativa.');
        }
    }

    public function getFormativeAction($id)
    {
        $formativeAction = FormativeAction::findOrFail($id);
        $formativeAction->load(['course', 'project', 'sector', 'teacher', 'center', 'users', 'course.modules', 'module', 'modules', 'students.modules', 'records.user']);

        return Redirect::back()->with(['item' => $formativeAction, 'itemType' => 'formativeAction']);
    }
}
