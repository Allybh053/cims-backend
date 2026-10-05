<?php
namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Faq\FaqsCollection;
use App\Http\Resources\PaginationResource;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;

class FaqController extends Controller
{
     public function __construct()
     {
         $this->middleware('can:faq read', ['only' => ['index']]);
         $this->middleware('can:faq create', ['only' => ['store']]);
         $this->middleware('can:faq update', ['only' => ['update']]);
         $this->middleware('can:faq delete', ['only' => ['destroy']]);
     }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('system admin'), 403, 'Unauthorized');

            // Get all faqs
            $faqs = Faq::query()->with('category')->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                return $query->where('question', 'like', "%{$searchValue}%")
                    ->orWhere('answer', 'like', "%{$searchValue}%")
                    ->orWhereRaw('CAST(position AS CHAR) LIKE ?', "%{$searchValue}%");
            })
            ->orderBy('faq_category_id')
            ->orderBy('position')
            ->paginate(10);

            return response()->json([
                'faqs' => new FaqsCollection($faqs),
                'pagination' => new PaginationResource($faqs)
            ], 200);
        } catch (\Throwable $th) {
            \Log::debug($th);
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

            // Create faq
            $faq = Faq::create([
                'question' => $request->question,
                'answer' => $request->answer,
                'faq_category_id' => $request->category,
                'position' => $request->position,
                'enabled' => $request->enabled,
            ]);

            return response()->json([
                'message' => 'Faq created successfully',
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

    public function update(Request $request, $id)
    {
        try {
            abort_if(!auth()->user()->hasRole('system admin'), 403, 'Unauthorized');

            // Update faq
            $faq = Faq::findOrFail($id);
            $faq->update([
                'question' => $request->question,
                'answer' => $request->answer,
                'faq_category_id' => $request->category,
                'position' => $request->position,
                'enabled' => $request->enabled,
            ]);

            return response()->json([
                'message' => 'Faq updated successfully',
                'faq' => $faq,
            ], 200);
        } catch (\Throwable $th) {
            \Log::debug($th);
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

            // Find faq
            $faq = Faq::findOrFail($id);

            // Delete faq
            $faq->delete();

            return response()->json([
                'message' => 'Faq deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
