<?php

namespace App\Http\Controllers\Salon;

use App\Exceptions\DestroyException;
use App\Http\Controllers\Controller;
use App\Models\SalonCategory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SalonCategoryController extends Controller
{
    public function authorize($ability, $arguments = [SalonCategory::class]): Response
    {
        return Parent::authorize($ability, $arguments);
    }
    /**
     * @OA\Get (
     *     path="/api/salon/category",
     *     tags={"미용실 - 카테고리"},
     *     summary="카테고리 목록",
     *     description="미용실 카테고리 목록을 불러올 때 사용합니다.",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(['categories' => SalonCategory::all()]);
    }

    /**
     * @OA\Post (
     *     path="/api/salon/category",
     *     tags={"미용실 - 카테고리"},
     *     summary="카테고리 생성(관리자)",
     *     description="미용실 카테고리 생성 시 사용합니다.",
     *     @OA\RequestBody(
     *         description="카테고리 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="category", type="string", description="카테고리 명", example="커트"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Created"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('salon');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        try {
            $validated = $request->validate([
                'category' => 'required|string'
            ]);
        } catch(ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $salonCategory = SalonCategory::create($validated);

        if(!$salonCategory) return response()->json(['error' => '카테고리 생성에 실패하였습니다.'], 500);

        return response()->json(['salon_category' => $salonCategory], 201);
    }

    /**
     * @OA\Patch (
     *     path="/api/salon/category",
     *     tags={"미용실 - 카테고리"},
     *     summary="카테고리 수정(관리자)",
     *     description="카테고리 이름 수정 시 사용합니다.",
     *     @OA\RequestBody(
     *         description="수정할 카테고리의 이름과 아이디",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *             @OA\Property (property="category_id", type="integer", description="카테고리 아이디", example=1),
     *             @OA\Property (property="category", type="string", description="카테고리명", example="엄준식"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $this->authorize('salon');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        try {
            $validated = $request->validate([
                'category_id' => 'required|numeric',
                'category' => 'required|string',
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

        $category->category = $validated['category'];

        if(!$category->save()) return response()->json(['error' => '카테고리명 수정에 실패하였습니다.'], 500);

        return response()->json(['message' => '카테고리명 수정에 성공하였습니다.']);

    }

    /**
     * @OA\Delete (
     *     path="/api/salon/category/{id}",
     *     tags={"미용실 - 카테고리"},
     *     summary="카테고리 삭제(관리자)",
     *     description="미용실 카테고리 삭제 시 사용합니다.",
     *      @OA\Parameter(
     *            name="id",
     *            description="삭제할 카테고리의 아이디",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="integer"),
     *        ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     * @throws DestroyException
     */
    public function destroy(String $id): JsonResponse
    {
        try {
            $this->authorize('salon');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        if (!SalonCategory::destroy($id)) {
            throw new DestroyException('카테고리 삭제에 실패하였습니다.');
        }

        return response()->json(['message'=>'카테고리 삭제에 성공하였습니다.']);
    }
}
