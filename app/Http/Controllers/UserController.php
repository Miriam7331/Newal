<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Dashboard/User');
    }

    public function loadItems()
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = User::query();

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
            ],
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Illuminate\Support\Facades\Redirect
     */
    public function store()
    {
        $request = Request::all();

        $usuario = new User;

        $usuario->name = $request['name'];
        $usuario->password = $request['password'];
        $usuario->email = $request['email'];
        $usuario->admin = $request['admin'];
            
        $usuario->save();

        return Redirect::back()->with('success', 'Usuario creado correctamente');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\User  $usuario
     * @return Illuminate\Support\Facades\Redirect
     */
    public function update($usuarioId)
    {
        $request = Request::all();
        $usuario = User::find($usuarioId);

        $usuario->name = $request['name'];
        $usuario->password = $request['password'] ?? $usuario->password;
        $usuario->email = $request['email'];
        $usuario->admin = $request['admin'];
            
        if($usuario->save()) {
            return Redirect::back()->with(['success' => 'Usuario actualizado correctamente', 'item' => $usuario, 'itemType' => 'user']);
        } else {
            return Redirect::back()->with('error', 'No se ha podido actualizar el usuario');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $usuario
     * @return Illuminate\Support\Facades\Redirect
     */
    public function destroy($usuarioId)
    {
        $usuario = User::find($usuarioId);

        if($usuario->delete()) {
            return Redirect::back()->with('success', 'Usuario eliminado correctamente');
        } else {
            return Redirect::back()->with('error', 'No se ha podido eliminar el usuario');
        }
    }

    public function destroyPermanent($usuarioId)
    {
        try {
            $usuario = User::onlyTrashed()->findOrFail($usuarioId);
            $usuario->forceDelete();

            return Redirect::back()->with('success', 'Usuario eliminado de forma permanente.');
        } catch (\Throwable $th) {
            return Redirect::back()->with('error', 'Error al eliminar usuario.');
        }
    }

    public function restore($usuarioId)
    {
        $usuario = User::onlyTrashed()->findOrFail($usuarioId);
        $usuario->restore();

        return Redirect::back()->with('success', 'Usuario restaurado.');
    }

    public function exportExcel()
    {
        $items = User::all();

        return  [ 'itemsExcel' => $items ];
    }
}