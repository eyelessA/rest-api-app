<?php

namespace App\Http\Resources\Organization;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationActivityNameResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activity = $this->activity;

        $getAllActivities = function ($activity) use (&$getAllActivities) {
            $activities = collect([$activity]);
            foreach ($activity->children as $child) {
                $activities = $activities->merge($getAllActivities($child));
            }
            return $activities;
        };

        $allActivities = $getAllActivities($activity);

        $childActivities = $allActivities->filter(function ($childActivity) use ($activity) {
            return $childActivity->id !== $activity->id;
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'building_id' => $this->building_id,
            'activity' => [
                'id' => $activity->id,
                'name' => $activity->name,
                'parent_id' => $activity->parent_id,
                'level' => $activity->level,
                'children' => $childActivities->pluck('name'),
            ],
        ];
    }
}
