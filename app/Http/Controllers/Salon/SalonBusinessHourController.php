<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Models\SalonBusinessHour;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalonBusinessHourController extends Controller
{
    public function __construct(protected  SalonBreakTimeController $salonBreakTimeController)
    {
    }

    public function authorize($ability, $arguments = [SalonBusinessHour::class])
    {
        return Parent::authorize($ability, $arguments);
    }

    /**
     * @OA\Get (
     *     path="/api/salon/hour",
     *     tags={"미용실 - 영업시간"},
     *     summary="전체 영업시간(수정)",
     *     description="모든 요일의 미용실 영업 시간을 불러올 때 사용합니다.",
     *     @OA\Response(response="200", description="Success"),
     * )
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['business_hours' => SalonBusinessHour::all(['id', 's_time', 'e_time', 'date'])]);
    }

    /**
     * @OA\Get (
     *     path="/api/salon/hour/{day}",
     *     tags={"미용실 - 영업시간"},
     *     summary="특정 날짜의 영업시간(수정)",
     *     description="특정 날짜의 미용실 영업 시간을 한시간 단위로 얻을 때 사용합니다.",
     *      @OA\Parameter(
     *            name="day",
     *            description="2024-01-01",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="date"),
     *        ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function show(string $date): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(['date' => $date], [
            'date' => 'required|date_format:Y-m-d',
        ]);

        try {
            $validated = $validator->validate();
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        // 해당 날짜의 요일을 얻어옵니다.
        $day_of_week = $this->dayList[date('w', strtotime($date))];

        // 요일과 일치하는 날짜의 영업 시간을 불러옵니다.
        $b_hour = SalonBusinessHour::where('date' , $day_of_week)->first();

        // UNIX 타임스탬프로 변경
        $current = $b_hour->s_time;
        $end = $b_hour->e_time;
        $business_hours = [];

        // 영업 시간을 30분 단위로 쪼개어, 배열에 객체형태로 저장합니다.
        while ($current <= $end) {
            $business_hours[] = (object) ['time' => $current, 'available' => true];
            $current = Carbon::parse($current)->addMinutes(30)->format('H:i');
        }

        // 예약불가 시간 필터링
        $salonBreakTimes = $this->salonBreakTimeController->index()->where('date', $validated['date']);

        // 예약불가 시간이 있을 경우, 동작합니다.
        // TODO: 효율적으로 만들 필요 있음
        if($salonBreakTimes->isNotEmpty()) {
            $breakTimes = [];

            // 예약불가 시간을 배열에 담습니다.
            foreach($salonBreakTimes as $salonBreakTime) {
                $breakTimes[] = $salonBreakTime->break_time;
            }

            // 위 배열에 존재하는 경우 available 값을 false로 바꿉니다.
            foreach ($business_hours as $business_hour) {
                if(in_array($business_hour->time, $breakTimes)) {
                    $business_hour->available = false;
                }
            }
        }

        return response()->json(['business_hours' => $business_hours]);
    }

    /**
     * @OA\Post (
     *     path="/api/salon/hour",
     *     tags={"미용실 - 영업시간"},
     *     summary="영업시간 생성(관리자)(수정)",
     *     description="미용실 영업시간 생성 시 사용합니다.",
     *     @OA\RequestBody(
     *         description="영업시간 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="s_time", type="time", description="영업 시작", example="08:00:00"),
     *                 @OA\Property (property="e_time", type="time", description="영업 종료", example="21:00:00"),
     *                 @OA\Property (property="date", type="date", description="요일(대문자, 기존 DB 내에 중복 값 없어야함)", example="MON")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Created"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
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
            $validated = $request->validate([
                's_time' => 'required|date_format:H:i',
                'e_time' => 'required|date_format:H:i',
                'date'   => ['required', Rule::in($this->dayList), 'unique:salon_business_hours,date'],
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $businessHour = SalonBusinessHour::create($validated);

        if(!$businessHour) return response()->json(['error' => '미용실 영업시간 설정에 실패하였습니다.'], 500);

        return response()->json(['reservation' => $businessHour], 201);
    }

    /**
     * @OA\Patch (
     *     path="/api/salon/hour",
     *     tags={"미용실 - 영업시간"},
     *     summary="영업시간 업데이트(관리자)",
     *     description="미용실 영업시간을 업데이트 시 사용합니다.",
     *     @OA\RequestBody(
     *         description="업데이트할 영업시간 및 아이디",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="b_hour_id", type="integer", description="영업시간 아이디", example=1),
     *                 @OA\Property (property="s_time", type="time", description="영업 시작", example="08:00:00"),
     *                 @OA\Property (property="e_time", type="time", description="영업 종료", example="21:00:00"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('update');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        try {
            $validated = $request->validate([
                'b_hour_id' => 'required|numeric',
                's_time' => 'required|date_format:H:i',
                'e_time' => 'required|date_format:H:i',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        try {
            $bHour = SalonBusinessHour::findOrFail($validated['b_hour_id']);
        } catch(ModelNotFoundException) {
            return response()->json(['error' => $this->modelExceptionMessage], 404);
        }

        $bHour->s_time = $validated['s_time'];
        $bHour->e_time = $validated['e_time'];

        if(!$bHour->save()) return response()->json(['error' => '미용실 영업시간 수정에 실패하였습니다.'], 500);

        return response()->json(['message' => '미용실 영업시간 수정에 성공하였습니다.']);
    }

    /**
     * @OA\Delete (
     *     path="/api/salon/hour/{id}",
     *     tags={"미용실 - 영업시간"},
     *     summary="영업시간 삭제(관리자)",
     *     description="미용실 영업시간 삭제시 사용합니다.",
     *      @OA\Parameter(
     *            name="id",
     *            description="삭제할 영업시간 값의 아이디",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="integer"),
     *        ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('destroy');
        } catch (AuthorizationException) {
            return $this->denied();
        }

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:salon_business_hours,id',
        ]);

        try {
            $validated = $validator->validate();
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        if(!SalonBusinessHour::destroy($validated['id'])) return response()->json(['error' => '영업시간 삭제에 실패하였습니다.'], 500);

        return response()->json(['message' => '영업시간이 성공적으로 삭제되었습니다.']);
    }
}
