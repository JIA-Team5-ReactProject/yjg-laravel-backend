<?php

namespace App\Http\Controllers\AfterService;

use App\Http\Controllers\Controller;
use App\Models\AfterService;
use App\Models\AfterServiceImage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AfterServiceController extends Controller
{
    // 유저의 AS 목록 구현하기
    /**
     * @OA\Get (
     *     path="/api/after-service",
     *     tags={"AS"},
     *     summary="AS 검색",
     *     description="조건에 맞게 AS 검색",
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
    public function index(Request $request)
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
        $afterServices = AfterService::query();

        if(isset($request['status'])) {
            $afterServices = $afterServices->where('status', $validated['status']);
        }

        if(isset($request['name'])) {
            $afterServices = $afterServices->whereHas('user', function ($query) use ($validated) {
                $query->where('name', $validated['name']);
            });
        }
        $afterServices = $afterServices->paginate(8);

        foreach ($afterServices as $afterService) {
            $userName = $afterService->user['name'];
            $afterService->user_name = $userName;
            unset($afterService->user);
        }

        return response()->json(['after_services' => $afterServices]);
    }

    /**
     * @OA\Post (
     *     path="/api/after-service",
     *     tags={"AS"},
     *     summary="AS 작성",
     *     description="새로운 AS를 생성함",
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
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:50',
                'content' => 'required|string',
                'visit_place' => 'required|string',
                'visit_date' => 'date',
                'images' => 'array',
                'images.*' => 'image|mimes:jpg,jpeg,png',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $afterService = new AfterService();

        $afterService->user_id = auth('users')->id();
        $afterService->title = $validated['title'];
        $afterService->content = $validated['content'];
        $afterService->visit_place = $validated['visit_place'];
        if(isset($validated['visit_date'])) $afterService->visit_date = $validated['visit_date'];

        if(!$afterService->save()) {
            return response()->json(['error' => 'Failed to save after service'], 500);
        }

        if(isset($validated['images'])) {
            foreach ($validated['images'] as $image) {
                $url = env('AWS_CLOUDFRONT_URL').Storage::put('images', $image);

                $saveImage = $afterService->afterServiceImages()->save(new AfterServiceImage(['image' => $url]));

                if(!$saveImage) return response()->json(['Failed to save image'], 500);
            }
        }

        return response()->json(['afterService' => $afterService, 'images' => $afterService->afterServiceImages()], 201);
    }

    /**
     * @OA\Get (
     *     path="/api/after-service/{id}",
     *     tags={"AS"},
     *     summary="AS 읽기",
     *     description="아이디에 해당하는 AS 정보 받아옴",
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
    public function show(string $id)
    {
        try {
            $afterService = AfterService::with(['afterServiceImages', 'afterServiceComments', 'user'])->findOrFail($id);
        } catch (ModelNotFoundException $modelException) {
            return response()->json(['error' => '해당하는 AS 정보가 없습니다.'], 404);
        }

        return response()->json(['afterService' => $afterService]);
    }

    /**
     * @OA\Patch (
     *     path="/api/after-service/status/{id}",
     *     tags={"AS"},
     *     summary="상태 변경(관리자)",
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

    /**
     * @OA\Patch (
     *     path="/api/after-service/{id}",
     *     tags={"AS"},
     *     summary="수정",
     *     description="아이디에 해당하는 AS를 수정",
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
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function update(Request $request, string $id)
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
        } catch (ModelNotFoundException $modelException) {
            return response()->json(['error' => 'id에 해당하는 AS 이력이 없습니다.'], 404);
        }

        if(isset($validated['delete_images'])) {
            foreach ($validated['delete_images'] as $deleteImage) {
                try {
                    //TODO: 연관관계 메서드 이용하여 수정하기
                    $asImage = AfterServiceImage::findOrFail($deleteImage);
                } catch (ModelNotFoundException $modelException) {
                    return response()->json(['error' => '해당하는 이미지가 존재하지 않습니다.'], 404);
                }
                $deleteDb = $asImage->delete();
                $fileName = basename($asImage->image);
                $deleteS3 = Storage::delete('images/'.$fileName);
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
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function destroy(string $id)
    {
        if(AfterService::destroy($id)) {
            return response()->json(['error' => 'AS 요청을 삭제하는데 실패하였습니다.'], 500);
        }

        return response()->json(['success' => 'AS 요청이 성공적으로 삭제되었습니다']);
    }
}
