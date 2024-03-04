<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AfterService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminAfterServiceController extends Controller
{
    /**
     * @OA\Patch (
     *     path="/api/after-service/status/{id}",
     *     tags={"AS"},
     *     summary="상태 변경",
     *     description="AS의 상태를 완료로 변경",
     *     @OA\Parameter(
     *          name="id",
     *          description="상태를 변경할 AS의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function updateStatus(string $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:after_services,id|numeric'
        ], [
            'exists' => '해당하는 AS 요청이 존재하지 않습니다.'
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $afterService = AfterService::findOrFail($id);

        $afterService->status = true;

        if(!$afterService->save()) {
            return response()->json(['error' => 'AS 상태 변경에 실패하였습니다.'], 500);
        }

        return response()->json(['success' => 'AS가 완료되었습니다.']);
    }
}
