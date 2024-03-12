<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Models\SalonBreakTime;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SalonBreakTimeController extends Controller
{
    private array $validationRule = [
        'break_time' => 'required|array',
        'date'   => 'required|date',
    ];

    public function authorize($ability, $arguments = [SalonBreakTime::class])
    {
        return Parent::authorize($ability, $arguments);
    }

    public function index(): \Illuminate\Database\Eloquent\Collection
    {
        $dayList = $this->dayList;

        $breakTimes = SalonBreakTime::all(['break_time', 'date']);

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
     *     description="미용실 예약불가 시간 생성",
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
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('store');
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

        foreach ($validated['break_time'] as $breakTime) {
            $salonBreakTime = new SalonBreakTime();
            $salonBreakTime->break_time = $breakTime;
            $salonBreakTime->date = $validated['date'];
            if(!$salonBreakTime->save()) return response()->json(['error' => 'Failed to set BreakTime'], 500);
        }

        return response()->json(['success' => 'Set BreakTime successfully'], 201);
    }

    /**
     * @OA\Delete (
     *     path="/api/salon/break",
     *     tags={"미용실 - 예약불가 시간"},
     *     summary="예약불가 시간 삭제(관리자)(수정)",
     *     description="미용실 예약불가 시간 삭제",
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
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('destroy');
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

        if(!$salonBreakTime) return response()->json(['error' => 'Nothing to delete'], 404);

        return response()->json(['success' => 'Delete BreakTime data successfully']);
    }
}
