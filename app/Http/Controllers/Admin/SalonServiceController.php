<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\DestroyException;
use App\Http\Controllers\Controller;
use App\Models\SalonService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalonServiceController extends Controller
{
    /**
     * @OA\Get (
     *     path="/api/admin/salon-service/{id}",
     *     tags={"미용실"},
     *     summary="카테고리 서비스 목록",
     *     description="미용실 특정 카테고리의 서비스 목록",
     *      @OA\Parameter(
     *            name="id",
     *            description="찾을 카테고리의 아이디",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="integer"),
     *      ),
     *      @OA\Parameter(
     *        name="gender",
     *        description="찾을 성별",
     *        required=true,
     *        in="path",
     *        @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function show(string $id, string $gender)
    {
        $validator = Validator::make([
            'id' => $id,
            'gender' => $gender,
        ], [
            'id' => 'required|exists:App\Models\SalonCategory,id',
            'gender' => ['required', Rule::in(['male', 'female'])],
        ]);

        try {
            $validated = $validator->validate();
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        return response()->json(['services' => SalonService::where('salon_category_id', $validated['id'])
            ->where('gender', $validated['gender'])->get()]);
    }

    /**
     * @OA\Post (
     *     path="/api/admin/salon-service",
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
     *                 @OA\Property (property="gender", type="string", description="성별", example="male"),
     *                 @OA\Property (property="price", type="string", description="가격", example="10000"),
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
                'gender' => 'required|string',
                'price' => 'required|string',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $salonService = SalonService::create([
            'salon_category_id' => $validated['category_id'],
            'service'           => $validated['service_name'],
            'price'             => $validated['price'],
            'gender'            => $validated['gender'],
        ]);

        return response()->json(['service' => $salonService]);
    }

    /**
     * @OA\Patch (
     *     path="/api/admin/salon-service",
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
     *             @OA\Property (property="gender", type="string", description="성별", example="male"),
     *             @OA\Property (property="price", type="string", description="가격", example="20000"),
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
                'gender' => 'string',
                'price' => 'string',
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

        unset($validated['service_id']);

        foreach ($validated as $key => $value) {
            $salonService->$key = $value;
        }

        if (!$salonService->save()) return response()->json(['error' => 'Failed to update service name'], 500);

        return response()->json(['success' => 'Updated successfully']);
    }

    /**
     * @OA\Delete (
     *     path="/api/admin/salon-service/{id}",
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
    public function destroy(string $id)
    {
        if (!SalonService::destroy($id)) {
            throw new DestroyException('Failed to destroy category');
        }
        return response()->json(['success' => 'Service deleted successfully']);
    }
}
