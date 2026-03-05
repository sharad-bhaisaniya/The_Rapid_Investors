<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\User; //
use App\Models\DeletedUser; 
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteController extends Controller
{
    /**
     * Delete the authenticated user and move to backup.
     */
    public function destroy(Request $request)
    {
        try {
            // Hum currently logged in user ko le rahe hain
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'User not authenticated'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Transaction start kar rahe hain taaki data safe rahe
            DB::beginTransaction();

            // 1. Pehle data backup table (deleted_users) mein insert karein
            // Aapki table structure ke columns: original_user_id, name, email, phone, full_profile_data
            DeletedUser::create([
                'original_user_id'   => $user->id,
                'name'               => $user->name,
                'email'              => $user->email,
                'phone'              => $user->phone,
                // Pura profile data (adhar, business details, etc.) capture karein
                'full_profile_data'  => $user->toArray(), 
                'reason_for_deletion' => $request->reason ?? 'User requested deletion',
                'deleted_at_time'    => now(),
            ]);

            // 2. Ab user ko main table se delete karein
            $user->delete();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Account deleted successfully and data moved to backup.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while deleting the account.',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}