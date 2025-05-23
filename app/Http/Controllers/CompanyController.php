<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{

    public function index()
    {
        return Inertia::render(
            'Dashboard/Company'
        );
    }

    public function loadItems()
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = Company::with(['documents', 'contacts']);

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
        $company = Company::create(
            Request::validate([
                'name' => ['required', 'max:191'],
                'cif'       => ['required', 'unique:companies,cif', 'regex:/^[0-9]{8}[A-Z]$|^[A-Z][0-9]{8}$/'],
                'email'     => ['nullable', 'email', 'max:191'],
                'phone'     => ['nullable', 'integer'],
                'island' => ['required', 'max:191'],
                'municipality' => ['nullable', 'max:191'],
                'cp'        => ['required', 'digits:5'],
                'address'   => ['nullable', 'max:191'],
                'number' => ['nullable', 'integer', 'max:10'],
                'floor' => ['nullable', 'integer', 'max:10'],
                'door' => ['nullable', 'max:10'],
            ])
        );

        $company->load(['documents', 'contacts']);

        return Redirect::back()->with(['success' => 'Empresa creada.', 'item' => $company, 'itemType' => 'company']);
    }

    public function update(Company $company)
    {
        $company->update(
            Request::validate([
                'name' => ['required', 'max:191'],
                'cif' => [
                    'required',
                    Rule::unique('companies', 'cif')->ignore($company->id),
                    'regex:/^[0-9]{8}[A-Z]$|^[A-Z][0-9]{8}$/'
                ],
                'email'     => ['nullable', 'email', 'max:191'],
                'phone'     => ['nullable', 'integer'],
                'island' => ['required', 'max:191'],
                'municipality' => ['nullable', 'max:191'],
                'cp'        => ['required', 'digits:5'],
                'address'   => ['nullable', 'max:191'],
                'number' => ['nullable', 'max:10'],
                'floor' => ['nullable', 'max:10'],
                'door' => ['nullable', 'max:10'],
            ])
        );

        $company->load(['documents', 'contacts']);

        return Redirect::back()->with(['success' => 'Empresa actualizada.', 'item' => $company, 'itemType' => 'company']);
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return Redirect::back()->with('success', 'Empresa movida a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        $company->forceDelete();

        return Redirect::back()->with('success', 'Empresa eliminada permanentemente.');
    }

    public function restore($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        $company->restore();

        return Redirect::back()->with('success', 'Empresa restaurada.');
    }

    public function exportExcel()
    {
        $items = Company::all();

        return  ['itemsExcel' => $items];
    }
}
