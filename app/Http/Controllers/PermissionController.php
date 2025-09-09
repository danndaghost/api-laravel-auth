<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Listar todos los permisos
     */
    public function index()
    {
        $permissions = Permission::with('roles')->get();

        return response()->json($permissions);
    }

    /**
     * Crear un nuevo permiso
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Permiso creado correctamente',
            'permission' => $permission
        ], 201);
    }

    /**
     * Ver un permiso específico
     */
    public function show(Permission $permission)
    {
        return response()->json([
            'permission' => $permission->load('roles')
        ]);
    }

    /**
     * Actualizar un permiso
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'description' => 'nullable|string|max:500',
        ]);

        $permission->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Permiso actualizado correctamente',
            'permission' => $permission
        ]);
    }

    /**
     * Eliminar un permiso
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'message' => 'Permiso eliminado correctamente'
        ]);
    }
}