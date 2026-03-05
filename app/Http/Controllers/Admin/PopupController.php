<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class PopupController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view popups', only: ['index']),
            new Middleware('permission:create popups', only: ['create', 'store']),
            new Middleware('permission:edit popups', only: ['edit', 'update', 'activate', 'deactivate']),
            new Middleware('permission:delete popups', only: ['destroy']),
        ];
    }

    // 🔹 List all popups
    public function index()
    {
        $popups = Popup::orderByDesc('id')->get();
        return view('admin.popups.index', compact('popups'));
    }

    // 🔹 Create form
    public function create()
    {
        return view('admin.popups.create');
    }

    // 🔹 Store popup
   

// 🔹 Store popup
public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    // 🔹 Handle image upload
    $imagePath = null;

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store(
            'popups',          // folder name
            'public'           // storage/app/public
        );
    }

    Popup::create([
        'title'          => $request->title,
        'slug'           => Str::slug($request->title),
        'description'    => $request->description,
        'type'           => $request->type,
        'content_type'   => $request->content_type,
        'content'        => $request->content,
        'image'          => $imagePath, // ✅ stored path
        'button_text'    => $request->button_text,
        'button_url'     => $request->button_url,
        'is_dismissible' => $request->has('is_dismissible'),
        'priority'       => $request->priority ?? 0,
        'status'         => 'inactive',
    ]);

    return redirect()
        ->route('admin.popups.index')
        ->with('success', 'Popup created successfully');
}


    // 🔹 Edit form
    public function edit(Popup $popup)
    {
        return view('admin.popups.edit', compact('popup'));
    }



    // 🔹 Update popup
public function update(Request $request, Popup $popup)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $data = $request->except('image');
    $data['slug'] = Str::slug($request->title);
    $data['is_dismissible'] = $request->has('is_dismissible');

    // Handle Image Update
    if ($request->hasFile('image')) {
        // Delete old image if it exists
        if ($popup->image) {
            Storage::disk('public')->delete($popup->image);
        }
        $data['image'] = $request->file('image')->store('popups', 'public');
    }

    $popup->update($data);

    return redirect()
        ->route('admin.popups.index')
        ->with('success', 'Popup updated successfully');
}

// 🔹 Delete popup
public function destroy(Popup $popup)
{
    // Delete the image file from storage
    if ($popup->image) {
        Storage::disk('public')->delete($popup->image);
    }

    $popup->delete();

    return back()->with('success', 'Popup deleted successfully');
}

    // 🔹 Activate popup (ONLY ONE ACTIVE)
    public function activate(Popup $popup)
    {
        Popup::where('status', 'active')->update(['status' => 'inactive']);

        $popup->update(['status' => 'active']);

        return back()->with('success', 'Popup activated');
    }

    // 🔹 Deactivate popup
    public function deactivate(Popup $popup)
    {
        $popup->update(['status' => 'inactive']);

        return back()->with('success', 'Popup deactivated');
    }


}
