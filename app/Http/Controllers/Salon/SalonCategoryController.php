<?php

namespace App\Http\Controllers\Salon;

use App\Exceptions\DestroyException;
use App\Http\Controllers\Controller;
use App\Models\SalonCategory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SalonCategoryController extends Controller
{
    public function authorize($ability, $arguments = [SalonCategory::class])
    {
        return Parent::authorize($ability, $arguments);
    }
    /**
     * @OA\Get (
     *     path="/api/salon/category",
     *     tags={"미용실 - 카테고리"},
     *     summary="카테고리 목록(수정)",
     *     description="미용실 카테고리 목록",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['categories' => SalonCategory::all()]);
    }

    /**
     * @OA\Post (
     *     path="/api/salon/category",
     *     tags={"미용실 - 카테고리"},
     *     summary="카테고리 생성(관리자)(수정)",
     *     description="미용실 카테고리 생성",
     *     @OA\RequestBody(
     *         description="카테고리 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="category_name", type="string", description="카테고리 명", example="커트"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Created"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('store');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        try {
            $validated = $request->validate([
                'category_name' => 'required|string'
            ]);
        } catch(ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $salonCategory = SalonCategory::create([
            'category' => $validated['category_name'],
        ]);

        if(!$salonCategory) return response()->json(['Falied to create category'], 500);

        return response()->json(['salon_category' => $salonCategory], 201);
    }
    /**
     * @OA\Patch (
     *     path="/api/salon/category",
     *     tags={"미용실 - 카테고리"},
     *     summary="카테고리 수정(관리자)(수정)",
     *     description="카테고리 이름 수정",
     *     @OA\RequestBody(
     *         description="카테고리 수정을 위한 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *             @OA\Property (property="category_id", type="integer", description="카테고리 아이디", example=1),
     *             @OA\Property (property="category_name", type="string", description="카테고리 명", example="엄준식"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="404", description="Model Not Found Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('update');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        try {
            $validated = $request->validate([
                'category_id' => 'required|numeric',
                'category_name' => 'required|string',
            ]);
        } catch(ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        try {
            $category = SalonCategory::findOrFail($validated['category_id']);
        } catch(ModelNotFoundException $modelException) {
            $errorMessage = $modelException->getMessage();
            return response()->json(['error' => $errorMessage], 404);
        }

        $category->category = $validated['category_name'];

        if(!$category->save()) return response()->json(['error' => 'Failed to update category name'], 500);

        return response()->json(['message' => 'Update category name successfully']);

    }
    /**
     * @OA\Delete (
     *     path="/api/salon/category/{id}",
     *     tags={"미용실 - 카테고리"},
     *     summary="카테고리 삭제(관리자)(수정)",
     *     description="미용실 카테고리 삭제",
     *      @OA\Parameter(
     *            name="id",
     *            description="삭제할 카테고리의 아이디",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="integer"),
     *        ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function destroy(String $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('destroy');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        if (!SalonCategory::destroy($id)) {
            throw new DestroyException('Failed to destroy category');
        }

        return response()->json(['message'=>'Destroy category successfully']);
    }
}
