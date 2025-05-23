<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\Company;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use App\Exports\InternshipExcel;
use Maatwebsite\Excel\Facades\Excel;

class InternshipController extends Controller
{
    public function index()
    {
        return Inertia::render(
            'Dashboard/Internship',
            [
                'companies' => Inertia::lazy(fn() => Company::all()),
            ]
        );
    }

    public function loadItems()
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = Internship::with(['documents']);

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
        $validated = Request::validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'schedule' => ['nullable', 'max:45'],
            'companies_id' => ['required'],
            'formative_actions_has_students_id' => ['required'],
        ]);

        $duplicate = Internship::where('formative_actions_has_students_id', $validated['formative_actions_has_students_id'])
            ->where('companies_id', $validated['companies_id'])
            ->exists();

        if ($duplicate) {
            return Redirect::back()->withErrors([
                'companies_id' => 'Este estudiante ya tiene una práctica registrada en esta empresa.'
            ])->withInput();
        }

        $overlapping = Internship::where('formative_actions_has_students_id', $validated['formative_actions_has_students_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->exists();

        if ($overlapping) {
            return Redirect::back()->withErrors([
                'start_date' => 'Ya existe una práctica para este estudiante en las fechas indicadas.'
            ])->withInput();
        }

        $internship = Internship::create($validated);

        $internship = $internship->refresh();
        $item = $internship->formativeActionHasStudent->formativeAction->load(['course', 'sector', 'project', 'teacher', 'center', 'users', 'course.modules', 'module', 'modules', 'entity', 'students.modules', 'records.user', 'formativeActionHasStudent.internship.company', 'formativeActionHasStudent.student', 'formativeActionHasStudent.internship.documents']);

        return Redirect::back()->with(['success' => 'Práctica creada.', 'item' => $item, 'itemType' => 'formativeAction']);
    }

    public function update(Internship $internship)
    {
        $validated = Request::validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'schedule' => ['nullable', 'max:45'],
            'companies_id' => ['required'],
            'formative_actions_has_students_id' => ['required'],
        ]);

        $duplicate = Internship::where('formative_actions_has_students_id', $validated['formative_actions_has_students_id'])
            ->where('companies_id', $validated['companies_id'])
            ->where('id', '!=', $internship->id)
            ->exists();

        if ($duplicate) {
            return Redirect::back()->withErrors([
                'companies_id' => 'Este estudiante ya tiene una práctica registrada en esta empresa.'
            ])->withInput();
        }

        $overlapping = Internship::where('formative_actions_has_students_id', $validated['formative_actions_has_students_id'])
            ->where('id', '!=', $internship->id)
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->exists();

        if ($overlapping) {
            return Redirect::back()->withErrors([
                'start_date' => 'Ya existe una práctica para este estudiante en las fechas indicadas.'
            ])->withInput();
        }

        $internship->update($validated);

        $item = $internship->formativeActionHasStudent->formativeAction->load(['course', 'sector', 'project', 'teacher', 'center', 'users', 'course.modules', 'module', 'modules', 'entity', 'students.modules', 'records.user', 'formativeActionHasStudent.internship.company', 'formativeActionHasStudent.student', 'formativeActionHasStudent.internship.documents']);

        return Redirect::back()->with(['success' => 'Práctica actualizada.', 'item' => $item, 'itemType' => 'formativeAction']);
    }

    public function destroyPermanent($id)
    {
        $internship = Internship::findOrFail($id);

        $item = $internship->formativeActionHasStudent->formativeAction;

        $internship->delete();

        $item->load(['course', 'sector', 'project', 'teacher', 'center', 'users', 'course.modules', 'module', 'modules', 'entity', 'students.modules', 'records.user', 'formativeActionHasStudent.internship.company', 'formativeActionHasStudent.student', 'formativeActionHasStudent.internship.documents']);

        return Redirect::back()->with(['success' => 'Práctica eliminada permanentemente.', 'item' => $item, 'itemType' => 'formativeAction']);
    }

    public function exportExcel()
    {
        return Excel::download(new InternshipExcel, 'prácticas.xlsx');
    }
}
