<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission; 
use Symfony\Component\HttpFoundation\Response;

class DynamicPermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // 1. Pehle check karein user logged in hai ya nahi
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        /** * 2. Super-Admin Bypass
         * Super-admin ko sab kuch allowed hai, chahe permission bani ho ya nahi.
         */
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        /** * 3. Database Check: Permission exist karti hai?
         * Agar permission database mein nahi milti (null hai), 
         * to hum direct 403 Forbidden error denge.
         */
        $permissionExists = Permission::where('name', $permission)->exists();

        if (!$permissionExists) {
            abort(403, "Security Error: The permission '$permission' has not been defined in the system yet.");
        }

        /** * 4. User Permission Check
         * Ab jab humein pata hai ki permission database mein hai, 
         * tab check karein ki kya user ke paas ye allot ki gayi hai.
         */
        if (!$user->hasPermissionTo($permission)) {
            $userRole = $user->getRoleNames()->first() ?? 'User';
            abort(403, "Access Denied: The '$userRole' role does not have the '$permission' permission.");
        }

        return $next($request);
    }
}