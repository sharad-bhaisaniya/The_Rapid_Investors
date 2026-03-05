<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicePlan;
use App\Models\ServicePlanDuration;
use App\Models\ServicePlanFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ServicePlansController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view service plans', only: ['index', 'show']),
            new Middleware('permission:create service plans', only: ['create', 'store']),
            new Middleware('permission:edit service plans', only: ['edit', 'update']),
            new Middleware('permission:delete service plans', only: ['destroy', 'multiDelete']),
        ];
    }

    // ========================================
    // LIST PAGE
    // ========================================
    public function index(Request $request)
{
    $query = ServicePlan::with('durations.features');

    // 🔍 SEARCH FILTER
    if ($request->search) {
        $query->where('name', 'like', "%{$request->search}%")
              ->orWhere('tagline', 'like', "%{$request->search}%");
    }

    // ⭐ FEATURED FILTER
    if ($request->featured !== null && $request->featured !== "") {
        $query->where('featured', $request->featured);
    }

    // 🔴 STATUS FILTER
    if ($request->status !== null && $request->status !== "") {
        $query->where('status', $request->status);
    }

    // 🔽 SORTING
    if ($request->sort) {
        $query->orderBy($request->sort, $request->direction ?? 'asc');
    } else {
        $query->orderBy('sort_order', 'asc');
    }

    // 📄 PAGINATION (10 per page)
    $plans = $query->paginate(5)->appends($request->all());

    return view('admin.services.plans_index', compact('plans'));
}

    // ========================================
    // CREATE FORM
    // ========================================
    public function create()
    {
        return view('admin.services.plans');
    }

    // ========================================
    // STORE LOGIC
    // ========================================
 

    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // 1. Save main plan
        $plan = ServicePlan::create([
            'name' => $request->name,
            'tagline' => $request->tagline,
            'featured' => $request->featured ? 1 : 0,
            'status' => $request->status ? 1 : 0,
            'sort_order' => $request->sort_order ?? 1,
            'button_text' => $request->button_text ?? 'Subscribe Now',
        ]);

        // 2. Save each duration
        if ($request->plans) {
            foreach ($request->plans as $key => $durationData) {

                // ✅ FIX: calculate duration_days
                // Example: "1.5 months" → 1.5 × 30 = 45
                $durationText = strtolower(trim($durationData['duration']));

                // Extract numeric value (supports 1, 1.5, 2.75 etc.)
                preg_match('/[\d.]+/', $durationText, $matches);
                $months = isset($matches[0]) ? (float) $matches[0] : 0;

                $durationDays = $months > 0 ? (int) round($months * 30) : null;

                $duration = ServicePlanDuration::create([
                    'service_plan_id' => $plan->id,
                    'duration' => $durationData['duration'],
                    'duration_days' => $durationDays,
                    'price' => $durationData['price'],
                ]);

                // 3. Save features per duration
                if (isset($durationData['features'])) {
                    foreach ($durationData['features'] as $feat) {
                        ServicePlanFeature::create([
                            'service_plan_duration_id' => $duration->id,
                            'svg_icon' => $feat['svg'] ?? null,
                            'text' => $feat['text'] ?? null,
                        ]);
                    }
                }
            }
        }

        DB::commit();
        return redirect()
            ->route('admin.service.plans_index')
            ->with('success', 'Plan Created Successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}



public function edit($id)
{
    $servicePlan = ServicePlan::with('durations.features')->findOrFail($id);

    $cleanDurations = $servicePlan->durations->map(function ($d) {
        return [
            'duration' => $d->duration,
            'price' => $d->price,
            'features' => $d->features->map(function ($f) {
                return [
                    'svg_icon' => $f->svg_icon,
                    'text' => $f->text
                ];
            })->toArray()
        ];
    })->toArray();

    return view('admin.services.plans_edit', compact('servicePlan', 'cleanDurations'));
}








public function update(Request $request, ServicePlan $servicePlan)
{
    // 1. UPDATE MAIN PLAN
    $servicePlan->update([
        'name'        => $request->name,
        'tagline'     => $request->tagline,
        'featured'    => $request->featured ? 1 : 0,
        'status'      => $request->status ? 1 : 0,
        'sort_order'  => $request->sort_order,
        'button_text' => $request->button_text,
    ]);

    // ONLY SELECTED PLANS
    $selectedPlans = collect($request->plans ?? [])
        ->filter(fn($p) => isset($p['selected']) && $p['selected']);

    // 2. EXISTING DURATIONS
    $existingDurations = $servicePlan->durations->pluck('duration')->toArray();
    $incomingDurations = $selectedPlans->pluck('duration')->toArray();

    // 3. DELETE UNSELECTED DURATIONS
    $toDelete = array_diff($existingDurations, $incomingDurations);

    if (!empty($toDelete)) {
        ServicePlanDuration::where('service_plan_id', $servicePlan->id)
            ->whereIn('duration', $toDelete)
            ->delete();
    }

    // 4. LOOP ONLY SELECTED DURATIONS
    foreach ($selectedPlans as $data) {
        $duration = ServicePlanDuration::firstOrCreate(
            [
                'service_plan_id' => $servicePlan->id,
                'duration'        => $data['duration'],
            ],
            [
                'price' => $data['price'],
            ]
        );

        // Update price
        $duration->update([
            'price' => $data['price'],
        ]);

        // 5. FEATURES LOGIC
        // Filter out null or empty strings from incoming features
        $incomingFeatures = collect($data['features'] ?? [])
            ->pluck('text')
            ->filter(fn($text) => !empty($text)) 
            ->toArray();

        // FIX: Delete features that are NOT in the incoming list.
        // We explicitly include OR conditions for NULL or empty strings to ensure they are cleaned up.
        ServicePlanFeature::where('service_plan_duration_id', $duration->id)
            ->where(function($query) use ($incomingFeatures) {
                $query->whereNotIn('text', $incomingFeatures)
                      ->orWhereNull('text')
                      ->orWhere('text', '');
            })->delete();

        // Insert / Update non-null features
        foreach ($data['features'] ?? [] as $feat) {
            // Skip processing if the text is null or empty
            if (empty($feat['text'])) {
                continue;
            }

            ServicePlanFeature::updateOrCreate(
                [
                    'service_plan_duration_id' => $duration->id,
                    'text' => $feat['text'],
                ],
                [
                    'svg_icon' => $feat['svg'] ?? '✔',
                ]
            );
        }
    }

    return redirect()
        ->route('admin.service-plans.index')
        ->with('success', 'Plan updated successfully (empty features and unchecked durations removed)');
}

public function destroy($id)
{
    $plan = ServicePlan::findOrFail($id);

    // delete relations
    $plan->durations()->each(function($duration){
        $duration->features()->delete();
    });
    $plan->durations()->delete();

    $plan->delete();

    return back()->with('success', 'Plan deleted successfully.');
}


public function multiDelete(Request $request)
{
    $ids = $request->ids ?? [];

    if (count($ids)) {
        $plans = ServicePlan::whereIn('id', $ids)->get();

        foreach ($plans as $plan) {
            $plan->durations()->each(function($duration){
                $duration->features()->delete();
            });
            $plan->durations()->delete();
            $plan->delete();
        }
    }

    return back()->with('success', 'Selected plans deleted successfully.');
}


}
