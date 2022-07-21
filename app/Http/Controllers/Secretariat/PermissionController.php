<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')->orderBy('name')->get();

        return view('secretariat.permissions.list', ['users' => $users]);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return view('secretariat.permissions.show', ['user' => $user]);
    }

    public function edit(Request $request, User $user, Role $role)
    {
        $object_id = $request->get('object_id') ?? $request->get('workshop_id');
        $object = $object_id ? $role->getObject($object_id) : null;

        if($request->user()->cannot('updatePermission', [$user, $role, $object])){
            return redirect()->back()->with('error', __('role.unauthorized'));
        }

        if (!$role->isValid($object))
            $message = __('role.role_can_not_be_attached');
        else if ($user->addRole($role, $object))
            $message = __('general.successfully_added');
        else
            $message = __('role.role_unavailable');
        return redirect()->back()->with('message', $message);

    }

    public function remove(Request $request, User $user, Role $role)
    {
        $object_id = $request->get('object');
        $object = $object_id ? $role->getObject($object_id) : null;

        if($request->user()->cannot('updatePermission', [$user, $role, $object])){
            return redirect()->back()->with('error', __('role.unauthorized'));
        }

        $user->removeRole($role, $object ?? null);

        return redirect()->back();
    }
}
