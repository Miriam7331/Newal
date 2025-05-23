<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class TeacherController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard/Teacher');
    }

    public function loadItems() 
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = Teacher::query();

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
        $teacher = Teacher::create(
            Request::validate([
                'name' => ['required', 'max:191'],
                'surnames' => ['required', 'max:191'],
                'info' => ['nullable', 'max:1000'],
            ])
        );

        return Redirect::back()->with(['success' => 'Profesor creado.', 'item' => $teacher, 'itemType' => 'teacher']);
    }

    public function update(Teacher $teacher)
    {
        $teacher->update(
            Request::validate([
                'name' => ['required', 'max:191'],
                'surnames' => ['required', 'max:191'],
                'info' => ['nullable', 'max:1000'],
            ])
        );

        return Redirect::back()->with('success', 'Profesor editado.');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return Redirect::back()->with('success', 'Profesor movido a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $teacher = Teacher::onlyTrashed()->findOrFail($id);
        $teacher->forceDelete();

        return Redirect::back()->with('success', 'Profesor eliminado de forma permanente.');
    }

    public function restore($id)
    {
        $teacher = Teacher::onlyTrashed()->findOrFail($id);
        $teacher->restore();

        return Redirect::back()->with('success', 'Profesor restaurado.');
    }

    public function exportExcel()
    {
        $items = Teacher::all();

        return  [ 'itemsExcel' => $items ];
    } 
}
