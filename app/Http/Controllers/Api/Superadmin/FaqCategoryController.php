<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqCategory\FaqCategoriesCollection;
use App\Http\Resources\PaginationResource;
use App\Models\FaqCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;

class FaqCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:faq category read', ['only' => ['index']]);
        $this->middleware('can:faq category create', ['only' => ['store']]);
        $this->middleware('can:faq category update', ['only' => ['update']]);
        $this->middleware('can:faq category delete', ['only' => ['destroy']]);
    }

    public function index(): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('system admin'), 403, 'Unauthorized');

            // Get all faq category
            $faqs = FaqCategory::query()->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                return $query->where('category_name', 'like', "%{$searchValue}%");
            })->orderBy('created_at', 'desc')->paginate(10);

            return response()->json([
                'categories' => new FaqCategoriesCollection($faqs),
                'pagination' => new PaginationResource($faqs)
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
            abort_if(!auth()->user()->hasRole('system admin'), 403, 'Unauthorized');

            // Create faq category
            $faq = FaqCategory::create([
                'category_name' => $request->categoryName,
            ]);

            return response()->json([
                'message' => 'Faq category created successfully',
                'faq' => $faq,
            ], 201);

        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('system admin'), 403, 'Unauthorized');

            // Update faq
            $faq = FaqCategory::findOrFail($id);
            $faq->update([
                'category_name' => strtolower($request->categoryName),
            ]);

            return response()->json([
                'message' => 'Faq category updated successfully',
                'faq' => $faq,
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
            abort_if(!auth()->user()->hasRole('system admin'), 403, 'Unauthorized');

            // Find faq category
            $faq = FaqCategory::findOrFail($id);

            // Check if faq category is associated with faqs
            if ($faq->faqs->count() > 0) {
                return response()->json([
                    'message' => 'Faq category is associated with faqs',
                    'status' => 422,
                ], 422);
            }

            // Delete faq category
            $faq->delete();

            return response()->json([
                'message' => 'Faq category deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
