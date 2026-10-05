<?php

namespace App\Http\Resources\Profiling;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProfilingsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($profiling) {

            $data = json_decode($profiling->data);

            $canEdit = $profiling->is_create_from_user ? true : false;

             $name = "N/A";
            if (isset($data[0]->name)) {
                $name = $data[0]->name;
            } elseif (isset($data[0]->poi_name)) {
                $name = $data[0]->poi_name;
            } elseif (isset($data[0]->search_term)) {
                $name = $data[0]->search_term;
            }

             $poi = "N/A";
            if (isset($data[0]->name)) {
                $poi = $data[0]->name;
            } elseif (isset($data[0]->poi_name)) {
                $poi = $data[0]->poi_name;
            } elseif (isset($data[0]->search_term)) {
                $poi = $data[0]->search_term;
            }

            return [
                'id' => $profiling->id,
                'name' => $name,
                'POI_name' => $poi,
                'data' => $data,
                'custom_can_edit' => $canEdit,
                'date_created' => date_format(date_create($profiling->created_at), 'd/m/Y'),
            ];
        });
    }
}
