<?php

namespace App\Services\Organization;

use App\Exceptions\BuildingNotFoundException;
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
     * @throws BuildingNotFoundException
     */
    public function getOrganizationsInBuilding(int $id): mixed
    {
        $building = Building::query()->find($id);

        if (!$building) {
            throw new BuildingNotFoundException("Здание с ID {$id} не найдено");
        }

        $organizations = $building->organizations;

        if ($organizations->isEmpty()) {
            throw new BuildingNotFoundException("Организации для здания {$building->address} не найдены");
        }

        return $organizations;
    }

    /**
     * @throws OrganizationNotFoundException
     */
    public function getOrganizationActivity(int $id): Collection
    {
        $activity = Activity::query()->find($id);

        if (!$activity) {
            throw new OrganizationNotFoundException("Деятельность с ID {$id} не найдено");
        }

        $organizations = $activity->organizations;

        if ($organizations->isEmpty()) {
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

    /**
     * @throws OrganizationNotFoundException
     */
    public function getOrganization(int $id): Organization|Collection|Model
    {
        $organization = Organization::query()->find($id);
        if (!$organization) {
            throw new OrganizationNotFoundException('Организация не найдена');
        }
        return $organization;
    }

    /**
     * @throws OrganizationNotFoundException
     */
    public function getOrganizationActivityName(string $activity): Collection
    {
        $activities = Activity::query()->where('name', $activity)->get();

        if ($activities->isEmpty()) {
            throw new OrganizationNotFoundException("Организация c видом деятельности {$activity} не найдена");
        }

        $allActivities = collect();

        foreach ($activities as $activity) {
            $allActivities->push($activity->id);
            $allActivities = $allActivities->merge($activity->children()->pluck('id'));
        }
        return Organization::query()->whereIn('activity_id', $allActivities)->get();
    }

    /**
     * @throws OrganizationNotFoundException
     */
    public function getOrganizationSearchByName(string $name)
    {
        $organization = Organization::query()->where('name', '=', $name)->first();
        if (!$organization) {
            throw new OrganizationNotFoundException("Организация по имени {$name} не найдена");
        }
        return $organization;
    }
}
