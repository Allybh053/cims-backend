<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvestigationNoteCollection;
use App\Http\Resources\PaginationResource;
use App\Models\CaseFile;
use App\Models\InvestigationNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $data = [
              'color' => $request->color,
              'notes' => $request->notes,
            ];

            InvestigationNote::create([
                'case_id' => $request->case_id,
                'title' => $request->title,
                'data' => json_encode($data),
            ]);

            return response()->json([
                'message' => 'Note created successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            initializeTenant();

            $caseFile = CaseFile::find($id);

            $notes = $caseFile->notes()->orderBy('created_at', 'desc')->paginate(5);

            return response()->json([
                'notes' => new InvestigationNoteCollection($notes),
                'pagination' => new PaginationResource($notes)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function latestNotes($id): JsonResponse
    {
        try {
            initializeTenant();

            $caseFile = CaseFile::find($id);

            $notes = $caseFile->notes()->orderBy('created_at', 'desc')->take(3)->get();

            return response()->json([
                'notes' => new InvestigationNoteCollection($notes),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            initializeTenant();

            $data = [
                'color' => $request->color,
                'notes' => $request->notes,
            ];

            $note = InvestigationNote::find($id);

            $note->update([
                'title' => $request->title,
                'data' => json_encode($data),
            ]);

            return response()->json([
                'message' => 'Note updated successfully',
                'status' => 200
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
            initializeTenant();

            InvestigationNote::find($id)->delete();

            return response()->json([
                'message' => 'Note deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
