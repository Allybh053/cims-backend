<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class TemporaryProfilingController extends Controller
{
    public function destroy()
    {
        $user = auth()->user();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
