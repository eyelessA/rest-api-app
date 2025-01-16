<?php

namespace App\Services\Organization;

use App\Exceptions\OrganizationNotFoundException;
use App\Models\Activity;
use App\Models\Building;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrganizationService
{

    /**
     * @throws OrganizationNotFoundException
     */
    public function getOrganizationsInBuilding(int $id): mixed
    {
        $building = Building::query()->findOrFail($id);

        $organizations = $building->organizations;

        if (empty($organizations)) {
            throw new OrganizationNotFoundException("Организации для здания {$building->address} не найдены");
        }

        return $organizations;
    }

    /**
     * @throws OrganizationNotFoundException
     */
    public function getOrganizationActivity(int $id): Collection
    {
        $activity = Activity::query()->findOrFail($id);

        $organizations = $activity->organizations;

        if (empty($organizations)) {
            throw new OrganizationNotFoundException("Организации для деятельности {$activity->name} не найдены");
        }

        return Organization::query()->where('activity_id', $id)->get();
    }

    public function getNearbyOrganizations(array $data): \Illuminate\Support\Collection
    {
        $polygonWKT = 'POLYGON((' . implode(',', array_map(function ($coord) {
                return "{$coord[0]} {$coord[1]}";
            }, $data)) . ', ' . "{$data[0][0]} {$data[0][1]}" . '))';

        $polygonWKTWithSRID = "ST_SetSRID(ST_GeomFromText('{$polygonWKT}'), 4326)";

        return DB::table('organizations')
            ->join('buildings', 'organizations.building_id', '=', 'buildings.id')
            ->select('organizations.id', 'organizations.name', 'organizations.phone_number', 'organizations.building_id', 'organizations.activity_id')
            ->whereRaw("ST_Within(buildings.coordinates, {$polygonWKTWithSRID})")
            ->get();
    }

    public function getOrganization(int $id): Organization|Collection|Model
    {
        return Organization::query()->findOrFail($id);
    }

    /**
     * @throws OrganizationNotFoundException
     */
    public function getOrganizationActivityName(string $activity): Collection
    {
        $activity = Activity::query()->where('name', $activity)->first();

        if (!$activity) {
            throw new OrganizationNotFoundException("Организация c видом деятельности {$activity} не найдена");
        }

        $allActivities = collect([$activity->id]);

        $allActivities = $allActivities->merge($activity->children()->pluck('id'));

        return Organization::query()->whereIn('activity_id', $allActivities)->get();
    }

    public function getOrganizationSearchByName(string $name): Collection
    {
        return Organization::query()->where('name', 'LIKE', "%{$name}%")->get();
    }
}
