<?php

namespace App\Http\Controllers;

use App\Exceptions\DestroyException;
use App\Models\SalonPrice;
use App\Models\SalonService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SalonServiceController extends Controller
{
    // For Admin
    /**
     * @OA\Post (
     *     path="/api/admin/salon-service/store",
     *     tags={"미용실"},
     *     summary="서비스 생성",
     *     description="미용실 서비스 생성",
     *     @OA\RequestBody(
     *         description="서비스 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="category_id", type="integer", description="카테고리 아이디", example=1),
     *                 @OA\Property (property="service_name", type="string", description="서비스 명", example="커트"),
     *                 @OA\Property (property="price_male", type="string", description="남성 가격", example="12345"),
     *                 @OA\Property (property="price_female", type="string", description="여성 가격", example="54321"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Created"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_id' => 'required|numeric',
                'service_name' => 'required|string',
                'price_male' => 'string',
                'price_female' => 'string',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $salonService = SalonService::create([
            'salon_category_id' => $validated['category_id'],
            'service' => $validated['service_name'],
        ]);

        if (!empty($validated['price_male'])) {
            $salonService->salonPrices()->create([
                'gender' => 'M',
                'price' => $validated['price_male'],
            ]);
        }
        if (!empty($validated['price_female'])) {
            $salonService->salonPrices()->create([
                'gender' => 'F',
                'price' => $validated['price_female'],
            ]);
        }

        return response()->json(['service' => SalonService::with(['salonPrices' => function ($query) {
            $query->select('salon_service_id', 'gender', 'price');
        }])->where('id', $salonService->id)->get(['id', 'salon_category_id', 'service'])]);
    }

    /**
     * @OA\Patch (
     *     path="/api/admin/salon-service/update",
     *     tags={"미용실"},
     *     summary="서비스 수정",
     *     description="미용실 서비스 수정",
     *     @OA\RequestBody(
     *         description="서비스 수정을 위한 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *             @OA\Property (property="service_id", type="integer", description="카테고리 아이디", example=1),
     *             @OA\Property (property="service_name", type="string", description="카테고리 명", example="엄준식"),
     *             @OA\Property (property="price_male", type="string", description="남성 가격", example="54321"),
     *             @OA\Property (property="price_female", type="string", description="여성 가격", example="12345"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="404", description="Model Not Found Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_id' => 'required|numeric',
                'service_name' => 'string',
                'price_male' => 'string',
                'price_female' => 'string',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }
        try {
            $salonService = SalonService::findOrFail($validated['service_id']);
        } catch (ModelNotFoundException $modelException) {
            $errorMessage = $modelException->getMessage();
            return response()->json(['error' => $errorMessage], 404);
        }

        $salonService->service = $validated['service_name'];

        if(!$salonService->save()) return response()->json(['error' => 'Failed to update service name'], 500);

        $priceMale = 1;
        $priceFemale = 1;

        if (!empty($validated['price_male'])) {
            $priceMale = $salonService->salonPrices()->where('gender' , 'M')->update(['price' => $validated['price_male']]);
        }
        if (!empty($validated['price_female'])) {
            $priceFemale = $salonService->salonPrices()->where('gender' , 'F')->update(['price' => $validated['price_female']]);
        }

        if(!$priceMale || !$priceFemale) return response()->json(['error' => 'Failed to update price'], 500);

        return response()->json(['success' => 'Updated successfully']);
    }
    /**
     * @OA\Delete (
     *     path="/api/admin/salon-service/destroy/{id}",
     *     tags={"미용실"},
     *     summary="서비스 삭제",
     *     description="미용실 서비스 삭제",
     *      @OA\Parameter(
     *            name="id",
     *            description="삭제할 서비스의 아이디",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="integer"),
     *        ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function destroy(String $id)
    {
        if (!SalonService::destroy($id)) {
            throw new DestroyException('Failed to destroy category');
        }
        return response()->json(['success' => 'Service deleted successfully']);
    }

    // For Student
}
