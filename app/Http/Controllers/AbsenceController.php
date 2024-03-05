<?php

namespace App\Http\Controllers;

use App\Models\AbsenceList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AbsenceController extends Controller
{
    /**
     * @OA\Get (
     *     path="/api/absence",
     *     tags={"외출/외박"},
     *     summary="전체 목록",
     *     description="외출/외박의 전체 목록 + 검색",
     *     @OA\Parameter(
     *          name="type",
     *          description="타입(외박:sleep 혹은 외출:go)",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *           name="page",
     *          description="페이지",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *          name="date",
     *          description="조회할 날짜",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="date"),
     *     ),
     *     @OA\Parameter(
     *           name="user_name",
     *           description="사용자 이름",
     *           required=false,
     *           in="query",
     *           @OA\Schema(type="string"),
     *      ),

     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        // 관리자
        // 전체 외박, 외출 목록 (외박 혹은 외출, 날짜, 유저로 검색 및 페이지네이션)
        try {
            $validated = $request->validate([
                'type' => ['required','string', Rule::in(['sleep', 'go'])],
                'date' => 'required|date-format:Y-m-d',
                'user_name'  => 'string',
            ]);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $absenceLists = AbsenceList::with('user')->where('type', $validated['type'])->where('start_date', $validated['date']);

        if (isset($validated['user_name'])) {
            $absenceLists = $absenceLists->whereHas('user', function (Builder $query) use($validated) {
                $query->where('name', $validated['user_name']);
            });
        }

        $stayOutLists = $absenceLists->paginate(8);

        foreach ($absenceLists as $absenceList) {
            $userName = $absenceList->user['name'];
            $absenceList['user_name'] = $userName;
            unset($absenceList['user']);
        }

        return response()->json(['absence_lists' => $absenceLists]);
    }

    /**
     * @OA\Get (
     *     path="/api/absence/user",
     *     tags={"외출/외박"},
     *     summary="유저의 외출/외박 목록",
     *     description="현재 유저의 외출/외박 목록",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function userIndex(Request $request)
    {
        // 유저
        // 유저의 외박, 외출 목록 (페이지네이션)
        $userId = $request->user()->id;

        $absenceList = AbsenceList::where('user_id', $userId)->paginate(8);

        return response()->json(['absence_lists' => $absenceList]);
    }

    /**
     * @OA\Post (
     *     path="/api/absence",
     *     tags={"외출/외박"},
     *     summary="외출/외박 신청",
     *     description="유저의 외출/외박 신청",
     *     @OA\RequestBody(
     *         description="외출/외박 일자 및 사유",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="start_date", type="date", description="출발일", example="2024-01-01"),
     *                 @OA\Property (property="end_date", type="date", description="복귀일(외출의 경우에는 복귀일과 동일하게)", example="2024-01-02"),
     *                 @OA\Property (property="content", type="string", description="사유", example="병원 진료로 인한 외박"),
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
        // 유저
        // 외박, 외출 신청
        try {
            $validated = $request->validate([
                'start_date' => 'required|date_format:Y-m-d',
                'end_date'   => 'required|date_format:Y-m-d',
                'content'    => 'required|string',
                'type'       => ['required', Rule::in(['go', 'sleep'])],
            ]);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $validated['user_id'] = $request->user()->id;

        $absence = AbsenceList::create($validated);

        return response()->json(['absence' => $absence], 201);
    }

    /**
     * @OA\Get (
     *     path="/api/absence/{id}",
     *     tags={"외출/외박"},
     *     summary="요약",
     *     description="설명",
     *     @OA\Parameter(
     *          name="파라미터명",
     *          description="설명",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="타입"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function show(string $id)
    {
        // 공용
        // 특정 아이디를 가진 외박, 외출 목록
        try {
            $stayOut = AbsenceList::findOrFail($id);
        } catch (ModelNotFoundException $modelException) {
            return response()->json(['error' => '해당하는 외출/외박 기록이 없습니다.'], 404);
        }

        return response()->json(['stay_out' => $stayOut]);
    }

    /**
     * @OA\Patch (
     *     path="/api/absence/{id}",
     *     tags={"외출/외박"},
     *     summary="수정",
     *     description="외출/외박 내용 수정",
     *     @OA\Parameter(
     *          name="id",
     *          description="수정할 외출/외박 기록의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         description="수정할 날짜 혹은 내용",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="start_date", type="date", description="출발일", example="2024-01-01"),
     *                 @OA\Property (property="end_date", type="date", description="복귀일(외출의 경우에는 복귀일과 동일하게)", example="2024-01-02"),
     *                 @OA\Property (property="content", type="string", description="사유", example="병원 진료로 인한 외박"),
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
        // 유저
        // 외박, 외출 수정 (내용)
        try {
            $validated = $request->validate([
                'start_date' => 'date_format:Y-m-d',
                'end_date'   => 'date_format:Y-m-d',
                'content'    => 'string',
                'type'       => ['string', Rule::in(['go', 'sleep'])],
            ]);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        try {
            $absenceList = AbsenceList::findOrFail($id);
        } catch (ModelNotFoundException $modelException) {
            return response()->json(['error' => '해당하는 외출/외박 기록이 없습니다.'], 404);
        }

        foreach ($validated as $key => $value) {
            $absenceList->$key = $value;
        }

        $absenceList->start_date = $validated['start_date'];

        $absenceList->save();

        if(!$absenceList->save()) return response()->json(['error' => '외출/외박 내용 수정에 실패하였습니다.'], 500);

        return response()->json(['success' => '외출/외박 내용이 수정되었습니다.', 'stay_out' => $absenceList]);
    }

    /**
     * @OA\Patch (
     *     path="/api/absence/reject/{id}",
     *     tags={"외출/외박"},
     *     summary="수정",
     *     description="외출/외박 내용 수정",
     *     @OA\Parameter(
     *          name="id",
     *          description="수정할 외출/외박 기록의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function reject(string $id)
    {
        // 관리자
        // 외박, 외출 거절 (status 업데이트)
        try {
            $stayOut = AbsenceList::findOrFail($id);
        } catch (ModelNotFoundException $modelException) {
            return response()->json(['error' => '해당하는 외출/외박 기록이 없습니다.'], 404);
        }

        $stayOut->status = false;

        if(!$stayOut->save()) return response()->json(['error' => '외출/외박 상태 수정에 실패하였습니다.'], 500);

        return response()->json(['success' => '외출/외박 상태 수정에 성공하였습니다.']);
    }

    /**
     * @OA\Delete (
     *     path="/api/absence/{id}",
     *     tags={"외출/외박"},
     *     summary="삭제(취소)",
     *     description="외출/외박 기록 삭제(취소)",
     *     @OA\Parameter(
     *          name="파라미터명",
     *          description="설명",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="타입"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function destroy(string $id)
    {
        // 유저
        // 외박, 외출 취소(삭제)
        try {
            $stayOut = AbsenceList::findOrFail($id);
        } catch (ModelNotFoundException $modelException) {
            return response()->json(['error' => '해당하는 외출/외박 기록이 없습니다.'], 404);
        }

        if(!$stayOut->delete()) return response()->json(['error' => '외출/외박 기록 삭제에 실패하였습니다.'], 500);

        return response()->json(['success' => '외출/외박 기록이 삭제되었습니다.']);
    }
}
