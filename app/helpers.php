<?php

use App\Events\NotifyUser;
use App\Models\Notification;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// function random_code()
// {
//     return rand(1111, 9999);
// }


// function allUpper($str)
// {
//     return strtoupper($str);
// }


// function tenantCode()
// {
//     $code = 'central';
//     // $tenantcode = TenantUser::where('email', auth()->user()->email)->first()->tenant_code;
//     // if($tenantcode){
//     //     $code = $tenantcode;
//     // }

//     return strtolower($code);
// }

function initializeTenant(): void
{
    if (auth()->user()->agency_id) {
        $tenant = Tenant::find(auth()->user()->agency_id);

        tenancy()->initialize($tenant);
    }
}

function endConnectionTenant(): void
{
    if (auth()->user()->agency_id) {
        tenancy()->end();
    }
}

function getImage($media): ?string
{
    $mimeType = $media['custom_properties']['mime_type'];

    $path = null;

    if ($mimeType) {

        $explodePath = explode('/', $media['original_url']);

        $pathImage = storage_path('app/' . $explodePath[2] . '/' . $explodePath[3]);

        $path = "data:{$mimeType};base64," . base64_encode(file_get_contents($pathImage));
    }

    return $path;
}

function convertToArray($medias): Collection
{
    $arrayMedias = collect();
    foreach ($medias as $media) {
        $arrayMedias->push($media);
    }

    return $arrayMedias;
}

function getCaseFileMedia($caseFiles, $type)
{
    $mediaCaseFile = [];
    foreach ($caseFiles as $caseFile) {
        foreach ($caseFile->getMedia($type) as $media) {
            $mediaCaseFile[$media->uuid] = json_decode(json_encode($media), true);
        }
    }

    return $mediaCaseFile;
}

function sendNotification($content, $userIds){
    $notification = Notification::create([
        'type' => 'general',
        'data' => $content,
        'target_url' => '',
    ]);

    $notification->users()->attach($userIds);

    event(new NotifyUser($notification, $userIds));
}

if (!function_exists('array_keys_to_lowercase')) {
    function array_keys_to_lowercase($array) {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = is_string($key) ? strtolower($key) : $key;
            $result[$newKey] = is_array($value) ? array_keys_to_lowercase($value) : $value;
        }
        return $result;
    }
}

if (!function_exists('convert_empty_arrays_to_dash')) {
    function convert_empty_arrays_to_dash($array) {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && empty($value)) {
                // Replace empty array with dash
                $result[$key] = "N/A";
            } elseif (is_array($value)) {
                // Recursively process nested arrays
                $result[$key] = convert_empty_arrays_to_dash($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
