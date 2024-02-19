<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalonBreakTime;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SalonBreakTimeController extends Controller
{

    //TODO: 불러오는 기능 구현

    private $validationRule = [
        'break_times' => 'required|array',
        'date'   => 'required|date',
    ];

    /**
     * @OA\Post (
     *     path="/api/admin/salon-break",
     *     tags={"미용실"},
     *     summary="예약불가 시간 생성",
     *     description="미용실 예약불가 시간 생성",
     *     @OA\RequestBody(
     *         description="예약불가 시간 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="s_time", type="time", description="예약불가 시작", example="08:00:00"),
     *                 @OA\Property (property="e_time", type="time", description="예약불가 종료", example="21:00:00"),
     *                 @OA\Property (property="date", type="date", description="날짜", example="2024-01-01")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Created"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->validationRule);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        foreach ($validated['break_times'] as $breakTime) {
            $salonBreakTime = new SalonBreakTime();
            $salonBreakTime->break_time = $breakTime;
            $salonBreakTime->date = $validated['date'];
            if(!$salonBreakTime->save()) return response()->json(['error' => 'Failed to set BreakTime'], 500);

        }

        return response()->json(['success' => 'Set BreakTime successfully'], 201);
    }

    /**
     * @OA\Delete (
     *     path="/api/admin/salon-break",
     *     tags={"미용실"},
     *     summary="예약불가 시간 삭제",
     *     description="미용실 예약불가 시간 삭제",
     *     @OA\RequestBody(
     *         description="예약불가 시간 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="break_time", type="time", description="삭제할 예약불가 시간", example="08:00:00"),
     *                 @OA\Property (property="date", type="date", description="삭제할 날짜", example="2024-01-01")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function destroy(Request $request)
    {
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
