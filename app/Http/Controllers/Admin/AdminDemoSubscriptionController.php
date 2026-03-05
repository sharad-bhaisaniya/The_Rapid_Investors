<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminDemoSubscriptionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view demo subscriptions', only: ['index']),
            new Middleware('permission:edit demo subscriptions', only: ['grantDemo', 'updateStatus']),
        ];
    }


    public function index(Request $request)
    {
        $query = User::query()->with(['subscriptions' => function ($q) {
            $q->latest();
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'never') {
                $query->doesntHave('subscriptions');
            } elseif ($request->status === 'active') {
                $query->whereHas('subscriptions', function($q) {
                    $q->where('status', 'active')->where('end_date', '>', now());
                });
            } elseif ($request->status === 'suspended') {
                $query->whereHas('subscriptions', function($q) {
                    $q->where('status', 'suspended');
                });
            }
        }

        if ($request->filled('type')) {
            $query->whereHas('subscriptions', function($q) use ($request) {
                $q->where('payment_status', $request->type);
            });
        }

        $users = $query->paginate(10);
        return view('admin.demo_subscription.index', compact('users'));
    }

    public function grantDemo(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'days'    => 'required|integer|min:1|max:30',
        ]);

        $lastSub = UserSubscription::where('user_id', $request->user_id)->latest()->first();

        if ($lastSub && $lastSub->isActive()) {
            return back()->with('error', 'User already has active access!');
        }

        UserSubscription::create([
            'user_id' => $request->user_id,
            'payment_status' => 'demo',
            'start_date' => now(),
            'end_date' => now()->addDays((int) $request->days),    
            'status' => 'active',
        ]);

        return back()->with('success', 'Demo access granted successfully!');
    }

    /**
     * Update subscription status (Suspend/Activate)
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:user_subscriptions,id',
            'status' => 'required|in:active,suspended'
        ]);

        $subscription = UserSubscription::findOrFail($request->subscription_id);
        $subscription->update(['status' => $request->status]);

        $msg = $request->status === 'suspended' ? 'Subscription suspended.' : 'Subscription activated.';
        return back()->with('success', $msg);
    }
}

// namespace App\Http\Controllers\Admin;

// use App\Http\Controllers\Controller;
// use App\Models\User;
// use App\Models\UserSubscription;
// use Illuminate\Http\Request;

// class AdminDemoSubscriptionController extends Controller
// {
    
//     public function index(Request $request)
// {
//     $query = User::query()->with(['subscriptions' => function ($q) {
//         $q->latest();
//     }]);

//     // Filter by Name, Email, or Phone
//     if ($request->filled('search')) {
//         $search = $request->search;
//         $query->where(function($q) use ($search) {
//             $q->where('name', 'like', "%{$search}%")
//               ->orWhere('email', 'like', "%{$search}%")
//               ->orWhere('phone', 'like', "%{$search}%"); // Ensure you have a phone column
//         });
//     }

//     // Filter by Status (Requires slightly more logic for Eloquent)
//     if ($request->filled('status')) {
//         if ($request->status === 'never') {
//             $query->doesntHave('subscriptions');
//         } elseif ($request->status === 'active') {
//             $query->whereHas('subscriptions', function($q) {
//                 $q->where('status', 'active')->where('end_date', '>', now());
//             });
        
//         }
//     }

//     // Filter by Type (Demo vs Paid)
//     if ($request->filled('type')) {
//         $query->whereHas('subscriptions', function($q) use ($request) {
//             $q->where('payment_status', $request->type);
//         });
//     }

//     $users = $query->paginate(10); // Added pagination for professionalism

//     return view('admin.demo_subscription.index', compact('users'));
// }

//     public function grantDemo(Request $request)
//     {
//         $request->validate([
//             'user_id' => 'required|exists:users,id',
//             'days'    => 'required|integer|min:1|max:30',
//         ]);

//         $lastSub = UserSubscription::where('user_id', $request->user_id)
//                     ->latest()
//                     ->first();

//         // ❌ Block if active subscription exists
//         if ($lastSub && $lastSub->isActive()) {
//             return back()->with('error', 'User already has active subscription!');
//         }

//         // ✅ Create demo subscription
//         UserSubscription::create([
//             'user_id' => $request->user_id,
//             'payment_status' => 'demo',
//             'start_date' => now(),
//             'end_date' => now()->addDays((int) $request->days),    
//             'status' => 'active',
//         ]);

//         return back()->with('success', 'Demo access granted successfully!');
//     }
// }