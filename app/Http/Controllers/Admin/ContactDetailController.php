<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactDetail;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ContactDetailController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view contact details', only: ['index']),
            new Middleware('permission:edit contact details', only: ['store']),
        ];
    }
    public function index()
    {
        $contact = ContactDetail::first();

        return view('admin.contact-details.index', compact('contact'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email'   => 'nullable|email',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        // ✅ if already exists → update
        $contact = ContactDetail::first();

        if ($contact) {
            $contact->update($data);
        } else {
            ContactDetail::create($data);
        }

        return back()->with('success', 'Contact details saved');
    }
}
