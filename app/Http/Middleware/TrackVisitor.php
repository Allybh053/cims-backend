<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Visitor;
use Illuminate\Support\Facades\Auth;

class TrackVisitor
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // $ip = $request->ip();
            $userAgent = $request->userAgent();
            // $currentIp = request()->header('X-Forwarded-For');
            // $currentIp = $request->ip();
            $currentIp = $request->ip() ?? '127.0.0.1';

            // Check if the user has already been logged today
            $existingVisitor = Visitor::where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->first();

            if (!$existingVisitor) {
                Visitor::create([
                    'user_id' => $user->id,
                    'ip_address' => $currentIp,
                    'user_agent' => $userAgent,
                ]);
            }
        }

        return $next($request);
    }
}
