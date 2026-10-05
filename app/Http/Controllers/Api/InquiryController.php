<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inquiry\CreateInquiryRequest;
use App\Http\Resources\InquiriesCollection;
use App\Http\Resources\PaginationResource;
use App\Mail\InquiryResponse;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InquiryController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:inquiry read', ['only' => ['index']]);
        $this->middleware('can:inquiry update', ['only' => ['update']]);
        $this->middleware('can:inquiry create', ['only' => ['store']]);
        $this->middleware('can:inquiry delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            if (!auth()->user()->hasRole('system admin')) {
                return response()->json([
                    'message' => 'Forbidden',
                    'status' => 403,
                ], 403);
            }

            $inquiries = Inquiry::orderBy('created_at', 'desc')->paginate(10);

            return response()->json([
                'inquiries' => new InquiriesCollection($inquiries),
                'pagination' => new PaginationResource($inquiries)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            // Get all inquiries
            $inquiry = Inquiry::with('inquiry_responses')->findOrFail($id);

            return response()->json([
                'inquiry' => $inquiry,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function store(CreateInquiryRequest $request): JsonResponse
    {
        try {
            // Create inquiry
            Inquiry::create([
                'user_id' => auth()->user()->id,
                'title' => $request->title,
                'question' => $request->question,
            ]);

            return response()->json([
                'message' => 'Inquiry created successfully',
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            // Update role
            $inquiry = Inquiry::findOrFail($id);
            $inquiry->update([
                'admin_id' => auth()->user()->id,
                'answer' => $request->answer,
                'respond_date' => now(),
                'status' => 'close',
            ]);

            $inquiry = $inquiry->fresh();

            $user = User::find($inquiry->user_id);
            $admin = User::find($inquiry->admin_id);

            Mail::to($user->email)->send(new InquiryResponse($admin, $user, $inquiry));

            DB::commit();

            return response()->json([
                'message' => 'Inquiry has been successfully answered',
                'inquiry' => $inquiry,
            ], 200);
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
            $inquiry = Inquiry::findOrFail($id);

            // Delete role
            $inquiry->delete();

            return response()->json([
                'message' => 'Inquiry deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
