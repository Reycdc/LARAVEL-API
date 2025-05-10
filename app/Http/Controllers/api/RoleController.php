<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => env('AUTH_GUARD', 'web'),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Role created successfully',
            'data' => $role,
        ]);
    }

    public function store_permission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => env('AUTH_GUARD', 'web'),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Permission created successfully',
            'data' => $permission,
        ]);
    }
}
