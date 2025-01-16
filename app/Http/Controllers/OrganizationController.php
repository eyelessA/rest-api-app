<?php

namespace App\Http\Controllers;

use App\Exceptions\OrganizationNotFoundException;
use App\Http\Requests\Organization\OrganizationActivityNameRequest;
use App\Http\Requests\Organization\OrganizationNameRequest;
use App\Http\Requests\Organization\OrganizationNearbyRequest;
use App\Http\Resources\Organization\OrganizationActivityNameResource;
use App\Http\Resources\Organization\OrganizationResource;
use App\Services\Organization\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

/**
 * @OA\Info(
 *     title="rest-api-app",
 *     version="2.0.0"
 * ),
 * @OA\PathItem(
 *     path="/api/"
 * )
 */
class OrganizationController extends Controller
{
    private OrganizationService $organizationService;

    public function __construct(
        OrganizationService $organizationService
    )
    {
        $this->organizationService = $organizationService;
    }

    /**
     * @OA\Get(
     *     path="/api/organization/building/{id}",
     *     summary="Список всех организаций, находящихся в конкретном здании",
     *     tags={"Organization"},
     *     description="Возвращает коллекцию организаций, связанных с заданным зданием.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID здания",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="ID организации", example=1),
     *                 @OA\Property(property="name", type="string", description="Название организации", example="ООО Рога и Копыта"),
     *                 @OA\Property(property="phone_number", type="string", description="Номер телефона", example="2-222-222"),
     *                 @OA\Property(property="building_id", type="integer", description="ID здания", example=1),
     *                 @OA\Property(property="activity", type="object", description="Информация о виде деятельности",
     *                     @OA\Property(property="id", type="integer", description="ID вида деятельности", example=1),
     *                     @OA\Property(property="name", type="string", description="Название вида деятельности", example="Еда"),
     *                     @OA\Property(property="parent_id", type="integer", description="ID родительского вида деятельности", example=null),
     *                     @OA\Property(property="level", type="integer", description="Уровень иерархии", example=1),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Организации не найдены"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера"
     *     )
     * )
     */
    public function getOrganizationsInBuilding(int $id): mixed
    {
        try {
            $organizations = $this->organizationService->getOrganizationsInBuilding($id);
            return OrganizationResource::collection($organizations)->resource;
        } catch (OrganizationNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 404
            ], 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/organization/activity/{id}",
     *     summary="Список всех организаций, относящихся к указанному виду деятельности",
     *     tags={"Organization"},
     *     description="Возвращает коллекцию организаций, которые имеют указанный вид деятельности.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID вида деятельности",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="ID организации", example=2),
     *                 @OA\Property(property="name", type="string", description="Название организации", example="ООО Apple"),
     *                 @OA\Property(property="phone_number", type="string", description="Номер телефона", example="3-333-333"),
     *                 @OA\Property(property="building_id", type="integer", description="ID здания", example=2),
     *                 @OA\Property(property="activity", type="object", description="Информация о виде деятельности",
     *                     @OA\Property(property="id", type="integer", description="ID вида деятельности", example=2),
     *                     @OA\Property(property="name", type="string", description="Название вида деятельности", example="Автомобили"),
     *                     @OA\Property(property="parent_id", type="integer", description="ID родительского вида деятельности", example=null),
     *                     @OA\Property(property="level", type="integer", description="Уровень иерархии", example=1),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Организации не найдены для указанного вида деятельности"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера"
     *     )
     * )
     */
    public function getOrganizationActivity(int $id): mixed
    {
        try {
            $organizations = $this->organizationService->getOrganizationActivity($id);
            return OrganizationResource::collection($organizations)->resource;
        } catch (OrganizationNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 404
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/organization/nearby",
     *     summary="Список организаций в указанной области",
     *     tags={"Organization"},
     *     description="Возвращает список организаций и зданий, которые находятся в заданной области, указанной массивом координат.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Массив координат, задающих область поиска.",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"coordinates"},
     *             @OA\Property(
     *                 property="coordinates",
     *                 type="array",
     *                 description="Массив координат, задающий область. Каждая координата представлена как массив [широта, долгота].",
     *                 @OA\Items(
     *                     type="array",
     *                     @OA\Items(type="number", format="float", example=55.7558),
     *                     @OA\Items(type="number", format="float", example=37.6173)
     *                 ),
     *                 example={
     *                     {55.978508, 37.170409},
     *                     {55.971481, 37.174577},
     *                     {55.982639, 37.179951}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="organizations",
     *                 type="array",
     *                 description="Список организаций",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", description="ID здания", example=1),
     *                     @OA\Property(property="name", type="string", description="Название здания", example="ООО Рога и Копыта"),
     *                     @OA\Property(property="phone_number", type="string", description="Номер телефона", example="3-333-333"),
     * *                   @OA\Property(property="building_id", type="integer", description="ID здания", example=2),
     * *                   @OA\Property(property="activity", type="object", description="Информация о виде деятельности"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Некорректные входные данные",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера"
     *     )
     * )
     */
    public function getNearbyOrganizations(OrganizationNearbyRequest $organizationNearbyRequest): JsonResponse|Collection
    {
        $data = $organizationNearbyRequest->validated();
        return $this->organizationService->getNearbyOrganizations($data['coordinates']);
    }

    /**
     * @OA\Get(
     *     path="/api/organization/{id}",
     *     summary="Получить информацию об организации по её идентификатору",
     *     tags={"Organization"},
     *     description="Возвращает детальную информацию об организации, включая её вид деятельности и здание.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID организации",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", description="ID организации", example=2),
     *             @OA\Property(property="name", type="string", description="Название организации", example="ООО Apple"),
     *             @OA\Property(property="phone_number", type="string", description="Номер телефона", example="3-333-333"),
     *             @OA\Property(property="building_id", type="integer", description="ID здания", example=1),
     *             @OA\Property(property="activity", type="object", description="Информация о виде деятельности",
     *                 @OA\Property(property="id", type="integer", description="ID вида деятельности", example=1),
     *                 @OA\Property(property="name", type="string", description="Название вида деятельности", example="Еда"),
     *                 @OA\Property(property="parent_id", type="integer", description="ID родительского вида деятельности", example=null),
     *                 @OA\Property(property="level", type="integer", description="Уровень иерархии", example=1),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Организация не найдена"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера"
     *     )
     * )
     */
    public function getOrganization(int $id): OrganizationResource|JsonResponse
    {
        $organization = $this->organizationService->getOrganization($id);
        return OrganizationResource::make($organization);
    }

    /**
     * @OA\Get(
     *     path="/api/organization/activity/",
     *     summary="Получить организации по виду деятельности",
     *     tags={"Organization"},
     *     description="Возвращает коллекцию организаций, которые соответствуют указанному виду деятельности.",
     *     @OA\Parameter(
     *         name="activity",
     *         in="query",
     *         required=true,
     *         description="Название вида деятельности, по которому нужно найти организации",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="ID организации", example=1),
     *                 @OA\Property(property="name", type="string", description="Название организации", example="ООО Рога и Копыта"),
     *                 @OA\Property(property="phone_number", type="string", description="Номер телефона организации", example="2-222-222"),
     *                 @OA\Property(property="building_id", type="integer", description="ID здания организации", example=1),
     *                 @OA\Property(property="activity_id", type="integer", description="ID вида деятельности", example=1),
     *                 @OA\Property(
     *                     property="activity",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", description="ID вида деятельности", example=1),
     *                     @OA\Property(property="name", type="string", description="Название вида деятельности", example="Еда"),
     *                     @OA\Property(property="parent_id", type="integer", description="ID родительской активности", example=null),
     *                     @OA\Property(property="level", type="integer", description="Уровень активности", example=1),
     *                     @OA\Property(
     *                         property="children",
     *                         type="array",
     *                         @OA\Items(type="string", description="Названия дочерних активностей", example="Мясная продукция"),
     *                         @OA\Items(type="string", description="Названия дочерних активностей", example="Молочная продукция"),
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Не найдено организаций для указанного вида деятельности"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера"
     *     )
     * )
     */
    public function getOrganizationActivityName(OrganizationActivityNameRequest $organizationActivityRequest): mixed
    {
        try {
            $data = $organizationActivityRequest->validated('activity');
            $organizations = $this->organizationService->getOrganizationActivityName($data);

            return OrganizationActivityNameResource::collection($organizations)->resource;
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 404
            ], 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/organization/search-by-name/",
     *     summary="Поиск организации по названию",
     *     tags={"Organization"},
     *     description="Возвращает организацию по указанному названию. Если организация не найдена, возвращается пустой результат.",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Название организации для поиска",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", description="ID организации", example=1),
     *             @OA\Property(property="name", type="string", description="Название организации", example="ООО Рога и Копыта"),
     *             @OA\Property(property="phone_number", type="string", description="Номер телефона организации", example="2-222-222"),
     *             @OA\Property(property="building_id", type="integer", description="ID здания организации", example=1),
     *             @OA\Property(property="activity_id", type="integer", description="ID вида деятельности", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Организация не найдена"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера"
     *     )
     * )
     */
    public function getOrganizationSearchByName(OrganizationNameRequest $organizationNameRequest): mixed
    {
        $data = $organizationNameRequest->validated('name');
        $organization = $this->organizationService->getOrganizationSearchByName($data);
        return OrganizationResource::collection($organization)->resource;
    }
}
