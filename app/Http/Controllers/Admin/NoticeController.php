<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Notice;
use App\Models\NoticeImage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NoticeController extends Controller
{
    private array $tagRules = ['admin', 'salon', 'restaurant', 'bus'];

    /**
     * @OA\Get (
     *     path="/api/notice",
     *     tags={"공지사항"},
     *     summary="공지사항 검색",
     *     description="파라미터 값에 맞는 공지사항을 반환",
     *     @OA\RequestBody(
     *     description="검색 파라미터가 있을 경우 반환, 없을 경우 전체 반환",
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
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function index(Request $request)
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
     *     path="/api/notice/{id}",
     *     tags={"공지사항"},
     *     summary="공지사항 검색",
     *     description="파라미터 값에 맞는 공지사항을 반환",
     *     @OA\Parameter(
     *          name="id",
     *          description="찾을 공지사항의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function show(string $id)
    {
        try {
            $notice = Notice::with(['noticeImages', 'admin'])->findOrFail($id);
        } catch (ModelNotFoundException $modelException) {
            $errorMessage = $modelException->getMessage();
            return response()->json(['error'=>$errorMessage], 404);
        }

        return response()->json(['notice' => $notice]);
    }

    /**
     * @OA\Post (
     *     path="/api/notice",
     *     tags={"공지사항"},
     *     summary="공지사항 작성(관리자)(수정)",
     *     description="공지사항 작성",
     *     @OA\RequestBody(
     *         description="글 내용",
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
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function store(Request $request)
    {
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

        $adminId = $request->user()->id;

        try {
            Admin::findOrFail($adminId);
        } catch (ModelNotFoundException $modelException) {
            return response()->json(['error' => '해당하는 관리자가 없습니다.'], 404);
        }

        $notice = new Notice();
        $notice->admin_id = $adminId;
        $notice->title = $validated['title'];
        $notice->content = $validated['content'];
        $notice->tag = $validated['tag'];
        if(isset($validated['urgent'])) {
            $notice->urgent = $validated['urgent'];
        }

        $notice->save();

        if(!$notice) return response()->json(['error' => 'Failed to create notice'], 500);

        if(isset($validated['images'])) {
            foreach ($validated['images'] as $image) {
                $url = env('AWS_CLOUDFRONT_URL').Storage::put('images', $image);

                $saveImage = $notice->noticeImages()->save(new NoticeImage(['image' => $url]));

                if(!$saveImage) return response()->json(['Failed to save image'], 500);
            }
        }


        return response()->json(['notice' => $notice, 'images' => $notice->noticeImages()], 201);
    }

    /**
     * @OA\Patch (
     *     path="/api/notice/{id}",
     *     tags={"공지사항"},
     *     summary="공지사항 수정(관리자)(수정)",
     *     description="공지사항 수정",
     *     @OA\Parameter(
     *          name="id",
     *          description="찾을 공지사항의 아이디",
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
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function update(string $id, Request $request)
    {
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

        try {
            $notice = Notice::findOrFail($id);
        } catch (ModelNotFoundException $modelException) {
            return response()->json(['error' => 'id에 해당하는 게시글이 없습니다.'], 404);
        }

        if(isset($validated['delete_images'])) {
            foreach ($validated['delete_images'] as $deleteImage) {
                try {
                    //TODO: 연관관계 메서드 이용하여 수정하기
                    $noticeImage = NoticeImage::findOrFail($deleteImage);
                } catch (ModelNotFoundException $modelException) {
                    return response()->json(['error' => '해당하는 이미지가 존재하지 않습니다.'], 404);
                }
                $deleteDb = $noticeImage->delete();
                $fileName = basename($noticeImage->image);
                $deleteS3 = Storage::delete('images/'.$fileName);
                if(!$deleteS3 || !$deleteDb) return response()->json(['error' => '이미지 삭제에 실패하였습니다.'], 500);
            }
            unset($validated['delete_images']);
        }

        if(isset($validated['images'])) {
            foreach ($validated['images'] as $image) {
                $url = env('AWS_CLOUDFRONT_URL').Storage::put('images', $image);
                $saveImage = $notice->noticeImages()->save(new NoticeImage(['image' => $url]));
                if(!$saveImage) return response()->json(['Failed to save image'], 500);
            }
            unset($validated['images']);
        }

        foreach($validated as $key => $value) {
            $notice->$key = $value;
        }

        if(!$notice->save()) return response()->json(['error' => 'Failed to update notice'], 500);

        return response()->json(['notice' => $notice, 'images' => $notice->noticeImages()]);
    }

    /**
     * @OA\Delete (
     *     path="/api/notice/{id}",
     *     tags={"공지사항"},
     *     summary="공지사항 삭제(관리자)(수정)",
     *     description="공지사항 삭제",
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
    public function destroy(string $id)
    {
        $notice = Notice::destroy($id);

        if(!$notice) return response()->json(['error' => '삭제에 실패하였습니다.'], 500);

        return response()->json(['success' => '공지사항이 성공적으로 삭제되었습니다.']);
    }
}
