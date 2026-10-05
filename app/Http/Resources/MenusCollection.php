<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MenusCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($menu) {
            return [
                'id' => $menu->id,
                'title' => $menu->title,
                'slug' => $menu->slug,
                'icon' => $menu->icon,
                'route_name' => $menu->route_name,
                'layout' => $menu->layout,
                'level' => $menu->lvl,
                'position' => $menu->position,
                'role' => ucwords($menu->role->name),
                'parent' => $menu->parent_id,
            ];
        });
    }
}
