<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutMissionValue;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AboutMissionValueController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view about mission values', only: ['index']),
            new Middleware('permission:edit about mission values', only: ['store', 'update']),
        ];
    }

    public function index()
    {
        return view('admin.about.mission', [
            'mission' => AboutMissionValue::first()
        ]);
    }

    public function store(Request $request)
    {
        AboutMissionValue::updateOrCreate(
            ['id' => $request->id],
            $request->only([
                'badge',
                'title',
                'mission_text',
                'short_description',
                'is_active'
            ])
        );

        return back()->with('success', 'Mission & Values updated');
    }

    public function update(Request $request, $id)
    {
        AboutMissionValue::findOrFail($id)->update($request->all());

        return back()->with('success', 'Mission & Values updated');
    }
}
