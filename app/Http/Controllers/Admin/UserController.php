<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Log;
use App\Models\DeletedUser;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\KycVerification;
use App\Models\UserSubscription;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $users = User::with('roles', 'media')
        ->role('customer')->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'city' => $user->city,
                'status' => $user->status,
                'notes' => $user->notes ?? '', // Add this if you have notes field
                'roles' => $user->getRoleNames()->toArray(), // Make sure it's an array
                'profile_image_url' => $user->getFirstMediaUrl('profile_images') // Use correct collection name
            ];
        });

        $selectedUserId = $request->query('user');

        return view('admin.users.index', compact('users', 'selectedUserId'));
    }


  


    // public function listUsers(Request $request)
    // {
    //     // 1. Get Active Users (Customers)
    //     $activeUsers = User::with(['roles', 'media','subscriptions', 'refundRequests' ])
    //         ->role('customer')
    //         ->get()
    //         ->map(function($user) {
    //             return [
    //                 'id'         => $user->id,
    //                 'name'       => $user->name,
    //                 'email'      => $user->email,
    //                 'phone'      => $user->phone,
    //                 'role'       => $user->getRoleNames()->first() ?? 'Customer',
    //                 'status'     => 'Active', // Label for UI
    //                 'is_deleted' => false,    // Flag for Blade logic
    //                 'created_at' => $user->created_at,
    //                 'profile_image' => $user->getFirstMediaUrl('profile_images')
    //             ];
    //         });

    //     // 2. Get Inactive (Deleted) Users
    //     $inactiveUsers = DeletedUser::all()->map(function($dUser) {
    //         // We assume the role was customer since they were moved from the customer list
    //         return [
    //             'id'         => $dUser->original_user_id,
    //             'name'       => $dUser->name,
    //             'email'      => $dUser->email,
    //             'phone'      => $dUser->phone,
    //             'role'       => 'Customer',
    //             'status'     => 'Inactive', // Label for UI
    //             'is_deleted' => true,      // Flag for Blade logic
    //             'created_at' => $dUser->deleted_at_time, // Use deletion time as reference
    //             'profile_image' => null     // Usually media is deleted, or get from JSON if stored
    //         ];
    //     });

    //     // 3. Merge and Paginate
    //     // Note: To paginate a merged collection, we convert to a simple collection 
    //     // or use a LengthAwarePaginator. For simplicity, we'll merge and sort.
    //     $allUsers = $activeUsers->concat($inactiveUsers)->sortByDesc('created_at');

    //     // Manually paginate the collection
    //     $currentPage = request()->get('page', 1);
    //     $perPage = 10;
    //     $currentItems = $allUsers->slice(($currentPage - 1) * $perPage, $perPage)->all();
        
    //     $users = new \Illuminate\Pagination\LengthAwarePaginator(
    //         $currentItems, 
    //         $allUsers->count(), 
    //         $perPage, 
    //         $currentPage, 
    //         ['path' => request()->url(), 'query' => request()->query()]
    //     );

    //     return view('admin.users.listedUsers', compact('users'));
    // }


    public function listUsers(Request $request)
{
    // 1. Active Users (Customers)
    $activeUsers = User::with([
            'roles',
            'media',
            'subscriptions',
            'refundRequests',
        ])       
        ->get()
        ->map(function ($user) {

            // ✅ Find any paid/active subscription
            $subscription = $user->subscriptions
                ->whereIn('status', ['active', 'completed'])
                ->first();

            // ✅ Check if refund already exists for this subscription
            $alreadyRefunded = false;
            if ($subscription) {
                $alreadyRefunded = $user->refundRequests
                    ->where('user_subscription_id', $subscription->id)
                    ->isNotEmpty();
            }

            return [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'role'       => $user->getRoleNames()->first() ?? 'Customer',
                'status'     => 'Active',
                'is_deleted' => false,
                'created_at' => $user->created_at,
                'profile_image' => $user->getFirstMediaUrl('profile_images'),

                // ✅ REFUND FLAGS (IMPORTANT)
                'has_subscription' => (bool) $subscription,
                'can_refund'       => $subscription && !$alreadyRefunded,
                'subscription_id'  => $subscription?->id,
            ];
        });

    // 2. Inactive Users
    $inactiveUsers = DeletedUser::all()->map(function ($dUser) {
        return [
            'id'         => $dUser->original_user_id,
            'name'       => $dUser->name,
            'email'      => $dUser->email,
            'phone'      => $dUser->phone,
            'role'       => 'Customer',
            'status'     => 'Inactive',
            'is_deleted' => true,
            'created_at' => $dUser->deleted_at_time,
            'profile_image' => null,

            // ❌ Refund not allowed
            'has_subscription' => false,
            'can_refund'       => false,
            'subscription_id'  => null,
        ];
    });

    // 3. Merge + Paginate
    $allUsers = $activeUsers->concat($inactiveUsers)->sortByDesc('created_at');

    $currentPage = request()->get('page', 1);
    $perPage = 10;

    $currentItems = $allUsers
        ->slice(($currentPage - 1) * $perPage, $perPage)
        ->values();

    $users = new \Illuminate\Pagination\LengthAwarePaginator(
        $currentItems,
        $allUsers->count(),
        $perPage,
        $currentPage,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    return view('admin.users.listedUsers', compact('users'));
}



    public function update(Request $request, User $user)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'role' => 'nullable|string|in:user,admin,manager,editor',
            'status' => 'required|in:0,1',
            'notes' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            'remove_profile_image' => 'nullable|boolean'
        ]);

        // Update basic user info
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
        ]);

        // Handle role update
        if (isset($validated['role'])) {
            $user->roles()->detach();
            $role = Role::where('name', $validated['role'])->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }
        }

        // Handle profile image with Media Library
        if ($request->hasFile('profile_image')) {
            // IMPORTANT: Clear existing images first
            $user->clearMediaCollection('profile_images');
            
            // Add the new media
            $media = $user->addMediaFromRequest('profile_image')
                ->usingName('profile_image_' . $user->id)
                ->usingFileName($request->file('profile_image')->getClientOriginalName())
                ->toMediaCollection('profile_images', 'public');
            
            // Log for debugging
            Log::info('New profile image uploaded for user ' . $user->id, [
                'media_id' => $media->id,
                'file_name' => $media->file_name,
                'url' => $media->getUrl()
            ]);
        }
        // Handle remove profile image checkbox
        elseif ($request->has('remove_profile_image') && $request->remove_profile_image == '1') {
            // Delete all profile images
            $user->clearMediaCollection('profile_images');
            Log::info('Profile image removed for user ' . $user->id);
        }

        // IMPORTANT: Refresh the user model to get updated media
        $user->refresh();
        
        // Get the latest profile image URL for response
        $profileImageUrl = $user->getFirstMediaUrl('profile_images');
        
        // Debug: Check what's being returned
        Log::info('Update response for user ' . $user->id, [
            'profile_image_url' => $profileImageUrl,
            'media_count' => $user->getMedia('profile_images')->count()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'city' => $user->city,
                'status' => $user->status,
                'notes' => $user->notes,
                'profile_image_url' => $profileImageUrl ?: null,
                'roles' => $user->roles->pluck('name')->toArray()
            ]
        ]);
    }



    public function destroy(User $user)
    {
        // Log the start of the process
        Log::info('Initiating user deletion process.', [
            'admin_id' => auth()->id(),
            'target_user_id' => $user->id,
            'target_user_email' => $user->email
        ]);

        try {
            DB::beginTransaction();

            // 1. Prepare data for the archive
            $userData = $user->toArray();
            
            // Log the data being archived for verification
            Log::debug('User data prepared for archive:', $userData);

            // 2. Store data into deleted_users table
            $deletedRecord = DeletedUser::create([
                'original_user_id'    => $user->id,
                'name'                => $user->name,
                'email'               => $user->email,
                'phone'               => $user->phone,
                'full_profile_data'   => $userData,
                'reason_for_deletion' => 'Admin Delete',
                'deleted_at_time'     => now(),
            ]);

            Log::info('User record successfully archived in deleted_users table.', ['deleted_record_id' => $deletedRecord->id]);

            // 3. Delete the user from main table
            $user->delete();

            DB::commit();

            Log::info('User deletion transaction committed successfully.', ['user_id' => $user->id]);

            return response()->json([
                'success' => true, 
                'message' => 'User deleted and archived successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error with full stack trace for debugging
            Log::error('User deletion failed. Transaction rolled back.', [
                'user_id' => $user->id,
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }


 


    
 
    public function exportCSV(Request $request)
    {
        // status is ONLY a request flag
        $type = $request->get('status', 'active'); // active | inactive | all
        $from = $request->from;
        $to   = $request->to;

        $data = collect();

        /* =========================
        ACTIVE USERS (users table)
        ========================= */
        if ($type === 'active' || $type === 'all') {

            $query = User::role('customer');

            if ($from) {
                $query->whereDate('created_at', '>=', $from);
            }

            if ($to) {
                $query->whereDate('created_at', '<=', $to);
            }

            $active = $query->get()->map(function ($u) {
                return [
                    $u->name,
                    $u->email,
                    $u->phone,
                    optional($u->created_at)->format('Y-m-d'),
                    'Active', // LABEL ONLY
                ];
            });

            $data = $data->merge($active);
        }

        /* =========================
        INACTIVE USERS
        (deleted_users table)
        ========================= */
        if ($type === 'inactive' || $type === 'all') {

            $query = DeletedUser::query();

            if ($from) {
                $query->whereDate('deleted_at_time', '>=', $from);
            }

            if ($to) {
                $query->whereDate('deleted_at_time', '<=', $to);
            }

            $inactive = $query->get()->map(function ($u) {
                return [
                    $u->name,
                    $u->email,
                    $u->phone,
                    optional($u->deleted_at_time)->format('Y-m-d'),
                    'Inactive', 
                ];
            });

            $data = $data->merge($inactive);
        }

        /* =========================
        STREAM CSV RESPONSE
        ========================= */
        return response()->stream(function () use ($data) {

            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, ['Name', 'Email', 'Phone', 'Date', 'Status']);

            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);

        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=customers.csv',
        ]);
    }

    public function show($id) {
        $user = User::with(['subscriptions.plan', 'kycVerification', 'invoices'])
                    ->findOrFail($id);
                    
        $activeSubscription = $user->subscriptions()->where('status', 'active')->latest()->first();
        $kyc = $user->kycVerification; // Assuming 1-to-1 or latest

        return view('admin.users.show', compact('user', 'activeSubscription', 'kyc'));
    }

    public function delete($id)
{
    DB::beginTransaction();

    try {
        // 1. Find user
        $user = User::findOrFail($id);

       
        // 3. Delete KYC records (by user_id)
        KycVerification::where('user_id', $id)->delete();

        // 4. Delete user subscriptions (by user_id)
        UserSubscription::where('user_id', $id)->delete();
         // 2. Deactivate user
        $user->delete();


        DB::commit();

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Customer deleted and related data removed successfully'
        // ]);
        return redirect()->back()->with('success', 'Customer deleted and related data removed successfully');

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}
    
}