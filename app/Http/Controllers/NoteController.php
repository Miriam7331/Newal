<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

class NoteController extends Controller
{
    public function store()
    {
        
        $note = Note::create(
            Request::validate([
                'note' => ['required'],
                'students_id' => ['required', 'exists:students,id'],
                'users_id' => ['required', 'exists:users,id'],
            ])
        );

        $item = $note->student->load(['formativeActions', 'documents', 'notes.user']);

        return Redirect::back()->with(['success' => 'Nota creada.', 'item' => $item, 'itemType' => 'student']);
    }

    public function update(Note $note)
    {
        $note->update(
            Request::validate([
                'note' => ['required'],
                'students_id' => ['required', 'exists:students,id'],
                'users_id' => ['required', 'exists:users,id'],
            ])

        );

        $item = $note->student->load(['formativeActions', 'documents', 'notes.user']);

        return Redirect::back()->with(['success' => 'Nota editada.', 'item' => $item, 'itemType' => 'student']);
    }

    public function destroy(Note $note)
    {
        $note->delete();

        $item = $note->student->load(['formativeActions', 'documents', 'notes.user']);

        return Redirect::back()->with(['success' => 'Nota eliminada.', 'item' => $item, 'itemType' => 'student']);
    }
}