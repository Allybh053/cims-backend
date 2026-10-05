<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediasCollection;
use App\Http\Resources\PaginationResource;
use App\Models\CaseFile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request as FacadesRequest;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Image;
use setasign\Fpdi\Fpdi;

class MediaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:file management read', ['only' => ['indexImage', 'indexVideo', 'indexDocument']]);
        $this->middleware('can:file management create', ['only' => ['storeImage', 'storeVideo', 'storeDocument']]);
        $this->middleware('can:file management delete', ['only' => ['destroy']]);
    }

    public function indexImage(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $sort = $request->sort;

            $order = null;
            if ($sort == 'name') {
                $order = 'asc';
            } else {
                $order = 'desc';
            }

            $user = User::whereEmail(auth()->user()->email)->first();
            $medias = $user->media()->where('model_id', $user->id)->when(FacadesRequest::input('search'), function ($query, $searchValue) {
//                \Log::debug($searchValue);

//                    if ($query->where('model_type', 'App\Models\User') && $query->where('model_id', $user->id)) {
                        return $query->where('custom_properties', 'like', "%{$searchValue}%");
//                            ->orWhere('file_name', 'like', "%{$searchValue}%")
//                            ->orWhere('mime_type', 'like', "%{$searchValue}%");
//                    }
                })
                ->where('collection_name', 'images')
                ->orderBy($sort, $order)
                ->paginate(10);

            return response()->json([
                'medias' => new MediasCollection($medias),
                'pagination' => new PaginationResource($medias)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function indexVideo(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $sort = $request->sort;

            $order = null;
            if ($sort == 'name') {
                $order = 'asc';
            } else {
                $order = 'desc';
            }

            $user = User::whereEmail(auth()->user()->email)->first();

            $medias = $user->media()
                ->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                    return $query->where('custom_properties', 'like', "%{$searchValue}%");
//                    return $query->where('name', 'like', "%{$searchValue}%")
//                        ->orWhere('file_name', 'like', "%{$searchValue}%")
//                        ->orWhere('mime_type', 'like', "%{$searchValue}%");
                })
                ->where('collection_name', 'videos')
                ->orderBy($sort, $order)
                ->paginate(10);

            return response()->json([
                'medias' => new MediasCollection($medias),
                'pagination' => new PaginationResource($medias)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function indexDocument(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $sort = $request->sort;

            $order = null;
            if ($sort == 'name') {
                $order = 'asc';
            } else {
                $order = 'desc';
            }

            $user = User::whereEmail(auth()->user()->email)->first();

            $medias = $user->media()
                ->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                    return $query->where('custom_properties', 'like', "%{$searchValue}%");
//                    return $query->where('name', 'like', "%{$searchValue}%")
//                        ->orWhere('file_name', 'like', "%{$searchValue}%")
//                        ->orWhere('mime_type', 'like', "%{$searchValue}%");
                })
                ->where('collection_name', 'documents')
                ->orderBy($sort, $order)
                ->paginate(10);

            return response()->json([
                'medias' => new MediasCollection($medias),
                'pagination' => new PaginationResource($medias)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function storeImage(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $user = User::whereEmail(auth()->user()->email)->first();

            if ($request->hasFile('files')) {
                $files = $request->file('files');
                foreach ($files as $file) {

                    $imageFile = Image::make($file);

                    $extension = $file->getClientOriginalExtension();
                    $originalName = $file->getClientOriginalName();
                    $name = time() . '.' . $extension;

                    $width = $imageFile->width();
                    $height = $imageFile->height() * 2;

                    $wordings = $user->name;
                    for ($i = 0; $i < $height; $i++) {
                        $wordings .= $user->name . ' ' . $user->profile->id_no . ' ' . now()->format('d/m/Y') . ' ';
                    }

                    $lines = explode("\n", wordwrap($wordings, 260)); // break line after 120 charecters

                    for ($i = 0; $i < count($lines); $i++) {
                        $offset = ($i * 200); // 50 is line height
                        $imageFile->text($lines[$i], 0, $offset, function ($font) {
                            $font->file(public_path('font/Roboto-Medium.ttf'));
                            $font->size(24);
                            $font->color('#DCDCDC');
                            $font->angle(45);
                        });
                    }

                    $watermarkFile = $imageFile->save(storage_path($originalName));

                    $image = $user->addMedia($watermarkFile->basePath())
                        ->withCustomProperties(['description' => $request->description, 'created_at' => now()])
                        ->toMediaCollection('images', 'local');

                    $image->setCustomProperty('mime_type', $image->mime_type);
                    $image->setCustomProperty('id', $image->id);
                    $image->save();
                }
            }

            return response()->json([
                'message' => 'Image stored successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function storeVideo(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $user = User::whereEmail(auth()->user()->email)->first();

            if ($request->hasFile('files')) {
                $files = $request->file('files');
                foreach ($files as $file) {
                    $video = $user->addMedia($file)
                        ->withCustomProperties(['description' => $request->description, 'created_at' => now()])
                        ->toMediaCollection('videos', 'local');

                    $video->setCustomProperty('mime_type', $video->mime_type);
                    $video->setCustomProperty('id', $video->id);
                    $video->save();
                }
            }

            return response()->json([
                'message' => 'Video stored successfully',
            ], 201);
        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function storeDocument(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $user = User::whereEmail(auth()->user()->email)->first();

            if ($request->hasFile('files')) {
                $files = $request->file('files');
                foreach ($files as $file) {

                    $document = $user->addMedia($file)
                        ->withCustomProperties(['description' => $request->description, 'created_at' => now()])
                        ->toMediaCollection('documents', 'local');

                    $document->setCustomProperty('mime_type', $document->mime_type);
                    $document->setCustomProperty('id', $document->id);
                    $document->save();

                    $filePathDoc = $document->getPath();

                    // This is to watermark the pdf file
                    $this->fillPDFFile($filePathDoc, $filePathDoc, $user);
                }
            }

            return response()->json([
                'message' => 'Document stored successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function fillPDFFile($file, $outputFilePath, $user)
    {
        $fpdi = new FPDI;

        $count = $fpdi->setSourceFile($file);

        for ($i=1; $i<=$count; $i++) {

            $template = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($template);
            $fpdi->AddPage($size['orientation'], array($size['width'], $size['height']));
            $fpdi->useTemplate($template);

            $fpdi->SetFont("helvetica", "", 12);
            $fpdi->SetTextColor(0,0,0);

            $width = $fpdi->GetPageWidth();
            $height = $fpdi->GetPageHeight() * 2;

            $wordings = $user->name;
            for ($i = 0; $i < $height; $i++) {
                $wordings .= $user->name . ' ';
            }

            $lines = explode("\n", wordwrap($wordings, 260)); // break line after 120 charecters

            for ($i = 0; $i < count($lines); $i++) {
                $offset = ($i * 50); // 50 is line height
                $fpdi->Text(0,$offset,$lines[$i]);
                // $fpdi->TextWithRotation(0,$offset,$lines[$i],45,-45);

            }
        }

        return $fpdi->Output($outputFilePath, 'F');
    }

    public function update(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $user = User::whereEmail(auth()->user()->email)->first();

            $media = $user->media()->find($request->id);
            $media->name = $request->name;
//            $media->setCustomProperty('name', $request->name);
            $media->setCustomProperty('description', $request->description);

            $media->save();

            return response()->json([
                'message' => 'Media updated successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {

            initializeTenant();

            $user = User::whereEmail(auth()->user()->email)->first();

            if (str_contains($id, ',')) {

                $ids = explode(",", $id);

                foreach ($ids as $mediaId) {
                    $user->deleteMedia($mediaId);
                }

            } else {
                $user->deleteMedia($id);
            }

            return response()->json([
                'message' => 'Image deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
}
