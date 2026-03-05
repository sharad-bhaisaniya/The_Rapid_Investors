<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use App\Models\TipCategory;
use App\Models\TipPlanAccess;
use App\Models\ServicePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\RiskRewardMaster;
use App\Models\UserSubscription;
use App\Models\MasterNotification;
use App\Events\MasterNotificationBroadcast;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Routing\Controllers\HasMiddleware; 
use Illuminate\Routing\Controllers\Middleware;


class TipController extends Controller implements Hasmiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view tips', only: ['index', 'EquityTips', 'FutureAndOption', 'accuracyDashboard']),
            new Middleware('permission:create tips', only: ['create', 'store', 'storeEquityTip', 'storeDerivativeTip', 'storeCategory']),
            new Middleware('permission:edit tips', only: ['edit', 'update', 'updateLiveStatus', 'storeFollowUp', 'updateMedia']),
            new Middleware('permission:delete tips', only: []), // No delete methods currently
        ];
    }


public function index(Request $request)
{
    $query = Tip::with(['category', 'planAccess.plan'])
        ->where('status', '!=', 'archived');

    if ($request->filled('search')) {
        $query->where('stock_name', 'like', '%' . $request->search . '%');
    }
    
    if ($request->filled('trade_status')) {
        $query->where('trade_status', $request->trade_status);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('date')) {
        $query->whereDate('created_at', $request->date);
    }
    if ($request->filled('month')) {
        $query->whereMonth('created_at', $request->month);
    }
    if ($request->filled('year')) {
        $query->whereYear('created_at', $request->year);
    }

    // $tips = $query->orderByRaw("FIELD(trade_status, 'Open', 'Closed')")
    //               ->latest()
    //               ->paginate(10)
    //               ->withQueryString();

    $tips = $query->latest()
               ->paginate(10)
              ->withQueryString();

               $tips->getCollection()->transform(function ($tip) {
        $tip->media_files = $tip->getMedia('tip_charts')->map(fn ($m) => [
            'id'        => $m->id,
            'file_name'=> $m->file_name,
            'mime_type'=> $m->mime_type,
            'url'       => $m->getFullUrl(),
        ]);
        return $tip;
    });


    return view('admin.tips.index', compact('tips'));
}

    public function EquityTips()
    {
        $categories = TipCategory::where('status', 1)->get();
        $plans = ServicePlan::where('status', 1)->get();
        $riskMaster = RiskRewardMaster::where('is_active', 1)->first();

        $tips = Tip::with(['category', 'planAccess.plan'])
            ->where('tip_type', 'equity')
            ->where('status', '!=', 'archived')
            ->orderByRaw("FIELD(trade_status, 'Open', 'Closed')")
            ->latest()
            ->paginate(20);

        return view('admin.tips.tips', compact('tips', 'categories', 'plans', 'riskMaster'));
    }

    public function FutureAndOption()
    {
        $categories = TipCategory::where('status', 1)->get();
        $plans = ServicePlan::where('status', 1)->get();
        $riskMaster = RiskRewardMaster::where('is_active', 1)->first();

        $tips = Tip::with(['category', 'planAccess.plan'])
            ->whereIn('tip_type', ['future', 'option'])
            ->where('status', '!=', 'archived')
            ->orderByRaw("FIELD(trade_status, 'Open', 'Closed')")
            ->latest()
            ->paginate(20);

        return view('admin.tips.future_Option', compact('tips', 'categories', 'plans', 'riskMaster'));
    }

    // --- STORE METHODS ---

    public function storeEquityTip(Request $request)
    {
        return $this->handleStore($request, 'equity');
    }

    public function storeDerivativeTip(Request $request)
    {
        return $this->handleStore($request, $request->tip_type);
    }

 private function handleStore(Request $request, $type)
{
    // Validation rules - expiry_date handled as string (we parse it)
    $rules = [
        'stock_name'     => 'required|string|max:255',
        'symbol_token'   => 'nullable|string|max:100',
        'category_id'    => 'required|exists:tip_categories,id',
        'exchange'       => 'required|in:NSE,BSE,MCX',
        'call_type'      => 'required|in:Buy,Sell',
        'entry_price'    => 'required|numeric',
        'target_price'   => 'required|numeric',
        'stop_loss'      => 'required|numeric',
        'plans'          => 'required|array|min:1',
        'attachment'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ];

    if ($type === 'option') {
        $rules['option_type']  = 'required|in:CE,PE';
        $rules['strike_price'] = 'required|numeric';
        // accept textual expiry; we'll parse manually
        $rules['expiry_date']  = 'required|string';
        // frontend optional helper: expiry_date_formatted (YYYY-MM-DD)
    }

    $validator = Validator::make($request->all(), $rules);
    // if ($validator->fails()) {
    //     Log::warning('Tip validation failed', ['errors' => $validator->errors()->toArray()]);
    //     return redirect()->back()->withErrors($validator)->withInput();
    // }
    if ($validator->fails()) {
    Log::warning('Tip validation failed', ['errors' => $validator->errors()->toArray()]);
    
    if ($request->ajax()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }
    // Replace your success redirect with this:
// if ($request->ajax()) {
//     return response()->json([
//         'status' => 'success',
//         'message' => ucfirst($type) . ' Tip published successfully!'
//     ], 200);
// }

// return redirect()->back()->with('success', ucfirst($type) . ' Tip published successfully!');
    
    return redirect()->back()->withErrors($validator)->withInput();
}

    // Parse expiry (if option)
    $expiryDate = null;
    if ($type === 'option') {
        // Prefer formatted ISO sent by frontend if present
        $candidate = $request->input('expiry_date_formatted') ?: $request->input('expiry_date');

        if (!$candidate) {
            return redirect()->back()->withErrors(['expiry_date' => 'Expiry date is required for options'])->withInput();
        }

        // Normalize candidate and try parsing
        $candidate = trim((string) $candidate);

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $candidate)) {
                $expiryDate = Carbon::createFromFormat('Y-m-d', $candidate)->format('Y-m-d');
            } else {
                // Try patterns like 10FEB2026, 1JAN2026, 10 Feb 2026 etc.
                // First try dMY (10FEB2026)
                try {
                    $expiryDate = Carbon::createFromFormat('dMY', strtoupper($candidate))->format('Y-m-d');
                } catch (\Exception $ex1) {
                    // try with dF Y (e.g. 10 February 2026) or other common forms
                    try {
                        $expiryDate = Carbon::parse($candidate)->format('Y-m-d');
                    } catch (\Exception $ex2) {
                        // parsing failed
                        return redirect()->back()->withErrors(['expiry_date' => 'Invalid expiry date format. Use 10FEB2026 or YYYY-MM-DD'])->withInput();
                    }
                }
            }
        } 
        catch (\Exception $e) {
            return redirect()->back()->withErrors(['expiry_date' => 'Invalid expiry date format'])->withInput();
        }
    }

    $notificationsToBroadcast = [];

    try {
        DB::beginTransaction();

        $tip = Tip::create([
            'tip_type'       => $type,
            'stock_name'     => strtoupper($request->stock_name),
            'symbol_token'   => $request->symbol_token,
            'exchange'       => $request->exchange,
            'call_type'      => $request->call_type,
            'category_id'    => $request->category_id,
            'entry_price'    => $request->entry_price,
            'target_price'   => $request->target_price,
            'target_price_2' => $request->target_price_2,
            'stop_loss'      => $request->stop_loss,
            'cmp_price'      => $request->cmp_price ?? $request->entry_price,
            'expiry_date'    => $expiryDate,
            'strike_price'   => $request->strike_price ?? null,
            'option_type'    => $request->option_type ?? null,
            // use lowercase status to avoid enum mismatches
            'status'         => 'active',
            'trade_status'   => 'Open',
            'admin_note'     => $request->admin_note ?? "New $type Tip Generated",
            'created_by'     => Auth::id(),
        ]);

        Log::info('Created Tip', ['tip_id' => $tip->id]);

        // Media: protect file upload so it doesn't kill the transaction
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            try {
                $tip->addMediaFromRequest('attachment')->toMediaCollection('tip_charts');
                Log::info('Media added for tip', ['tip_id' => $tip->id]);
            } catch (\Exception $mediaEx) {
                Log::error('Media upload failed (non-fatal)', ['error' => $mediaEx->getMessage(), 'tip_id' => $tip->id]);
            }
        }

        // Attach plans
        if ($request->has('plans')) {
            foreach ($request->plans as $planId) {
                TipPlanAccess::create([
                    'tip_id' => $tip->id,
                    'service_plan_id' => $planId,
                ]);
            }
        }

        // Build notifications (DB records) inside transaction
        $allowedPlans = $request->plans;
        $subscribedUsers = UserSubscription::where('status', 'active')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->where(function ($q) use ($allowedPlans) {
                $q->where(function ($paid) use ($allowedPlans) {
                    $paid->where('payment_status', 'paid')->whereIn('service_plan_id', $allowedPlans);
                })->orWhere(function ($demo) {
                    $demo->where('payment_status', 'demo')->whereNull('service_plan_id');
                });
            })
            ->pluck('user_id')
            ->unique();

        foreach ($subscribedUsers as $userId) {
            $notification = MasterNotification::create([
                'user_id' => $userId,
                'type'    => 'tip',
                'title'   => '📈 New Premium Tip Live!',
                'message' => $tip->stock_name . ' new trading tip is available now.',
                'data'    => [
                    'tip_id' => $tip->id,
                    'plans'  => $allowedPlans,
                    'from_id'=> Auth::id()
                ]
            ]);
            $notificationsToBroadcast[] = $notification;
        }

        DB::commit();

        // Broadcast AFTER commit — failures here won't rollback DB
        foreach ($notificationsToBroadcast as $n) {
            try {
                event(new MasterNotificationBroadcast($n));
            } catch (\Exception $e) {
                Log::error('Broadcast failed after commit', ['error' => $e->getMessage(), 'notification_id' => $n->id]);
            }
        }

        // --- FIXED SUCCESS RETURN ---
        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => ucfirst($type) . ' Tip published successfully!'
            ], 200);
        }

        return redirect()->back()->with('success', ucfirst($type) . ' Tip published successfully!');

    
   } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Tip create error', ['msg' => $e->getMessage()]);

        // --- FIXED ERROR RETURN ---
        if ($request->ajax()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
    }
}



    public function create()
    {
        $plans = ServicePlan::where('status', 1)->orderBy('sort_order')->get();
        $categories = TipCategory::where('status', 1)->orderBy('name')->get();
        return view('admin.tips.create', compact('plans', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'stock_name'   => 'required|string|max:255',
            'symbol_token' => 'nullable|string|max:100',
            'exchange'     => 'required|in:NSE,BSE',
            'call_type'    => 'required|in:BUY,SELL',
            'category_id'  => 'required|exists:tip_categories,id',
            'entry_price'  => 'required|numeric',
            'target_price' => 'required|numeric',
            'stop_loss'    => 'required|numeric',
            'status'       => 'required|string',
            'plan_access'  => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $tip = Tip::create([
                'stock_name'   => strtoupper($request->stock_name),
                'symbol_token' => $request->symbol_token,
                'exchange'     => $request->exchange,
                'call_type'    => $request->call_type,
                'category_id'  => $request->category_id,
                'entry_price'  => $request->entry_price,
                'target_price' => $request->target_price,
                'stop_loss'    => $request->stop_loss,
                'status'       => $request->status,
                'trade_status' => 'Open',
                'admin_note'   => $request->admin_note,
                'created_by'   => auth()->id(),
            ]);

            foreach ($request->plan_access as $access) {
                [$planId, $durationId] = explode('_', $access);
                TipPlanAccess::create([
                    'tip_id'                 => $tip->id,
                    'service_plan_id'        => $planId,
                    'service_plan_duration_id' => $durationId,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.tips.index')->with('success', 'Market Tip Created Successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }


    public function updateLiveStatus(Request $request, $id)
    {
        $request->validate([
            'status'    => 'required|in:T1-Achieved,T2-Achieved,SL-Hit',
            'cmp_price' => 'required|numeric'
        ]);

        $tip = Tip::findOrFail($id);


        if ($tip->trade_status === 'Closed') {
            return response()->json([
                'success'      => true,
                'message'      => 'Trade is closed. No updates allowed.',
                'new_status'   => $tip->status,
                'trade_status' => 'Closed'
            ]);
        }

        $newStatus = $request->status;
        $tradeStatus = 'Open'; // Default assumption

        if ($newStatus === 'SL-Hit' || $newStatus === 'T2-Achieved') {
            $tradeStatus = 'Closed';
        } 
        elseif ($newStatus === 'T1-Achieved') {
            if (empty($tip->target_price_2) || $tip->target_price_2 == 0) {
                $tradeStatus = 'Closed';
            } else {
                $tradeStatus = 'Open';
            }
        }

        $tip->update([
            'status'       => $newStatus,
            'trade_status' => $tradeStatus,
            'cmp_price'    => $request->cmp_price,
            'admin_note'   => $tip->admin_note . "\n[System]: Status changed to $newStatus at price " . $request->cmp_price
        ]);

        return response()->json([
            'success'      => true,
            'new_status'   => $newStatus,
            'trade_status' => $tradeStatus
        ]);
    }

    // --- HELPERS ---

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:tip_categories,name']);
        TipCategory::create(['name' => $request->name, 'status' => 1]);
        return redirect()->back()->with('success', 'Category created successfully');
    }

    public function edit(Tip $tip)
    {
        if ($tip->status == 'archived') abort(403);
        $categories = TipCategory::all();
        $plans = ServicePlan::all();
        return view('admin.tips.edit', compact('tip', 'categories', 'plans'));
    }

    public function update(Request $request, $id)
    {
        $oldTip = Tip::findOrFail($id);
        
        $validatedData = $request->validate([
            'stock_name' => 'required',
            'plans' => 'required|array'
        ]);

        DB::transaction(function () use ($oldTip, $request) {
            $oldTip->update(['status' => 'archived']);
            $planIds = $request->plans;

            $newTip = $oldTip->replicate();
            $newTip->fill($request->except(['plans', '_token', '_method']));
            $newTip->parent_id = $oldTip->parent_id ?? $oldTip->id;
            $newTip->version = $oldTip->version + 1;
            $newTip->status = 'Active';
            $newTip->trade_status = 'Open'; // Reset to Open for new version
            $newTip->created_by = Auth::id();
            $newTip->save();

            foreach ($planIds as $planId) {
                TipPlanAccess::create(['tip_id' => $newTip->id, 'service_plan_id' => $planId]);
            }
        });

        return redirect()->route('admin.tips.index')->with('success', 'Tip updated successfully.');
    }

    public function storeFollowUp(Request $request, $id)
    {
        $request->validate([
            'target_price' => 'required|numeric',
            'target_price_2' => 'nullable|numeric',
            'stop_loss' => 'required|numeric',
            'message' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();
            
            $tip = Tip::findOrFail($id);

            $newEntry = [
                'date' => now()->toDateTimeString(),
                'message' => $request->message,
                'old_values' => [
                    'target_price' => $tip->target_price,
                    'target_price_2' => $tip->target_price_2,
                    'stop_loss' => $tip->stop_loss,
                ],
                'new_values' => [
                    'target_price' => $request->target_price,
                    'target_price_2' => $request->target_price_2,
                    'stop_loss' => $request->stop_loss,
                ]
            ];

            $currentFollowups = $tip->followups ?? [];
            array_unshift($currentFollowups, $newEntry); 

            $tip->update([
                'target_price' => $request->target_price,
                'target_price_2' => $request->target_price_2,
                'stop_loss' => $request->stop_loss,
                'followups' => $currentFollowups
            ]);


            /* ============================================
           🔔 SEND FOLLOW-UP NOTIFICATION
           ============================================ */

        // 1. Get allowed plans for this specific tip
        $allowedPlans = TipPlanAccess::where('tip_id', $tip->id)->pluck('service_plan_id')->toArray();

        // 2. Fetch eligible users (Active + Subscription Valid + Paid/Demo Logic)
        $subscribedUsers = UserSubscription::where('status', 'active')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->where(function ($q) use ($allowedPlans) {
                $q->where(function ($paid) use ($allowedPlans) {
                    $paid->where('payment_status', 'paid')
                        ->whereIn('service_plan_id', $allowedPlans);
                })
                ->orWhere(function ($demo) {
                    $demo->where('payment_status', 'demo')
                        ->whereNull('service_plan_id');
                });
            })
            ->pluck('user_id')
            ->unique();

        // 3. Prepare Notification Content (Small & Clear)
        // $old_p = "T1:{$newEntry['old_values']['target_price']}, SL:{$newEntry['old_values']['stop_loss']}";
        // $new_p = "T1:{$request->target_price}, SL:{$request->stop_loss}";
        
        // $notifMessage = "Update for {$tip->stock_name}: {$request->message}. Prices changed from ($old_p) to ($new_p)";

        // foreach ($subscribedUsers as $userId) {
        //     $notification = MasterNotification::create([
        //         'user_id' => $userId,
        //         'type'    => 'tip_followup',
        //         'title'   => "📢 Follow-up: {$tip->stock_name}",
        //         'message' => $notifMessage,
        //         'data'    => [
        //             'tip_id' => $tip->id,
        //             'type'   => 'followup_update',
        //             'from_id'=> Auth::id()
        //         ]
        //     ]);

        //     // Real-time broadcast
        //     event(new MasterNotificationBroadcast($notification));
        // }
        // 3. Prepare Notification Content (Structured for UI)
// Hum message mein JSON bhejenge taaki frontend isse parse kar sake
$notifData = [
    'stock' => $tip->stock_name,
    'update_title' => $request->message, // E.g., "Updated Target 2"
    't1' => ['old' => $newEntry['old_values']['target_price'], 'new' => $request->target_price],
    't2' => ['old' => $newEntry['old_values']['target_price_2'], 'new' => $request->target_price_2],
    'sl' => ['old' => $newEntry['old_values']['stop_loss'], 'new' => $request->stop_loss],
];

foreach ($subscribedUsers as $userId) {
    $notification = MasterNotification::create([
        'user_id' => $userId,
        'type'    => 'tip_followup', // Is type ko frontend pe check karenge
        'title'   => "📢 Follow-up: {$tip->stock_name}",
        'message' => json_encode($notifData), // JSON string bana kar bhej rahe hain
        'data'    => [
            'tip_id' => $tip->id,
            'type'   => 'followup_update',
            'from_id'=> Auth::id()
        ]
    ]);

    event(new MasterNotificationBroadcast($notification));
}

            DB::commit();
            
            return redirect()->back()->with('success', 'Follow-up added and prices updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }


public function accuracyDashboard(Request $request)
{
    $query = Tip::with('category')->where('status', '!=', 'archived');

    // --- Apply Filters ---
    if ($request->filled('tip_type')) $query->where('tip_type', $request->tip_type);
    if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
    if ($request->filled('from_date')) $query->whereDate('created_at', '>=', $request->from_date);
    if ($request->filled('to_date')) $query->whereDate('created_at', '<=', $request->to_date);

    // --- Current Stats ---
    $totalTrades = (clone $query)->count();
    $closedTrades = (clone $query)->where('trade_status', 'Closed')->count();
    $t1Hits = (clone $query)->where('status', 'T1-Achieved')->count();
    $t2Hits = (clone $query)->where('status', 'T2-Achieved')->count();
    $slHits = (clone $query)->where('status', 'SL-Hit')->count();
    
    $wins = $t1Hits + $t2Hits;
    $accuracy = $closedTrades > 0 ? round(($wins / ($wins + $slHits)) * 100, 2) : 0;

    // --- Growth Rate Logic (Comparing to Last Month) ---
    $lastMonthWins = Tip::where('trade_status', 'Closed')
        ->whereIn('status', ['T1-Achieved', 'T2-Achieved'])
        ->whereMonth('created_at', now()->subMonth()->month)
        ->count();
    $lastMonthLosses = Tip::where('status', 'SL-Hit')
        ->whereMonth('created_at', now()->subMonth()->month)
        ->count();
    
    $lastMonthAccuracy = ($lastMonthWins + $lastMonthLosses) > 0 
        ? round(($lastMonthWins / ($lastMonthWins + $lastMonthLosses)) * 100, 2) : 0;
    
    $growthRate = $accuracy - $lastMonthAccuracy;

    // --- Paginated List of Tips ---
    $tipsList = (clone $query)->latest()->paginate(15)->withQueryString();
    
    $categories = TipCategory::all();

    return view('admin.tips.accuracy', compact(
        'accuracy', 'totalTrades', 'closedTrades', 't1Hits', 't2Hits', 
        'slHits', 'categories', 'tipsList', 'growthRate', 'lastMonthAccuracy'
    ));
}
//   public function updateMedia(Request $request, Media $media)
// {
//     $request->validate([
//         'file' => 'required|mimes:jpg,jpeg,png,webp,pdf|max:5120',
//     ]);

//     $model = $media->model;

//     $media->delete();

//     $model->addMediaFromRequest('file')
//           ->toMediaCollection('tip_charts');

//     return back()->with('success', 'Attachment updated');
// }

public function storeMedia(Request $request, Tip $tip)
{
    $request->validate([
        'file' => 'required|mimes:jpg,jpeg,png,webp,pdf|max:5120',
    ]);

    $tip->clearMediaCollection('tip_charts');

    $tip->addMediaFromRequest('file')
        ->toMediaCollection('tip_charts');

    return back()->with('success', 'Attachment added');
}

public function updateMedia(Request $request, Media $media)
{
    $request->validate([
        'file' => 'required|mimes:jpg,jpeg,png,webp,pdf|max:5120',
    ]);

    $model = $media->model;

    $media->delete();

    $model->addMediaFromRequest('file')
          ->toMediaCollection('tip_charts');

    return back()->with('success', 'Attachment updated');
}


// Export CSV of Tips with filters
    public function exportCSV()
    {
        $fileName = 'market_tips_full_export_' . date('Y-m-d') . '.csv';
        
        // Fetch all tips with their category relationship
        $tips = Tip::with('category')->latest()->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = [
            'ID', 'Type', 'Stock Name', 'Token', 'Exchange', 'Call', 'Category', 
            'Entry Price', 'Target 1', 'Target 2', 'Stop Loss', 'Status', 'Trade Status', 'Date Created'
        ];

        $callback = function() use($tips, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($tips as $tip) {
                fputcsv($file, [
                    $tip->id,
                    $tip->tip_type,
                    $tip->stock_name, 
                    $tip->symbol_token,
                    $tip->exchange,
                    $tip->call_type,
                    $tip->category ? $tip->category->name : '',
                    $tip->entry_price,
                    $tip->target_price,
                    $tip->target_price_2 ?? '',
                    $tip->stop_loss,
                    $tip->status,
                    $tip->trade_status,
                    $tip->created_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


}
