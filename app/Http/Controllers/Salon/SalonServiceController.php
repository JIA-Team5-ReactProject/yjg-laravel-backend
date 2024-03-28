<?php

namespace App\Http\Controllers\Salon;

use App\Exceptions\DestroyException;
use App\Http\Controllers\Controller;
use App\Models\SalonCategory;
use App\Models\SalonService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalonServiceController extends Controller
{
    public function authorize($ability, $arguments = [SalonService::class])
    {
        return Parent::authorize($ability, $arguments);
    }
    /**
     * @OA\Get (
     *     path="/api/salon/service",
     *     tags={"미용실 - 서비스"},
     *     summary="카테고리 서비스 목록",
     *     description="미용실 특정 카테고리의 서비스 목록을 반환합니다.",
     *     @OA\Parameter(
     *          name="category_id",
     *          description="카테고리 아이디",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="string"),
     *      ),
     *      @OA\Parameter(
     *          name="gender",
     *          description="성별(male or female)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function show(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'category_id' => 'exists:App\Models\SalonCategory,id',
                'gender' => [Rule::in(['male', 'female'])],
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $services = SalonService::query()->select('id', 'salon_category_id', 'service', 'price', 'gender');

        // category_id가 있는 경우
        if (isset($validated['category_id'])) {
            $services = $services->where('salon_category_id', $validated['category_id']);
        }

        // gender가 있는 경우
        if (isset($validated['gender'])) {
            $services = $services->where('gender', $validated['gender']);
        }

        $services = $services->get();

        return response()->json(['services' => $services]);
    }

    /**
     * @OA\Post (
     *     path="/api/salon/service",
     *     tags={"미용실 - 서비스"},
     *     summary="서비스 생성(관리자)",
     *     description="미용실 서비스 생성할 때 사용합니다.",
     *     @OA\RequestBody(
     *         description="서비스 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="category_id", type="integer", description="카테고리 아이디", example=1),
     *                 @OA\Property (property="service", type="string", description="서비스 명", example="커트"),
     *                 @OA\Property (property="gender", type="string", description="성별", example="male"),
     *                 @OA\Property (property="price", type="string", description="가격", example="10000"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Created"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        try {
            $validated = $request->validate([
                'category_id' => 'required|numeric',
                'service' => 'required|string',
                'gender' => 'required|string',
                'price' => 'required|string',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        try {
            $salonCategory = SalonCategory::findOrFail($validated['category_id']);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
        }

        unset($validated['category_id']);

        $salonService = $salonCategory->salonServices()->create($validated);

        return response()->json(['service' => $salonService]);
    }

    /**
     * @OA\Patch (
     *     path="/api/salon/service/{id}",
     *     tags={"미용실 - 서비스"},
     *     summary="서비스 수정(관리자)",
     *     description="미용실의 서비스 정보를 수정 시 사용합니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="삭제할 서비스의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         description="서비스 수정을 위한 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *             @OA\Property (property="service", type="string", description="카테고리 명", example="엄준식"),
     *             @OA\Property (property="gender", type="string", description="성별", example="male"),
     *             @OA\Property (property="price", type="string", description="가격", example="20000"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        try {
            $validated = $request->validate([
                'service' => 'string',
                'gender' => 'string',
                'price' => 'string',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }
        try {
            $salonService = SalonService::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
        }

        foreach ($validated as $key => $value) {
            $salonService->$key = $value;
        }

        if (!$salonService->save()) return response()->json(['error' => '미용실 서비스 수정에 실패하였습니다.'], 500);

        return response()->json(['message' => '미용실 서비스 수정에 성공하였습니다.']);
    }

    /**
     * @OA\Delete (
     *     path="/api/salon/service/{id}",
     *     tags={"미용실 - 서비스"},
     *     summary="서비스 삭제(관리자)",
     *     description="미용실 서비스 삭제에 사용됩니다.",
     *      @OA\Parameter(
     *            name="id",
     *            description="삭제할 서비스의 아이디",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="integer"),
     *        ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        if (!SalonService::destroy($id)) {
            throw new DestroyException('미용실 서비스 삭제에 실패하였습니다.');
        }
        return response()->json(['message' => '미용실 서비스 삭제에 성공하였습니다.']);
    }
}
