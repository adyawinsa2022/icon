<?php

namespace App\Http\Controllers;

use App\Models\Container;
use Illuminate\Http\Request;

class ContainerController extends Controller
{
    public function index()
    {
        return view('container.index');
    }

    public function create()
    {
        return view('container.create');
    }

    public function search()
    {
        return view('container.search');
    }

    public function edit($name)
    {
        $container = Container::where('name', $name)->firstOrFail();

        // JSON array → "Mouse, Keyboard, Headset"
        $itemsString = collect($container->items ?? [])
            ->implode(', ');

        return view('container.edit', compact('container', 'itemsString'));
    }

    public function show($name)
    {
        $container = Container::where('name', $name)->firstOrFail();
        return view('container.show', compact('container'));
    }

    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'location' => 'nullable|string',
            'items' => 'nullable|string', // input koma: "Mouse, Keyboard"
        ]);

        // parse input koma → array
        $itemslist = collect(explode(',', $request->items ?? ''))
            ->map(fn($item) => trim($item))
            ->filter()
            ->values()
            ->toArray();

        $container = Container::updateOrCreate(
            ['name' => $request->name,], // key pencarian
            [
                'location' => $request->location,
                'items' => $itemslist,
            ]
        );

        return view('container.index', compact('container'));
    }
}
