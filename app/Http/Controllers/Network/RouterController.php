<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Internet\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RouterController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Router::class);

        $routers = Router::all()->sortBy('room');

        return view('network.routers.list', ['routers' => $routers]);
    }

    public function view(Router $router)
    {
        $this->authorize('view', $router);

        return view('network.routers.view', ['router' => $router]);
    }

    public function create()
    {
        $this->authorize('create', Router::class);

        return view('network.routers.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Router::class);

        Validator::make($request->all(), [
            'ip' => 'required|max:15|ip|unique:routers,ip',
            'room' => 'required|max:5',
            'mac_WAN' => ['nullable', 'regex:/^(([a-f0-9]{2}[-:]){5}([a-f0-9]{2}))$/i'],
            'mac_2G_LAN' => ['nullable', 'regex:/^(([a-f0-9]{2}[-:]){5}([a-f0-9]{2}))$/i'],
            'mac_5G' => ['nullable', 'regex:/^(([a-f0-9]{2}[-:]){5}([a-f0-9]{2}))$/i'],
            'comment' => 'max:255',
        ])->validate();

        Router::create($request->all());

        return redirect(route('routers'));
    }

    public function edit(Router $router)
    {
        $this->authorize('update', Router::class);

        return view('network.routers.edit', ['router' => $router]);
    }

    public function update(Request $request, Router $router)
    {
        $this->authorize('update', Router::class);

        Validator::make($request->all(), [
            'ip' => ['required', 'max:15', 'ip', \Illuminate\Validation\Rule::unique('routers')->ignore($router)],
            'room' => 'required|max:5',
            'mac_WAN' => ['nullable', 'regex:/^(([a-f0-9]{2}[-:]){5}([a-f0-9]{2}))$/i'],
            'mac_2G_LAN' => ['nullable', 'regex:/^(([a-f0-9]{2}[-:]){5}([a-f0-9]{2}))$/i'],
            'mac_5G' => ['nullable', 'regex:/^(([a-f0-9]{2}[-:]){5}([a-f0-9]{2}))$/i'],
            'comment' => 'max:255',
        ])->validate();

        $router->update($request->all());

        return redirect(route('routers.view', $router));
    }

    public function delete(Router $router)
    {
        $this->authorize('delete', Router::class);

        $router->delete();

        return redirect(route('routers'));
    }
}
