<?php

namespace App\Http\Controllers;

use App\Models\CompanyContact;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;

class CompanyContactController extends Controller
{

    public function store()
    {

        $data = Request::validate([
            'companies_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'max:191'],
            'surname' => ['required', 'max:191'],
            'dni'       => ['required', 'unique:company_contacts,dni', 'regex:/^([0-9]{8}[A-Z])|[XYZ][0-9]{7}[A-Z]$/'],
            'email'     => ['required', 'email', 'max:191'],
            'phone'     => ['required', 'integer'],
        ]);

        $contact = CompanyContact::create([
            'companies_id' => $data['companies_id'],
            'name' => $data['name'],
            'surname' => $data['surname'],
            'dni' => $data['dni'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);


        $item = $contact->company->load(['contacts', 'documents']);

        return Redirect::back()->with(['success' => 'Contacto creado.', 'item' => $item, 'itemType' => 'company']);
    }

    public function update($id)
    {
        $contact = CompanyContact::findOrFail($id);
        $contact->update(
            Request::validate([
                'companies_id' => ['required', 'exists:companies,id'],
                'name' => ['required', 'max:191'],
                'surname' => ['required', 'max:191'],
                'dni'       => ['required', Rule::unique('company_contacts', 'dni')->ignore($contact->id), 'regex:/^([0-9]{8}[A-Z])|[XYZ][0-9]{7}[A-Z]$/'],
                'email'     => ['required', 'email', 'max:191'],
                'phone'     => ['required', 'integer'],
            ])
        );

        $item = $contact->company->load(['contacts', 'documents']);

        return Redirect::back()->with(['success' => 'Contacto editado.', 'item' => $item, 'itemType' => 'company']);
    }

    public function destroy($id)
    {
        $contact = CompanyContact::findOrFail($id);
        $company = $contact->company;
        $contact->delete();

        $item = $company->load(['contacts', 'documents']);

        return Redirect::back()->with(['success' => 'Contacto eliminado.', 'item' => $item, 'itemType' => 'company']);
    }
}
