<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\NoticeImage;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Exception\MessagingException;

class NoticeController extends Controller
{
    private array $tagRules = ['admin', 'salon', 'restaurant', 'bus'];

    public function __construct(protected NotificationService $service)
    {
    }

    public function authorize($ability, $arguments = [Notice::class]): \Illuminate\Auth\Access\Response
    {
        return Parent::authorize($ability, $arguments);
    }

    /**
     * @OA\Get (
     *     path="/api/notice",
     *     tags={"공지사항"},
     *     summary="공지사항 검색",
     *     description="body로 보낸 값에 맞는 공지사항을 검색하는 기능입니다. 검색할 값을 보내지 않으면 전체 공지사항을 반환합니다.",
     *     @OA\RequestBody(
     *     description="페이지, 공지사항의 태그(admin, salon, restaurant, bus), 제목으로 검색합니다.",
     *     required=true,
     *         @OA\MediaType(
     *         mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="page", type="integer", description="페이지", example=1),
     *                 @OA\Property (property="tag", type="string", description="태그", example="Bus"),
     *                 @OA\Property (property="title", type="string", description="검색어", example="엄준식")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'tag' => [Rule::in($this->tagRules)],
                'title' => 'string',
                'page' => 'required|numeric',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $notices = Notice::query()->with('noticeImages');

        if(isset($validated['tag'])) {
            $notices = $notices->where('tag', $validated['tag']);
        }

        if(isset($validated['title'])) {
            $notices = $notices->where('title', 'LIKE', "%{$validated['title']}%");
        }

        $notices = $notices->orderByDesc('created_at')->paginate(8);

        return response()->json(['notices' => $notices]);
    }

    /**
     * @OA\Get (
     *     path="/api/notice/recent",
     *     tags={"공지사항"},
     *     summary="최근 3건의 공지사항",
     *     description="Query string과 일치하는 태그를 가진 최신 공지사항 3개를 반환합니다.",
     *     @OA\Parameter(
     *          name="tag",
     *          description="찾을 공지사항의 태그",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function recentIndex(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'tag' => [Rule::in($this->tagRules)],
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        // 태그에 해당하는 최신 게시글 3개만 필터링
        $notices = Notice::with('noticeImages')->where('tag', $validated['tag'])->latest()->take(3)->get();

        return response()->json(['notices' => $notices]);
    }

    /**
     * @OA\Get (
     *     path="/api/notice/recent/urgent",
     *     tags={"공지사항"},
     *     summary="최근 긴급 공지사항",
     *     description="bus, admis 태그를 가진 최신 긴급 공지사항 1개를 반환합니다.",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function recentUrgent(): \Illuminate\Http\JsonResponse
    {
        $notice = Notice::with('noticeImages')->latest()->first();
        return response()->json(['notices' => $notice]);
    }

    /**
     * @OA\Get (
     *     path="/api/notice/{id}",
     *     tags={"공지사항"},
     *     summary="공지사항 검색",
     *     description="아이디와 일치하는 공지사항을 반환합니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="찾을 공지사항의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $notice = Notice::with(['noticeImages', 'user'])->findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => __('messages.404')], 404);
        }

        return response()->json(['notice' => $notice]);
    }

    /**
     * @OA\Post (
     *     path="/api/notice",
     *     tags={"공지사항"},
     *     summary="공지사항 작성(관리자)",
     *     description="공지사항 작성에 사용됩니다. 사진은 이미지 파일 그대로 보내면 됩니다.",
     *     @OA\RequestBody(
     *         description="공지사항에 들어갈 내용 및 사진",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="title", type="string", description="제목", example="제목입니다."),
     *                 @OA\Property (property="content", type="string", description="내용", example="내용입니다."),
     *                 @OA\Property (property="tag", type="string", description="태그", example="행정"),
     *                 @OA\Property (property="images", type="array",
     *                     @OA\Items(
     *                          example="file",
     *                     ),
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     * @throws AuthorizationException
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied(__('auth.denied'));
        }

        // 태그를 가지고 있는 테이블을 생성해서 그에 맞는 테이블을 참조하게 하기
        try {
            $validated = $request->validate([
                'title'     => 'required|string',
                'content'   => 'required|string',
                'tag'       => ['required', Rule::in($this->tagRules)],
                'urgent'    => 'boolean',
                'images'    => 'array',
                'images.*'  => 'image|mimes:jpg,jpeg,png',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $validated['user_id'] = auth()->id();

        // 공지사항 생성
        $notice = Notice::create($validated);

        if(!$notice) return response()->json(['error' => __('messages.500')], 500);

        // 생성된 공지사항의 연관관계 메서드를 이용하여 이미지를 하나씩 저장
        if(isset($validated['images'])) {
            foreach ($validated['images'] as $image) {
                $url = env('AWS_CLOUDFRONT_URL').Storage::put('images', $image);

                $saveImage = $notice->noticeImages()->save(new NoticeImage(['image' => $url]));

                if(!$saveImage) return response()->json(['error' => __('messages.500')], 500);
            }
        }

        $users = User::where('push_enabled', true)->where('admin', false)->whereNot('fcm_token', null)->get();

        // 긴급 공지일 경우, 알림 전송
        if($validated['urgent'] && $users->isNotEmpty()) {
            // 마스터 및 행정 관리자의 토큰을 $tokens 배열에 담음
            $tokens = [];

            foreach ($users as $user) {
                $tokens[] = $user->fcm_token;
            }

            // 알림 전송
            try {
                $this->service->postNotificationMulticast('🚨긴급 공지🚨', $notice->title, $tokens, 'notice', $notice->id);
            } catch (MessagingException) {
                return response()->json(['error' => __('messages.500.push')], 500);
            }
        }

        return response()->json([
            'notice' => $notice,
            'images' => $notice->noticeImages(),
        ], 201);
    }

    /**
     * @OA\Patch (
     *     path="/api/notice/{id}",
     *     tags={"공지사항"},
     *     summary="공지사항 수정(관리자)",
     *     description="공지사항을 수정할 때 사용됩니다. 수정할 값만 request body에 전송하면 됩니다.
     *                  삭제할 이미지의 경우에는 delete_images 배열에 이미지의 아이디 값을 문자열로,
     *                  추가할 이미지는 기존 images 배열에 파일로 추가하면 됩니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="수정할 공지사항의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         description="수정할 글 내용",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="title", type="string", description="제목", example="제목입니다."),
     *                 @OA\Property (property="content", type="string", description="내용", example="내용입니다."),
     *                 @OA\Property (property="urgent", type="boolean", description="긴급", example="긴급 여부"),
     *                 @OA\Property (property="tag", type="string", description="태그", example="행정"),
     *                 @OA\Property (property="images", type="array",
     *                     @OA\Items(
     *                          example="string",
     *                     ),
     *                 ),
     *                @OA\Property (property="delete_images", type="array",
     *                     @OA\Items(
     *                          example="file",
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
    public function update(string $id, Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied(__('auth.denied'));
        }
        // 태그를 가지고 있는 테이블을 생성해서 그에 맞는 테이블을 참조하게 하기
        try {
            $validated = $request->validate([
                'title' => 'string',
                'content' => 'string',
                'tag' => [Rule::in($this->tagRules)],
                'urgent' => 'boolean',
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

        // 해당하는 공지사항이 있는지 검색
        try {
            $notice = Notice::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => __('messages.404')], 404);
        }

        // delete_images 배열을 확인하여, 해당하는 이미지의 아이디로 삭제
        if(isset($validated['delete_images'])) {
            foreach ($validated['delete_images'] as $deleteImage) {
                $imageURL = $notice->noticeImages()->where('id', $deleteImage)->get('image');
                $deleteDb = $notice->noticeImages()->where('id', $deleteImage)->delete();
                $fileName = basename($imageURL);
                $deleteS3 = Storage::delete('images/'.$fileName);
                if(!$deleteS3 || !$deleteDb) return response()->json(['error' => __('messages.500')], 500);
            }
            unset($validated['delete_images']);
        }

        if(isset($validated['images'])) {
            foreach ($validated['images'] as $image) {
                $url = env('AWS_CLOUDFRONT_URL').Storage::put('images', $image);
                $saveImage = $notice->noticeImages()->save(new NoticeImage(['image' => $url]));
                if(!$saveImage) return response()->json(['error' => __('messages.500')], 500);
            }
            unset($validated['images']);
        }

        // 키 값을 컬럼명과 일치시켜 들어온 값만 수정하도록 구현
        foreach($validated as $key => $value) {
            $notice->$key = $value;
        }

        if(!$notice->save()) return response()->json(['error' => __('messages.500')], 500);

        return response()->json(['notice' => $notice, 'images' => $notice->noticeImages()]);
    }

    /**
     * @OA\Delete (
     *     path="/api/notice/{id}",
     *     tags={"공지사항"},
     *     summary="공지사항 삭제(관리자)",
     *     description="공지사항 삭제 시 사용됩니다.",
     *     @OA\Parameter(
     *           name="id",
     *           description="삭제할 공지사항의 아이디",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied(__('auth.denied'));
        }
        $notice = Notice::destroy($id);

        if(!$notice) return response()->json(['error' => __('messages.500')], 500);

        return response()->json(['message' => __('messages.200')]);
    }
}
