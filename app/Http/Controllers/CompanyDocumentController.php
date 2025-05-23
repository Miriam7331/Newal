<?php

namespace App\Http\Controllers;

use App\Models\CompanyDocument;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class CompanyDocumentController extends Controller
{

    public function store()
    {
        $data = Request::validate([
            'companies_id' => ['required', 'exists:companies,id'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:1024'],
            'description' => ['nullable', 'string'],
            'expiration_date' => ['nullable', 'date'],
        ]);

        if (Request::hasFile('file')) {
            $file = Request::file('file');
            $originalName = $file->getClientOriginalName();
            $fileContent = file_get_contents($file->getRealPath());

            // Encriptar el contenido del archivo
            $encryptedContent = Crypt::encrypt($fileContent);

            // Generar un nombre único para el archivo
            $uniqueFileName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

            // Guardar el archivo encriptado con un nombre único
            $path = 'documents/' . $uniqueFileName;
            Storage::put($path, $encryptedContent);

            // Crear el documento en la base de datos
            $document = CompanyDocument::create([
                'companies_id' => $data['companies_id'],
                'file' => $path,  // Guardar el path del archivo
                'original_name' => $originalName,  // Guardar el nombre original en la base de datos
                'description' => $data['description'],
                'expiration_date' => $data['expiration_date'],
            ]);

            $item = $document->company->load(['documents', 'contacts']);

            return Redirect::back()->with(['success' => 'Documento creado.', 'item' => $item, 'itemType' => 'company']);
        } else {
            return Redirect::back()->withErrors(['file' => 'El documento es requerido.']);
        }
    }

    public function update($id)
    {
        $document = CompanyDocument::findOrFail($id);

        $document->update(
            Request::validate([
                'companies_id' => ['required', 'exists:companies,id'],
                'file' => ['sometimes', 'string'],
                'description' => ['nullable', 'string'],
                'expiration_date' => ['nullable', 'date'],
            ])
        );
        $item = $document->company->load(['documents', 'contacts']);

        return Redirect::back()->with(['success' => 'Documento editado.', 'item' => $item, 'itemType' => 'company']);
    }

    public function destroy($id)
    {
        $document = CompanyDocument::findOrFail($id);
        $filePath = $document->file;


        $company = $document->company;

        $document->delete();

        $item = $company->load(['documents', 'contacts']);


        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        return Redirect::back()->with(['success' => 'Documento eliminado.', 'item' => $item, 'itemType' => 'company']);
    }

    public function download(CompanyDocument $document)
    {
        $filePath = $document->file;
        $originalName = $document->original_name;

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
}
