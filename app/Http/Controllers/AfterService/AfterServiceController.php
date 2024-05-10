<?php

namespace App\Http\Controllers\AfterService;

use App\Http\Controllers\Controller;
use App\Models\AfterService;
use App\Models\AfterServiceImage;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Exception\MessagingException;

class AfterServiceController extends Controller
{
    protected array $relations = ['user:id,name,created_at,phone_number', 'afterServiceComments', 'afterServiceImages'];

    public function __construct(protected NotificationService $service)
    {
    }

    public function authorize($ability, $arguments = [AfterService::class])
    {
        return Parent::authorize($ability, $arguments);
    }
    /**
     * @OA\Get (
     *     path="/api/after-service",
     *     tags={"AS"},
     *     summary="AS 검색",
     *     description="Query string의 조건에 맞게 AS 검색",
     *     @OA\Parameter(
     *          name="name",
     *          description="신청인",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *          name="status",
     *          description="상태 (true or false)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="boolean"),
     *     ),
     *     @OA\Parameter(
     *          name="page",
     *          description="현재 페이지",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="boolean"),
     *      ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Error"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'string',
                'status' => 'boolean',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        // 클래스의 정의한 연관관계와 함께 불러옴
        // 유저 정보와 사진만
        $afterServices = AfterService::query()->with([$this->relations[0], $this->relations[1]]);

        if(isset($request['status'])) {
            $afterServices = $afterServices->where('status', $validated['status']);
        }

        if(isset($request['name'])) {
            $afterServices = $afterServices->whereHas('user', function ($query) use ($validated) {
                $query->where('name', $validated['name']);
            });
        }

        // 최신순으로 정렬하여 페이지네이션
        $afterServices = $afterServices->latest()->paginate(8);

        // 프론트엔드에서 필요한 정보만 반환하도록 구현
        foreach ($afterServices as $afterService) {
            $userName = $afterService->user['name'];
            $afterService->user_name = $userName;
            unset($afterService->user);
        }

        return response()->json(['after_services' => $afterServices]);
    }

    /**
     * @OA\Get (
     *     path="/api/after-service/user",
     *     tags={"AS"},
     *     summary="유저의 AS 검색",
     *     description="유저 아이디와 일치하는 AS 검색",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function userIndex(): JsonResponse
    {
        $userId = auth('users')->id();

        return response()->json([
            'after_services' => AfterService::where('user_id', $userId)->orderBy('visit_date')->get()
        ]);
    }

    /**
     * @OA\Post (
     *     path="/api/after-service",
     *     tags={"AS"},
     *     summary="AS 작성",
     *     description="새로운 AS를 신청할 때 사용합니다.",
     *     @OA\RequestBody(
     *         description="생성할 AS의 내용",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema (
     *                  @OA\Property (property="title", type="string", description="제목", example="제목입니다."),
     *                  @OA\Property (property="content", type="string", description="내용", example="내용입니다."),
     *                  @OA\Property (property="visit_place", type="string", description="방문 장소", example="101호"),
     *                  @OA\Property (property="visit_date", type="date", description="희망 방문 일자", example="2024-01-01"),
     *                  @OA\Property (property="images", type="array",
     *                      @OA\Items(
     *                           example="file",
     *                      ),
     *                  ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:50',
                'content' => 'required|string',
                'visit_place' => 'required|string',
                'visit_date' => 'date',
                'images' => 'array',
                'images.*' => 'file',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $validated['user_id'] = auth('users')->id();

        // AS 신청
        $afterService = AfterService::create($validated);

        if(!$afterService->save()) {
            return response()->json(['error' => 'A/S 신청에 실패하였습니다.'], 500);
        }

        // 연관관계를 이용하여 이미지 저장
        if(isset($validated['images'])) {
            foreach ($validated['images'] as $image) {
                $url = env('AWS_CLOUDFRONT_URL').Storage::put('images', $image);

                $saveImage = $afterService->afterServiceImages()->save(new AfterServiceImage(['image' => $url]));

                if(!$saveImage) return response()->json(['A/S 관련 이미지 저장에 실패하였습니다.'], 500);
            }
        }

        // 마스터 및 행정 관리자의 토큰을 $tokens 배열에 담음
        $tokens = [];

        $users = User::where('push_enabled', true)->where('admin', true)->whereHas('privileges', function (Builder $query) {
            $query->whereIn('privilege', ['master', 'admin']);
        })->whereNot('fcm_token', null)->get();

        // 유저 컬렉션이 비지 않았을 때 수행
        if($users->isNotEmpty()) {
            foreach ($users as $user) {
                $tokens[] = $user->fcm_token;
            }

            $notificationBody = '신청 내용: '.$validated['title'];

            // 알림 전송
            try {
                $this->service->postNotificationMulticast('새로운 AS 신청이 등록되었습니다.', $notificationBody, $tokens, 'as', $afterService->id);
            } catch (MessagingException $e) {
                return response()->json(['error' => '알림 전송에 실패하였습니다.'], 500);
            }
        }

        return response()->json([
            'afterService' => $afterService,
            'images' => $afterService->afterServiceImages(),
        ], 201);
    }

    /**
     * @OA\Get (
     *     path="/api/after-service/{id}",
     *     tags={"AS"},
     *     summary="특정 AS 불러오기",
     *     description="아이디에 해당하는 AS 정보를 받아옵니다.",
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
            $afterService = AfterService::with($this->relations)->findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => '해당하는 AS 정보가 없습니다.'], 404);
        }

        return response()->json(['afterService' => $afterService]);
    }

    /**
     * @OA\Patch (
     *     path="/api/after-service/status/{id}",
     *     tags={"AS"},
     *     summary="상태 변경(관리자)",
     *     description="AS의 상태를 완료로 변경할 때 사용합니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="상태를 변경할 AS의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function updateStatus(string $id): JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:after_services,id|numeric'
        ]);

        try {
            $validator->validate();
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

        $afterService->status = true;

        if(!$afterService->save()) {
            return response()->json(['error' => 'AS 상태 변경에 실패하였습니다.'], 500);
        }

        $token = $afterService->user['fcm_token'];

        if($afterService->user['push_enabled']) {
            // 알림 전송
            try {
                $this->service->postNotification('AS가 완료되었습니다.', 'AS 내용: '.$afterService->title, $token, 'as', $afterService->id);
            } catch (MessagingException) {
                return response()->json(['error' => '알림 전송에 실패하였습니다.'], 500);
            }
        }

        return response()->json(['message' => 'AS가 완료되었습니다.']);
    }

    /**
     * @OA\Patch (
     *     path="/api/after-service/{id}",
     *     tags={"AS"},
     *     summary="수정",
     *     description="아이디에 해당하는 AS를 수정할 때 사용합니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         description="수정할 AS 글 내용",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="title", type="string", description="제목", example="제목입니다."),
     *                 @OA\Property (property="content", type="string", description="내용", example="내용입니다."),
     *                 @OA\Property (property="visit_place", type="string", description="방문 장소", example="101호"),
     *                 @OA\Property (property="visit_date", type="date", description="희망 방문 일자", example="2024-01-01"),
     *                 @OA\Property (property="images", type="array",
     *                     @OA\Items(
     *                          example="file",
     *                     ),
     *                 ),
     *                @OA\Property (property="delete_images", type="array",
     *                     @OA\Items(
     *                          example="numeric",
     *                     ),
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'string|max:50',
                'content' => 'string',
                'visit_place' => 'string',
                'visit_date' => 'date',
                'images' => 'array',
                'images.*' => 'image|mimes:jpg,jpeg,png',
                'delete_images' => 'array',
                'delete_images.*' => 'numeric',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        try {
            $afterService = AfterService::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
        }

        // TODO: 효율적으로 삭제 가능한지 좀 생각해봐야 함
        // delete_images 배열을 확인하여, 해당하는 이미지의 아이디로 삭제
        if(isset($validated['delete_images'])) {
            foreach ($validated['delete_images'] as $deleteImage) {
                $imageURL = $afterService->afterServiceImages()->where('id', $deleteImage)->get('image');
                $deleteDb = $afterService->afterServiceImages()->where('id', $deleteImage)->delete();
                $deleteS3 = Storage::delete('images/'.$imageURL);
                if(!$deleteS3 || !$deleteDb) return response()->json(['error' => '이미지 삭제에 실패하였습니다.'], 500);
            }
            unset($validated['delete_images']);
        }

        if(isset($validated['images'])) {
            foreach ($validated['images'] as $image) {
                $url = env('AWS_CLOUDFRONT_URL').Storage::put('images', $image);
                $saveImage = $afterService->afterServiceImages()->save(new AfterServiceImage(['image' => $url]));
                if(!$saveImage) return response()->json(['이미지를 저장하는데 실패하였습니다.'], 500);
            }
            unset($validated['images']);
        }

        foreach($validated as $key => $value) {
            $afterService->$key = $value;
        }

        if(!$afterService->save()) return response()->json(['error' => 'AS 요청을 수정하는데 실패하였습니다.'], 500);

        return response()->json(['afterService' => $afterService, 'images' => $afterService->afterServiceImages()]);
    }

    /**
     * @OA\Delete (
     *     path="/api/after-service/{id}",
     *     tags={"AS"},
     *     summary="삭제",
     *     description="아이디에 해당하는 AS를 삭제",
     *     @OA\Parameter(
     *          name="id",
     *          description="아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        if(!AfterService::destroy($id)) {
            return response()->json(['error' => 'AS 요청을 삭제하는데 실패하였습니다.'], 500);
        }

        return response()->json(['message' => 'AS 요청이 성공적으로 삭제되었습니다']);
    }
}
