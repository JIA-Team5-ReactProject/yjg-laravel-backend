<?php

namespace App\Http\Controllers\AfterService;

use App\Http\Controllers\Controller;
use App\Models\AfterService;
use App\Models\AfterServiceComment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AfterServiceCommentController extends Controller
{
    public function authorize($ability, $arguments = [AfterServiceComment::class])
    {
        return Parent::authorize($ability, $arguments);
    }
    /**
     * @OA\Post (
     *     path="/api/after-service/{id}/comment",
     *     tags={"AS 댓글"},
     *     summary="작성(관리자)",
     *     description="AS에 대한 댓글을 작성할 때 사용하는 기능입니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="댓글을 작성할 AS의 아이디",
     *          required=true,
     *          in="path",
     *     @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         description="작성할 댓글의 내용",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="comment", type="string", description="댓글의 내용", example="2월 30일 12시에 방문하겠습니다"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function store(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('store');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        try {
            $validated = $request->validate([
                'comment' => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        try {
            $afterService = AfterService::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
        }

        // 위에서 찾은 모델의 연관관계를 이용하여 새 댓글을 작성
        $comment = $afterService->afterServiceComments()->create([
            'admin_id' => auth('admins')->id(),
            'comment'  => $validated['comment'],
        ]);

        // TODO: if문 제거하도록 수정
        if(!$comment) return response()->json(['error' => '댓글 작성에 실패하였습니다.'], 500);

        return response()->json(['message' => '성공적으로 댓글이 작성되었습니다.'], 201);
    }

    /**
     * @OA\Patch (
     *     path="/api/after-service/comment/{id}",
     *     tags={"AS 댓글"},
     *     summary="수정(관리자)",
     *     description="AS의 댓글을 수정할 때 사용하는 기능입니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="수정할 댓글의 아이디",
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
     *                 @OA\Property (property="comment", type="string", description="수정할 댓글의 내용", example="3월 32일 24시에 방문하겠습니다."),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('update');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        try {
            $validated = $request->validate([
                'comment' => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        try {
            $asComment = AfterServiceComment::findOrFail($id);
        } catch (ModelNotFoundException $modelException) {
            return response()->json(['error' => '해당하는 댓글이 존재하지 않습니다.'], 404);
        }

        // TODO: if문 제거
        // 위 모델의 연관관계를 이용하여 수정
        $asComment->comment = $validated['comment'];

        if(!$asComment->save()) return response()->json(['error' => '댓글을 수정하는데 실패하였습니다.'], 500);

        return response()->json(['message' => '댓글이 성공적으로 수정되었습니다.']);
    }

    /**
     * @OA\Delete (
     *     path="/api/after-service/comment/{id}",
     *     tags={"AS 댓글"},
     *     summary="삭제(관리자)",
     *     description="아이디에 해당하는 AS의 댓글 삭제할 때 사용합니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="삭제할 댓글의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('destroy');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        $validator = Validator::make([$id], [
            'id' => 'required|exists:after_service_comments,id|numeric'
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $asComment = AfterServiceComment::findOrFail($id);

        if(!$asComment->delete()) return response()->json(['error' => '댓글 삭제에 실패하였습니다.'], 500);

        return response()->json(['message' => '댓글이 삭제되었습니다.']);
    }
}
