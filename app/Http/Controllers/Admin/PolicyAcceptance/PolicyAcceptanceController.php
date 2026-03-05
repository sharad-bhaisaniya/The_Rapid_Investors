<?php

namespace App\Http\Controllers\Admin\PolicyAcceptance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PolicyAcceptance;

class PolicyAcceptanceController extends Controller
{
    /**
     * Display listing
     */
    public function index()
    {
        $policies = PolicyAcceptance::latest()->paginate(10);

        return view('admin.policyacceptance.index', compact('policies'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.policyacceptance.create');
    }

    /**
     * Store new policy
     */
          public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
        ]);
    
        // Convert checkbox to boolean (important)
        $status = $request->has('status') ? 1 : 0;
    
        // Check if policy already exists
        $policy = PolicyAcceptance::first();
    
        if ($policy) {
    
            // Update existing record
            $policy->update([
                'title' => $request->title,
                'description' => $request->description,
                'content' => $request->content,
                'status' => $status,
            ]);
    
            return redirect()->route('admin.policyacceptance.index')
                ->with('success', 'Policy updated successfully.');
        } else {
    
            // Create new record
            PolicyAcceptance::create([
                'title' => $request->title,
                'description' => $request->description,
                'content' => $request->content,
                'status' => $status,
            ]);
    
            return redirect()->route('admin.policyacceptance.index')
                ->with('success', 'Policy created successfully.');
        }
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $policy = PolicyAcceptance::findOrFail($id);

        return view('admin.policyacceptance.edit', compact('policy'));
    }

    /**
     * Update policy
     */
    public function update(Request $request, $id)
    {
        $policy = PolicyAcceptance::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
        ]);

        $policy->update([
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
        ]);

        return redirect()->route('admin.policyacceptance.index')
                         ->with('success', 'Policy updated successfully.');
    }

    /**
     * Delete policy
     */
    public function destroy($id)
    {
        $policy = PolicyAcceptance::findOrFail($id);
        $policy->delete();

        return redirect()->route('admin.policyacceptance.index')
                         ->with('success', 'Policy clear successfully.');
    }

    /**
     * Update Status (Active / Inactive)
     */
    public function updateStatus($id)
    {
        $policy = PolicyAcceptance::findOrFail($id);

        $policy->status = !$policy->status;
        $policy->save();

        return redirect()->back()
                         ->with('success', 'Policy status updated successfully.');
    }
}