<?php

namespace App\Services;

use App\Models\DataApplication;
use App\Models\DataApplicationStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Request as FacadesRequest;

class DataApplicationService
{
    public function fetchDataApplication()
    {
        $data = [];

        $search = FacadesRequest::input('search');
        $langReq = FacadesRequest::input('lang');
        $searchIds = [];

        if ($search) {

            // Check if the status is in my language
            $searchStatus = $search;
            if ($langReq == 'my') {
                $searchStatus = Lang::get('status.' . $search, [], $langReq);
            }

            // Collect possible IDs based on the search input
            $titleIds = DataApplication::where('title', 'like', "%{$search}%")->pluck('title')->toArray();
            $statusIds = DataApplicationStatus::where('status_name', 'like', "%{$searchStatus}%")->pluck('id')->toArray();
            $userIds = User::where('name', 'like', "%{$search}%")->pluck('name')->toArray();

            $searchIds = array_merge($statusIds, $userIds, $titleIds);
            // Add the original search term to the search IDs (in case it's an ID itself)
            if (is_numeric($search)) {
                $searchIds[] = $search;
            }
        }

        if (auth()->user()->hasRole('data request admin')) {  // Data Request Admin
            $data = DataApplication::query()
                ->when($search, function ($query) use ($search, $searchIds) {
                    return $query->where(function ($query) use ($search, $searchIds) {
                        $query->whereIn('data_application_status_id', $searchIds)
                            ->orWhereIn('requestor_id', $searchIds)
                            ->orWhereIn('requestor_agency_id', $searchIds)
                            ->orWhere('title', 'like', "%{$search}%");
                    });
                })
                ->whereHas('dataApplicationStatus', function ($query) {
                    $query->whereIn('status_name', ['confirm', 'incomplete']);
                })
                ->where(function ($query) {
                    $query->where('receiver_agency_id', auth()->user()->agency_id)
                        ->orWhere(function ($query) {
                            $query->where('data_application_status_id', '6')
                                ->where('receiver_id', auth()->user()->id);
                        });
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

        } else if (auth()->user()->hasRole('investigator')) {

            \Log::debug($searchIds);

            $data = DataApplication::query()
                ->when($search, function ($query) use ($search, $searchIds) {
                    return $query->where(function ($query) use ($search, $searchIds) {
                        foreach ($searchIds as $searchValue) {
                            $query->orWhere('data_application_status_id', 'like', "%{$searchValue}%")
                                ->orWhere('title', 'like', "%{$search}%");;
                        }
                    });
                })
                ->where('requestor_id', auth()->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return $data;
    }


    public function fetchDataInProgress()
    {
        $data = [];
        $search = FacadesRequest::input('search');
        $langReq = FacadesRequest::input('lang');
        $searchIds = [];

        if ($search) {

            // Check if the status is in my language
            $searchStatus = $search;
            if ($langReq == 'my') {
                $searchStatus = Lang::get('status.' . $search, [], $langReq);
            }

            // Collect possible IDs based on the search input
            $titleIds = DataApplication::where('title', 'like', "%{$search}%")->pluck('title')->toArray();
            $statusIds = DataApplicationStatus::where('status_name', 'like', "%{$searchStatus}%")->pluck('id')->toArray();
            $userIds = User::where('name', 'like', "%{$search}%")->pluck('name')->toArray();

            $searchIds = array_merge($statusIds, $userIds, $titleIds);
            // Add the original search term to the search IDs (in case it's an ID itself)
            if (is_numeric($search)) {
                $searchIds[] = $search;
            }
        }

        if (auth()->user()->hasRole('data request admin')) {  // Data Request Admin
            $data = DataApplication::query()
                ->when($search, function ($query) use ($search, $searchIds) {
                    return $query->where(function ($query) use ($search, $searchIds) {
                        $query->whereIn('data_application_status_id', $searchIds)
                            ->orWhereIn('requestor_id', $searchIds)
                            ->orWhereIn('requestor_agency_id', $searchIds)
                            ->orWhere('title', 'like', "%{$search}%");
                    });
                })
                ->whereHas('dataApplicationStatus', function ($query) {
                    $query->whereIn('status_name', ['complete']);
                })
                ->where(function ($query) {
                    $query->where('receiver_agency_id', auth()->user()->agency_id)
                        ->orWhere(function ($query) {
                            $query->where('data_application_status_id', '5')
                                ->where('receiver_id', auth()->user()->id);
                        });
                })
                ->orderBy('created_at', 'desc')->paginate(10);
        }

        return $data;
    }

    public function fetchApplicationAgencies()
    {
        $data = [];
//        if (auth()->user()->hasRole('data request admin')) {  // Data Request Admin
            $data =  Tenant::where('status', true)->get();
//        }

        return $data;
    }

    public function fetchStatusesList()
    {
        $data = [];
        if (auth()->user()->hasRole('data request admin')) {
            $data = DataApplicationStatus::whereIn('status_name', ['approved', 'rejected', 'complete', 'incomplete'])->get();
        } else if (auth()->user()->hasRole('investigator')) {
            $data = DataApplicationStatus::whereIn('status_name', ['draft', 'confirm'])->get();
        }

        return $data;
    }

    public function fetchDataApplicationHistory()
    {
        $search = FacadesRequest::input('search');
        $langReq = FacadesRequest::input('lang');
        $searchIds = [];

        if ($search) {

            // Check if the status is in my language
            $searchStatus = $search;
            if ($langReq == 'my') {
                $searchStatus = Lang::get('status.' . $search, [], $langReq);
            }

            // Collect possible IDs based on the search input
            $titleIds = DataApplication::where('title', 'like', "%{$search}%")->pluck('title')->toArray();
            $statusIds = DataApplicationStatus::where('status_name', 'like', "%{$searchStatus}%")->pluck('id')->toArray();
            $userIds = User::where('name', 'like', "%{$search}%")->pluck('name')->toArray();

            $searchIds = array_merge($statusIds, $userIds, $titleIds);
            // Add the original search term to the search IDs (in case it's an ID itself)
            if (is_numeric($search)) {
                $searchIds[] = $search;
            }
        }

        return DataApplication::whereHas('dataApplicationStatus', function ($query) {
                $query->whereIn('status_name', ['approved', 'rejected']);
            })
            ->when($search, function ($query) use ($search, $searchIds) {
                return $query->where(function ($query) use ($search, $searchIds) {
                    $query->whereIn('data_application_status_id', $searchIds)
                        ->orWhereIn('requestor_id', $searchIds)
                        ->orWhereIn('requestor_agency_id', $searchIds)
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->where('receiver_agency_id', auth()->user()->agency_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function fetchCaseFiles()
    {
        initializeTenant();

        $user = User::whereEmail(auth()->user()->email)->first();

        $caseFiles = $user->caseFiles()->wherePivot('status', 'owner')->orderBy('created_at', 'desc')->get();

        $sharedCaseFiles = $user->caseFiles()->wherePivot('status', '!=', 'owner')
            ->wherePivot('expired_at', null)
            ->orWherePivot('expired_at', '>=', now())
            ->get();

        return $caseFiles->merge($sharedCaseFiles);
    }
}
