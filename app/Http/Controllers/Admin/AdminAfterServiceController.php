<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AfterService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminAfterServiceController extends Controller
{
    /**
     * @OA\Patch (
     *     path="/api/",
     *     tags={"태그"},
     *     summary="요약",
     *     description="설명",
     *     @OA\Parameter(
     *          name="id",
     *          description="상태를 변경할 AS의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         description="설명",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="속성명", type="타입", description="설명", example="예시"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function updateStatus(Request $request, string $id)
    {
        $validator = Validator::make([$id], [
            'id' => 'required|exists:after_services,id'
        ]);

        try {
            $validated = $validator->validate();
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        try {
            $afterService = AfterService::findOrFail($validated['id']);
        } catch (ModelNotFoundException $modelException) {
            return response()->json(['error' => '해당하는 AS 정보가 없습니다.'], 404);
        }

        $afterService->status = true;

        if(!$afterService->save()) {
            return response()->json(['error' => 'AS 상태 변경에 실패하였습니다.'], 500);
        }

        return response()->json(['success' => 'AS가 완료되었습니다.']);
    }
}
