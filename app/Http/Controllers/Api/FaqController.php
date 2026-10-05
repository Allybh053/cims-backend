<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Faq\FaqsPublicCollection;
use App\Models\Faq;

class FaqController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:faq read', ['only' => ['index']]);
    }

    public function index()
    {
        try {
            if (auth()->user()->hasRole('system admin')) {
                return response()->json([
                    'message' => 'Forbidden',
                    'status' => 403,
                ], 403);
            }

            // Get all faqs where enabled is true
            $faqs = Faq::query()
                ->where('enabled', true)
                ->orderBy('position', 'asc')
                ->get()
                ->groupBy('faq_category_id');

            return response()->json([
//                'faqs' => new FaqsCollection($faqs),
                'faqs' => new FaqsPublicCollection($faqs),
            ], 200);
        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
