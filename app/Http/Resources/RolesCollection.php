<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RolesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => ucwords($role->name),
                'description' => $role->description,
                'permissions' => new PermissionsCollection($role->permissions),
            ];
        });
    }
}
