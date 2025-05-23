<?php

namespace App\Http\Controllers;

use App\Models\InternshipDocument;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class InternshipDocumentController extends Controller
{

    public function store()
    {
        $data = Request::validate([
            'internships_id' => ['required', 'exists:internships,id'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:1024'],
            'description' => ['nullable', 'string'],
            'expiration_date' => ['nullable', 'date'],
        ]);

        if (Request::hasFile('file')) {
            $file = Request::file('file');
            $originalName = $file->getClientOriginalName();
            $fileContent = file_get_contents($file->getRealPath());

            $encryptedContent = Crypt::encrypt($fileContent);

            $uniqueFileName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

            $path = 'documents/' . $uniqueFileName;
            Storage::put($path, $encryptedContent);

            $document = InternshipDocument::create([
                'internships_id' => $data['internships_id'],
                'file' => $path,
                'original_name' => $originalName,
                'description' => $data['description'],
                'expiration_date' => $data['expiration_date'],
            ]);

            $item = $document->internship->load(['documents']);

            return Redirect::back()->with(['success' => 'Documento creado.', 'item' => $item, 'itemType' => 'internship']);
        } else {
            return Redirect::back()->withErrors(['file' => 'El documento es requerido.']);
        }
    }

    public function update($id)
    {
        $document = InternshipDocument::findOrFail($id);

        $document->update(
            Request::validate([
                'internships_id' => ['required', 'exists:internships,id'],
                'file' => ['sometimes', 'string'],
                'description' => ['nullable', 'string'],
                'expiration_date' => ['nullable', 'date'],
            ])
        );
        $item = $document->internship->load(['documents']);

        return Redirect::back()->with(['success' => 'Documento editado.', 'item' => $item, 'itemType' => 'internship']);
    }

    public function destroy($id)
    {
        $document = InternshipDocument::findOrFail($id);
        $filePath = $document->file;

        $internship = $document->internship;

        $document->delete();

        $item = $internship->load(['documents']);

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        return Redirect::back()->with(['success' => 'Documento eliminado.', 'item' => $item, 'itemType' => 'internship']);
    }

    public function download(InternshipDocument $document)
    {
        $filePath = $document->file;
        $originalName = $document->original_name;

        if (Storage::exists($filePath)) {

            $encryptedContent = Storage::get($filePath);

            $decryptedContent = Crypt::decrypt($encryptedContent);

            return response($decryptedContent)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="' . $originalName . '"');
        } else {
            abort(404, 'El archivo no existe.');
        }
    }
}
