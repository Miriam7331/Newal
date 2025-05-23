<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class DocumentController extends Controller
{

    public function store()
    {
        $data = Request::validate([
            'students_id' => ['required', 'exists:students,id'],
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
            $document = Document::create([
                'students_id' => $data['students_id'],
                'file' => $path,  // Guardar el path del archivo
                'original_name' => $originalName,  // Guardar el nombre original en la base de datos
                'description' => $data['description'],
                'expiration_date' => $data['expiration_date'],
            ]);

            $item = $document->student->load(['formativeActions', 'documents', 'notes.user']);

            return Redirect::back()->with(['success' => 'Documento creado.', 'item' => $item, 'itemType' => 'student']);
        } else {
            return Redirect::back()->withErrors(['file' => 'El documento es requerido.']);
        }
    }

    public function update(Document $document)
    {
        $document->update(
            Request::validate([
                'students_id' => ['required', 'exists:students,id'],
                'file' => ['sometimes', 'string'],
                'description' => ['nullable', 'string'],
                'expiration_date' => ['nullable', 'date'],
            ])
        );

        $item = $document->student->load(['formativeActions', 'documents', 'notes.user']);

        return Redirect::back()->with(['success' => 'Documento editado.', 'item' => $item, 'itemType' => 'student']);
    }

    public function destroy(Document $document)
    {
        $filePath = $document->file;


        $student = $document->student;

        $document->delete();

        $item = $student->load(['formativeActions', 'documents', 'notes.user']);


        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        return Redirect::back()->with(['success' => 'Documento eliminado.', 'item' => $item, 'itemType' => 'student']);
    }

    public function download(Document $document)
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
