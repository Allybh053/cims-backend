<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CaseFile\CaseFileResource;
use App\Http\Resources\CaseFile\CaseFilesCollection;
use App\Http\Resources\CaseFile\CaseFilesMediaCollection;
use App\Http\Resources\DataApplication\DataApplicationCollection;
use App\Http\Resources\DataApplication\DataApplicationDBCollection;
use App\Http\Resources\FileLevel\FileLevelCollection;
use App\Http\Resources\FileStatus\FileStatusCollection;
use App\Http\Resources\MediasCollection;
use App\Http\Resources\PaginationResource;
use App\Models\CaseFile;
use App\Models\CaseFileDataApplication;
use App\Models\CaseProfile;
use App\Models\DataApplication;
use App\Models\FileLevel;
use App\Models\FileStatus;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Image;

class CaseFileController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:intelligence management read', ['only' => ['index']]);
        $this->middleware('can:intelligence management create', ['only' => ['store']]);
        $this->middleware('can:intelligence management update', ['only' => ['edit', 'update']]);
        $this->middleware('can:intelligence management delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $user = User::whereEmail(auth()->user()->email)->first();

            $caseFiles = $user->caseFiles()->wherePivot('status', 'owner')->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                return $query->where('title', 'like', "%{$searchValue}%")
                    ->orWhere('case_ref_id', 'like', "%{$searchValue}%");
            });
//                ->orderBy('created_at', 'desc')->paginate(10);

            if ($request->confidential) {
                $caseFiles = $caseFiles->where('level_id', $request->confidential);
            }

            if ($request->status) {
                $caseFiles = $caseFiles->where('status_id', $request->status);
            }

            $caseFiles = $caseFiles->orderBy('created_at', 'desc')->paginate(10);

            $statusFiles = FileStatus::all();
            $levelFiles = FileLevel::all();

            return response()->json([
                'caseFiles' => new CaseFilesCollection($caseFiles),
                'pagination' => new PaginationResource($caseFiles),
                'statusFiles' => new FileStatusCollection($statusFiles),
                'levelFiles' => new FileLevelCollection($levelFiles),
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {

            initializeTenant();

            DB::beginTransaction();

            $user = User::whereEmail(auth()->user()->email)->first();

            $message = "Case file created successfully";

            $closedAt = null;
            if ($request->case_file['statusFile'] == 2) {
                $closedAt = now();
            }

            $caseFile = CaseFile::create([
                'case_category' => strtolower($request->case_file['category']),
                'status_id' => $request->case_file['statusFile'],
                'level_id' => $request->case_file['levelFile'],
                'case_ref_id' => $request->case_file['referenceNumber'],
                'case_description' => $request->case_file['description'],
                'title' => $request->case_file['title'],
                'properties' => '{}',
                'original_name' => $request->case_file['title'],
                'last_closed_at' => $closedAt,
            ]);

            // Associate case file to user
            $user->caseFiles()->attach($caseFile->id);

            // Create profiling from integrated search
            if ($request->profiling) {
                $message = "Case file created successfully with profiling data";

                $dataArray = array();
                foreach ($request->profiling as $data) {
                    if (isset($data['posts']) || isset($data['user_profiles'])) {

                        $socialMediaPostsArray = array();
                        if (isset($data['posts'])) {
                            foreach ($data['posts'] as $post) {
                                if ($post['is_choose']) {
                                    $socialMediaPostsArray[] = $post;
                                }
                            }
                        }

                        $socialMediaUserProfilesArray = array();
                        if (isset($data['user_profiles'])) {
                            foreach ($data['user_profiles'] as $post) {
                                if ($post['is_choose']) {
                                    $socialMediaUserProfilesArray[] = $post;
                                }
                            }
                        }

                        $dataArray[] = [
                            'website_link' => $data['website_link'],
                            'social_media' => $data['social_media'],
                            'posts' => $socialMediaPostsArray,
                            'user_profiles' => $socialMediaUserProfilesArray,
                            'search_term' => $data['search_term'],
                            'count_posts' => isset($data['posts']) ? count($data['posts']) : 0,
                            'count_user_profiles' => isset($data['user_profiles']) ? count($data['user_profiles']) : 0,
                        ];

                    } else {
                        $dataArray[] = $data;
                    }
                }

                CaseProfile::create([
                    'case_id' => $caseFile->id,
                    'data' => json_encode($dataArray),
                ]);
            }

            DB::commit();

            tenancy()->end();

            return response()->json([
                'message' => $message,
            ], 201);

        } catch (\Throwable $th) {
            \Log::debug($th->getMessage());
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            initializeTenant();

            $caseFile = CaseFile::find($id);

            $user = User::whereEmail(auth()->user()->email)->first();

            // Check if case file exist
            if ($caseFile == null) {
                return response()->json([
                    'status' => 404,
                ], 404);
            }

            $caseFileExist = $user->caseFiles()->where('case_files.id', $id)->first();

            // Check if user has access to this case file
            if (!$caseFileExist || ($caseFileExist->pivot->expired_at && $caseFileExist->pivot->expired_at <= now())) {
                return response()->json([
                    'status' => 403,
                    'message' => 'You do not have access to this case file',
                ], 403);
            }

            return response()->json([
                'caseFile' => new CaseFileResource($caseFile),
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            initializeTenant();

            $closedAt = null;
            if ($request->statusFile == 2) {
                $closedAt = now();
            }

            CaseFile::where('id', $id)->update([
                'case_category' => strtolower($request->category),
                'status_id' => $request->statusFile,
                'case_ref_id' => $request->referenceNumber,
                'case_description' => $request->description,
                'title' => $request->title,
                'level_id' => $request->levelFile,
                'properties' => '{}',
                'original_name' => $request->title,
                'last_closed_at' => $closedAt,
            ]);

            return response()->json([
                'message' => 'Case file updated successfully',
            ], 200);

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

            $caseFile = CaseFile::find($id);

            $caseFile->delete();

            return response()->json([
                'message' => 'Case file deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function mediaShow($id): JsonResponse
    {
        try {
            initializeTenant();

            $caseFile = CaseFile::query()->find($id);

            $medias = $caseFile->media()->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                    return $query->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('file_name', 'like', "%{$searchValue}%")
                        ->orWhere('mime_type', 'like', "%{$searchValue}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'medias' => new MediasCollection($medias),
                'pagination' => new PaginationResource($medias)
            ], 200);

        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function fetchCopyMedia(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $user = User::where('email', auth()->user()->email)->first();

            $medias = $user->getMedia($request->mediaType);

            // Sort media name
            $medias = $medias->sortBy('name');

            return response()->json([
                'medias' => new CaseFilesMediaCollection($medias),
            ], 200);
        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500
            ]);
        }
    }

    public function storeCopyMedia(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $caseFile = CaseFile::find($request->id);

            foreach ($request->medias as $fileId) {
                $mediaCopy = Media::find($fileId);

                $existingName = $mediaCopy['name'];

                $explodeMimeType = explode('"', pathinfo($mediaCopy, PATHINFO_EXTENSION));

                $mimeType = $explodeMimeType[0];

                // Rewrite media name if file name exist
                $rewriteFileName = null;
                $rewriteName = null;
                $duplicateNumber = 1;
                foreach ($caseFile->getMedia(strtolower($request->mediaType)) as $media) {
                    if ($media->name == $existingName) {
                        $rewriteName = $existingName . ' (' . $duplicateNumber . ')';
                        $rewriteFileName = $rewriteName . '.' . $mimeType;
                        $duplicateNumber++;
                    } else {
                        if ($media->name == $rewriteName) {
                            $rewriteName = $existingName . ' (' . $duplicateNumber . ')';
                            $rewriteFileName = $rewriteName . '.' . $mimeType;
                            $duplicateNumber++;
                        }
                    }
                }

                $copyId = $mediaCopy->copy($caseFile, strtolower($request->mediaType), 'local');

                if ($rewriteName != null && $rewriteFileName != null) {
                    $media = Media::find($copyId['id']);
                    $media->name = $rewriteName;
                    $media->file_name = $rewriteFileName;
                    $media->save();
                }
            }

            return response()->json([
                'message' => 'Media has been copied successfully',
            ], 200);

        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function mediaStore(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $caseFile = CaseFile::find($request->id);

            $user = User::whereEmail(auth()->user()->email)->first();

            if ($request->hasFile('files')) {
                $files = $request->file('files');
                foreach ($files as $file) {

                    $pathFile = $file;
                    $originalName = $file->getClientOriginalName();
                    $fileNameOnly = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    if (strtolower($request->mediaType) == 'image') {
                        $imageFile = Image::make($file);

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
                        $pathFile = $watermarkFile->basePath();
                    }

                    $rewriteName = $originalName;
                    $originalRewriteName = $originalName;
                    $duplicateNumber = 1;
                    foreach ($caseFile->getMedia(strtolower($request->mediaType)) as $media) {

                        // Rewrite file name if file name exist
                        if ($media->name == $fileNameOnly) {

                            $originalRewriteName = $fileNameOnly . ' (' . $duplicateNumber . ')';
                            $rewriteName = $originalRewriteName . $extension;
                            $duplicateNumber++;
                        } else {
                            if ($media->name == $originalRewriteName) {
                                $originalRewriteName = $fileNameOnly . ' (' . $duplicateNumber . ')';
                                $rewriteName = $originalRewriteName . '.' . $extension;
                                $duplicateNumber++;
                            }
                        }
                    }

                    $image = $caseFile->addMedia($pathFile)
                        ->withCustomProperties(['created_at' => now(), 'uploaded_by' => $user->id, 'collection_name' => strtolower($request->mediaType)])
                        ->toMediaCollection(strtolower($request->mediaType), 'local');

                    // Check if file name is different from original file name
                    if ($rewriteName != $originalName) {
                        $image->name = $originalRewriteName;
                        $image->file_name = $rewriteName;
                    }

                    $image->setCustomProperty('mime_type', $image->mime_type);
                    $image->setCustomProperty('id', $image->id);
                    $image->save();
                }
            }

            return response()->json([
                'message' => 'Media uploaded successfully',
            ], 200);

        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function mediaDestroy(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $caseFile = CaseFile::find($request->caseId);

            $caseFile->deleteMedia($request->id);

            return response()->json([
                'message' => 'Media deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function fetchDataApplication($id): JsonResponse
    {
        try {
//            $dataApplications = DataApplication::query()->where('case_file_id', $id)
//                ->when(FacadesRequest::input('search'), function ($query, $searchValue) {
//                    return $query->where('name', 'like', "%{$searchValue}%")
//                        ->orWhere('email', 'like', "%{$searchValue}%")
//                        ->orWhere('agency_name', 'like', "%{$searchValue}%");
//                })
//                ->paginate(10);

            $dataApplications = DB::table('case_file_data_application')
                ->join('data_applications', 'case_file_data_application.data_application_id', '=', 'data_applications.id')
                ->where('case_file_data_application.case_file_id', '=', $id)
                ->paginate(10);

            return response()->json([
                'dataApplications' => new DataApplicationDBCollection($dataApplications),
                'pagination' => new PaginationResource($dataApplications)
            ], 200);

        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function destroyDataApplication(Request $request): JsonResponse
    {
        try {
            CaseFileDataApplication::where('case_file_id', $request->caseFileId)->where('data_application_id', $request->dataApplicationId)->delete();

            return response()->json([
                'message' => 'Data application deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function recent(): JsonResponse
    {
        try {
            initializeTenant();

            $user = User::whereEmail(auth()->user()->email)->first();

            $caseFiles = $user->caseFiles()->wherePivot('status', 'owner')->orderBy('created_at', 'desc')->paginate(5);

            $statusFiles = FileStatus::all();
            $levelFiles = FileLevel::all();

            return response()->json([
                'caseFiles' => new CaseFilesCollection($caseFiles),
                'pagination' => new PaginationResource($caseFiles),
                'statusFiles' => new FileStatusCollection($statusFiles),
                'levelFiles' => new FileLevelCollection($levelFiles),
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
