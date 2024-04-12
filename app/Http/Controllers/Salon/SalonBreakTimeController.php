<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Models\SalonBreakTime;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SalonBreakTimeController extends Controller
{
    private array $validationRule = [
        'break_time' => 'required|array',
        'date'   => 'required|date',
    ];

    public function authorize($ability, $arguments = [SalonBreakTime::class]): Response
    {
        return Parent::authorize($ability, $arguments);
    }

    /**
     * 해당 메서드는 미용실 예약에서 사용됩니다.
     * @return Collection
     */
    public function index(): Collection
    {
        $dayList = $this->dayList;

        $breakTimes = SalonBreakTime::all(['break_time', 'date']);

        // 요일, 시간:분으로 포맷팅하여 반환합니다.
        foreach ($breakTimes as $breakTime) {
            $breakTime->day = $dayList[date('w', strtotime($breakTime->date))];
            $breakTime->break_time = date('H:i', strtotime($breakTime->break_time));
        }

        return $breakTimes;
    }

    /**
     * @OA\Post (
     *     path="/api/salon/break",
     *     tags={"미용실 - 예약불가 시간"},
     *     summary="예약불가 시간 생성(관리자)",
     *     description="미용실 예약불가 시간을 추가할 때 사용합니다.",
     *     @OA\RequestBody(
     *         description="예약불가 시간 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property(
     *                     property="break_time",
     *                     type="array",
     *                     @OA\Items(
     *                          example="10:00",
     *                     ),
     *                 ),
     *                 @OA\Property (property="date", type="date", description="날짜", example="2024-01-01")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Created"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('salon');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        try {
            $validated = $request->validate($this->validationRule);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        // 시간 갯수만큼 배열을 순회하여 레코드를 생성합니다.
        foreach ($validated['break_time'] as $breakTime) {
            $salonBreakTime = new SalonBreakTime();
            $salonBreakTime->break_time = $breakTime;
            $salonBreakTime->date = $validated['date'];
            if(!$salonBreakTime->save()) return response()->json(['error' => '예약불가 시간 추가에 실패하였습니다.'], 500);
        }

        return response()->json(['message' => '예약불가 시간을 성공적으로 추가하였습니다.'], 201);
    }

    /**
     * @OA\Delete (
     *     path="/api/salon/break",
     *     tags={"미용실 - 예약불가 시간"},
     *     summary="예약불가 시간 삭제(관리자)(수정)",
     *     description="미용실 예약불가 시간 삭제 시 사용합니다.",
     *     @OA\RequestBody(
     *         description="예약불가 시간 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property(
     *                     property="break_time",
     *                     type="array",
     *                     @OA\Items(type="time"),
     *                     description="삭제할 시간"
     *                 ),
     *                 @OA\Property (property="date", type="date", description="삭제할 날짜", example="2024-01-01")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $this->authorize('salon');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        try {
            $validated = $request->validate($this->validationRule);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $salonBreakTime = SalonBreakTime::where('break_time', $validated['break_time'])
            ->where('date', $validated['date'])->delete();

        if(!$salonBreakTime) return response()->json(['error' => $this->modelExceptionMessage], 404);

        return response()->json(['message' => '예약불가 시간을 성공적으로 삭제하였습니다.']);
    }
}
