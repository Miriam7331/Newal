<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use App\Models\ProfessionalFamily;
use App\Models\Module;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{

    public function loadAutocompleteItems()
    {
        $search = Request::get('search', '');

        $items = Course::whereRaw("CONCAT(name, ' (', code, ')') LIKE '%$search%'")->limit(6)->get();

        return ['autocompleteItems' => $items];
    }

    public function loadItems()
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = Course::with(['professionalFamily', 'modules']);

        if ($deleted) {
            $query->onlyTrashed();
        }

        if (!empty($search)) {
            foreach ($search as $key => $value) {
                if (!empty($value)) {
                    $query->where($key, 'LIKE', '%' . $value . '%');
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
        $course = Course::create(
            Request::validate([
                'name' => ['required', 'max:191'],
                'description' => ['nullable'],
                'level' => ['required', 'integer'],
                'professional_families_id' => ['required', 'integer'],
                'code' => ['required', 'max:191', 'unique:courses,code'],
            ])
        );

        $course->load('modules');

        return Redirect::back()->with(['success' => 'Curso creado.', 'item' => $course, 'itemType' => 'course']);
    }

    public function update(Course $course)
    {
        $course->update(
            Request::validate([
                'name' => ['required', 'max:191'],
                'description' => ['nullable'],
                'level' => ['required', 'integer'],
                'professional_families_id' => ['required', 'integer'],
                'code' => [
                    'required',
                    'max:191',
                    Rule::unique('courses', 'code')->ignore($course->id),
                ],
            ])
        );

        $course->load('modules');

        return Redirect::back()->with(['success' => 'Curso editado.', 'item' => $course, 'itemType' => 'course']);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return Redirect::back()->with('success', 'Curso movido a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->forceDelete();

        return Redirect::back()->with('success', 'Curso eliminado de forma permanente.');
    }

    public function restore($id)
    {
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->restore();

        return Redirect::back()->with('success', 'Curso restaurado.');
    }

    public function exportExcel()
    {
        $items = Course::all();

        return  ['itemsExcel' => $items];
    }

    public function addModule($id)
    {
        try {
            $request = Request::all();

            $course = Course::with("modules")->find($id);

            if ($course->modules->contains($request["modules_id"])) {
                return Redirect::back()->with('error', 'El curso ya tiene el módulo seleccionado.');
            }

            $course->modules()->attach($request["modules_id"]);

            $course->load("modules");

            return Redirect::back()->with(['success' => 'Módulo vinculado correctamente.', 'item' => $course, 'itemType' => 'course']);
        } catch (\Throwable $th) {
            return Redirect::back()->with('error', 'Error al vincular el módulo.');
        }
    }

    public function removeModule($id)
    {
        try {
            $request = Request::all();

            $course = Course::with("modules")->find($id);
            $course->modules()->detach($request["modules_id"]);

            $course->load("modules");

            return Redirect::back()->with(['success' => 'Módulo desvinculado correctamente.', 'item' => $course, 'itemType' => 'course']);
        } catch (\Throwable $th) {
            return Redirect::back()->with('error', 'Error al desvincular el módulo.');
        }
    }
}
