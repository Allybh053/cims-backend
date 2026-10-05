<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MaintenanceCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
         return $this->collection->map(function ($maintenance) {
             return [
                 'id' => $maintenance->id,
                 'description' => $maintenance->description,
                 'status' => ucwords($maintenance->status),
                 'date_start' => Carbon::parse($maintenance->date_start)->format('Y-m-d'),
                 'date_end' => Carbon::parse($maintenance->date_end)->format('Y-m-d'),
                 'time_start' => Carbon::parse($maintenance->date_start)->format('H:i:s'),
                 'time_end' => Carbon::parse($maintenance->date_end)->format('H:i:s'),
                 'date' => Carbon::parse($maintenance->date_start)->format('d/m/Y') . ' - ' . Carbon::parse($maintenance->date_end)->format('d/m/Y'),
                 'time' => Carbon::parse($maintenance->date_start)->format('h:i A') . ' - ' . Carbon::parse($maintenance->date_end)->format('h:i A'),
             ];

         });
    }
}
