<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InquiryResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InquiryResponseController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Get all inquiries
            $inquiry_responses = (new Inquiry())->newQuery();
            $inquiry_responses = $inquiry_responses->latest()->get()->toArray();

            return response()->json([
                'inquiry_responses' => $inquiry_responses,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Create role
            $inquiry_response = Inquiry::create([
                'user_id' => $request->user_id,
                'response' => $request->response,
            ]);

            return response()->json([
                'response' => 'Inquiry response created successfully',
                'inquiry' => $inquiry_response,
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }


    public function destroy($id): JsonResponse
    {
        try {
            // Find role
            $inquiry_response = InquiryResponse::findOrFail($id);

            // Delete role
            $inquiry_response->delete();

            return response()->json([
                'message' => 'Inquiry response deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
