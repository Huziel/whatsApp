<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->rol == 'ADMIN') {
            $users = User::orderBy('name', 'asc')->get();
        } else {
            $users = User::where('empresa_id', $user->empresa_id)
                ->orderBy('name', 'asc')
                ->get();
        }

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'empresa_id' => 'required|integer',
            'name' => 'required|string|max:100|min:2',
            'rol' => 'required|string|max:20',
            'password' => 'required|string|max:255|min:8',
            'email' => 'required|email|unique:users,email',
        ], [
            'empresa_id.required' => 'El campo empresa es obligatorio.',
            'empresa_id.integer' => 'El campo empresa no tiene el formato correcto.',
            'name.required' => 'El campo nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede tener más de :max caracteres.',
            'email.required' => 'El campo correo es obligatorio.',
            'email.unique' => 'El correo ya está registrado.',
            'email.email' => 'El correo no tiene el formato correcto.',
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.min' => 'El campo contraseña es de mínimo :min caracteres.',
            'rol.required' => 'El campo rol es obligatorio.',
            'rol.max' => 'El rol no puede tener más de :max caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $validatedData = [
            'name' => strip_tags($request->input('name')),
            'email' => strip_tags($request->input('email')),
            'rol' => strip_tags($request->input('rol')),
            'password' => Hash::make($request->input('password')),
            'empresa_id' => htmlspecialchars($request->input('empresa_id')),
            'email_verified_at' => empty(htmlspecialchars($request->input('email_verified_at')))
                ? null
                : htmlspecialchars($request->input('email_verified_at')),
        ];

        $new = User::create($validatedData);

        if (!$new) {
            return response()->json([
                'error' => 'No se logró crear'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($new, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $model = User::find($id);

        if (!$model) {
            return response()->json([
                'error' => 'No encontrado'
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json($model);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $model = User::find($id);

        if (!$model) {
            return response()->json([
                'error' => 'No encontrado'
            ], Response::HTTP_BAD_REQUEST);
        }

        $validatedData = [
            'name' => strip_tags($request->input('name')),
            'email' => strip_tags($request->input('email')),
            'rol' => strip_tags($request->input('rol')),
            'empresa_id' => empty(htmlspecialchars($request->input('empresa_id')))
                ? null
                : htmlspecialchars($request->input('empresa_id')),
            'email_verified_at' => empty(htmlspecialchars($request->input('email_verified_at')))
                ? null
                : htmlspecialchars($request->input('email_verified_at')),
        ];

        $model->update($validatedData);

        return response()->json($model);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();

        if ($user->rol == 'ADMIN') {
            $model = User::find($id);

            if (!$model) {
                return response()->json([
                    'error' => 'No encontrado'
                ], Response::HTTP_BAD_REQUEST);
            } else {
                if ($user->id != $model->id) {
                    $model->delete();
                } else {
                    return response()->json([
                        'error' => 'No te puedes eliminar a ti mismo'
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            return response()->json([
                'message' => 'Eliminado'
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'error' => 'No tiene permisos para esta operación'
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
