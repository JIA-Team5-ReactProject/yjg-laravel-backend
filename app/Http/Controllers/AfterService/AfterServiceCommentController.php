<?php

namespace App\Http\Controllers\AfterService;

use App\Http\Controllers\Controller;
use App\Models\AfterService;
use App\Models\AfterServiceComment;
use App\Services\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Exception\MessagingException;

class AfterServiceCommentController extends Controller
{
    public function __construct(protected NotificationService $service)
    {
    }

    public function authorize($ability, $arguments = [AfterServiceComment::class]): Response
    {
        return Parent::authorize($ability, $arguments);
    }

    /**
     * @OA\Get (
     *     path="/api/after-service/{id}/comment",
     *     tags={"AS 댓글"},
     *     summary="특정 AS의 댓글 불러오기",
     *     description="아이디에 해당하는 AS의 댓글을 받아옵니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="as의 id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $afterService = AfterService::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => __('messages.404')], 404);
        }

        return response()->json(['after_service_comments' => $afterService->afterServiceComments()->get()]);

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
    public function store(Request $request, string $id): JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied(__('auth.denied'));
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
            return response()->json(['error' => __('messages.404')], 404);
        }

        // 위에서 찾은 모델의 연관관계를 이용하여 새 댓글을 작성
        $comment = $afterService->afterServiceComments()->create([
            'user_id' => auth()->id(),
            'comment'  => $validated['comment'],
        ]);


        if(!$comment) return response()->json(['error' => __('messages.500')], 500);

        // 알림 전송
        if($afterService->user['push_enabled']) {
            $token = $afterService->user['fcm_token'];

            try {
                $this->service->postNotification(__('notification.as_comment'), $validated['comment'], $token, 'as', $afterService->id);
            } catch (MessagingException) {
                return response()->json(['error' => __('messages.500.push')], 500);
            }
        }

        return response()->json(['comment' => $comment], 201);
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
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied(__('auth.denied'));
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
        } catch (ModelNotFoundException) {
            return response()->json(['error' => __('messages.404')], 404);
        }

        // 위 모델의 연관관계를 이용하여 수정
        $asComment->comment = $validated['comment'];

        if(!$asComment->save()) return response()->json(['error' => __('messages.500')], 500);

        return response()->json(['message' => __('messages.200')]);
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
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied(__('auth.denied'));
        }

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:after_service_comments,id|numeric'
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        try {
            $asComment = AfterServiceComment::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => __('messages.404')], 404);
        }

        if(!$asComment->delete()) return response()->json(['error' => __('messages.500')], 500);

        return response()->json(['message' => __('messages.200')]);
    }
}
