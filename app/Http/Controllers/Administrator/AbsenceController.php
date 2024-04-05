<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\AbsenceList;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AbsenceController extends Controller
{
    public function authorize($ability, $arguments = [AbsenceList::class])
    {
        return Parent::authorize($ability, $arguments);
    }
    /**
     * @OA\Get (
     *     path="/api/absence/count",
     *     tags={"외출/외박"},
     *     summary="외출/외박 신청 인원 수",
     *     description="Query string으로 받은 날짜에 외출(go)과 외박(sleep)을 신청한 인원 수",
     *     @OA\Parameter(
     *          name="date",
     *          description="조회할 날짜",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="date"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function absenceCount(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied();
        }
        $validated = $request->validate([
            'date' => 'required|date-format:Y-m-d',
        ]);

        $sleep = AbsenceList::where('type', 'sleep')->where('status', true)
            ->whereDate('created_at', $validated['date'])->count();
        $go    = AbsenceList::where('type', 'go')->where('status', true)
            ->whereDate('created_at', $validated['date'])->count();

        return response()->json(['sleep_count' => $sleep, 'go_count' => $go]);
    }

    /**
     * @OA\Get (
     *     path="/api/absence",
     *     tags={"외출/외박"},
     *     summary="전체 목록",
     *     description="외출/외박의 전체 목록을 반환함. type과 date는 필수 값이며,
     *                  추가적으로 user_name을 입력하면 사용자 이름과 일치하는 결과를 가져옴",
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
     *          description="조회할 날짜(예약 생성일 기준)",
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
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
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

        // 쿼리스트링으로 받은 값과 DB의 값을 비교해서 일치하는 데이터를 가져옴
        $absenceLists = AbsenceList::with('user')->where('type', $validated['type'])
            ->whereDate('created_at', $validated['date'])->where('status', true);

        // 사용자 이름은 옵션 필수가 아니기에 존재하면 해당하는 값을 필터링
        if (isset($validated['user_name'])) {
            $absenceLists = $absenceLists->whereHas('user', function (Builder $query) use($validated) {
                $query->where('name', $validated['user_name']);
            });
        }

        // 최신순으로 정렬하여 8개씩 페이지네이션
        $absenceLists = $absenceLists->orderByDesc('created_at')->paginate(8);

        // 프론트에서 필요한 사용자의 이름과 아이디만 user 객체에서 빼서 별도로 저장
        foreach ($absenceLists as $absenceList) {
            $userName = $absenceList->user['name'];
            $studentId = $absenceList->user['student_id'];
            $absenceList['user_name'] = $userName;
            $absenceList['student_id'] = $studentId;
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
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function userIndex(Request $request): \Illuminate\Http\JsonResponse
    {
        $userId = auth('users')->id();

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
     *         description="외출/외박 시작 및 종료일자, 사유, 타입을 입력받습니다.",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="start_date", type="date", description="출발일", example="2024-01-01"),
     *                 @OA\Property (property="end_date", type="date", description="복귀일(외출의 경우에는 복귀일과 동일하게)", example="2024-01-02"),
     *                 @OA\Property (property="content", type="string", description="사유", example="병원 진료로 인한 외박"),
     *                 @OA\Property (property="type", type="string", description="외박, 외출 여부 (go, sleep)", example="sleep"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="409", description="중복되는 외박/외출 기록이 있을 경우"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date_format:Y-m-d',
                'end_date'   => 'required|date_format:Y-m-d',
                'content'    => 'required|string',
                'type'       => ['required', Rule::in(['go', 'sleep']), 'string'],
            ]);
        } catch(ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $validated['user_id'] = auth('users')->id();

        $absenceList = AbsenceList::where('user_id', $validated['user_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']]);
            })->exists();

        if($absenceList) {
            return response()->json(['error' => '신청 날짜와 중복되는 외박/외출이 있습니다.'], 409);
        }

        $absence = AbsenceList::create($validated);

        return response()->json(['absence' => $absence], 201);
    }

    /**
     * @OA\Get (
     *     path="/api/absence/{id}",
     *     tags={"외출/외박"},
     *     summary="특정 외출/외박 내용",
     *     description="파라미터로 받은 외출/외박의 기록의 아이디와 일치하는 외출/외박 기록을 불러옴",
     *     @OA\Parameter(
     *          name="id",
     *          description="외출/외박 기록의 아이디",
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
            // 외출/외박 기록과 함께 유저의 아이디, 학번, 이름을 함께 불러옴
            $stayOut = AbsenceList::with('user:id,student_id,name')->findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
        }
        // TODO: 반횐되는 키값 변경하기
        return response()->json(['stay_out' => $stayOut]);
    }

    /**
     * @OA\Patch (
     *     path="/api/absence/{id}",
     *     tags={"외출/외박"},
     *     summary="수정",
     *     description="외출/외박 내용을 수정할 때 사용합니다. 변경할 값만 Request Body에 보내면 됩니다.",
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
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
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
        } catch (ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
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
     *     summary="거절(관리자)",
     *     description="외출/외박을 거절할 때 사용합니다. 해당하는 아이디의 status를 false로 변경합니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="거절할 외출/외박 기록의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function reject(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        try {
            $stayOut = AbsenceList::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
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
     *     description="외출/외박 기록 삭제(취소) 기능입니다. 유저가 본인의 예약을 삭제(취소)할 때 사용합니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="삭제(취소)할 외박/외출 기록의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $stayOut = AbsenceList::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
        }

        if(!$stayOut->delete()) return response()->json(['error' => '외출/외박 기록 삭제에 실패하였습니다.'], 500);

        return response()->json(['message' => '외출/외박 기록이 삭제되었습니다.']);
    }
}
