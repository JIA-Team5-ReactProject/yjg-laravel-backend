<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use App\Models\SalonReservation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalonReservationController extends Controller
{
    public function authorize($ability, $arguments = [SalonReservation::class])
    {
        return Parent::authorize($ability, $arguments);
    }
    /**
     * @OA\Get (
     *     path="/api/salon/reservation/user",
     *     tags={"미용실 - 예약"},
     *     summary="현재 유저의 예약 정보 가져오기(수정)",
     *     description="현재 로그인한 유저의 예약 정보를 불러옴",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function index(Request $request)
    {
        return response()->json(['reservations' => SalonReservation::with(['salonService'])->where('user_id', auth('users')->id())->get()]);
    }

    /**
     * @OA\Get (
     *     path="/api/salon/reservation",
     *     tags={"미용실 - 예약"},
     *     summary="예약 검색(수정)",
     *     description="미용실 예약 검색",
     *     @OA\RequestBody(
     *         description="검색할 옵션",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="status", type="char", description="예약의 상태(submit confirm reject 중 하나로 보내면 됨)", example="submit"),
     *                 @OA\Property (property="r_date", type="date", description="검색일", example="2001-01-29"),
     *                 @OA\Property (property="r_time", type="time", description="검색 시간", example="12:00:00"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function show(Request $request)
    {
        $statusRule = ['submit', 'confirm', 'reject'];
        try {
            $validated = $request->validate([
                'status' => ['string', Rule::in($statusRule)],
                'r_date' => 'date',
                'r_time' => 'date_format:H:i'
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $query = SalonReservation::with(['user:id,name,phone_number', 'salonService:id,service,price,gender']);
        if(isset($validated['status'])) {
            $query = $query->where('status', $validated['status']);
        }

        if(isset($validated['r_date'])) {
            $startDate = $validated['r_date'];
            $query = $query->where('reservation_date', $startDate);
        }

        if(isset($request->r_time)) {
            $startTime = $validated['r_time'];
            $query = $query->where('reservation_time', $startTime);
        }

        $reservations = $query->get();

        $reservations->map(function ($item) {
            $item['user_name'] = $item->user['name'];
            $item['service_name'] = $item->salonService['service'];
            $item['price'] = $item->salonService['price'];
            $item['gender'] = $item->salonService['gender'];
            $item['phone_number'] = $item->user['phone_number'];
            unset($item['user'], $item['salonService']);
        });

        return response()->json(['reservations' => $reservations]);
    }

    /**
     * @OA\Post (
     *     path="/api/salon/reservation",
     *     tags={"미용실 - 예약"},
     *     summary="예약(수정)",
     *     description="학생 미용실 예약",
     *     @OA\RequestBody(
     *         description="예약 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="salon_service_id", type="integer", description="미용실 서비스 아이디", example=1),
     *                 @OA\Property (property="r_date", type="date", description="예약 날짜", example="2024-01-01"),
     *                 @OA\Property (property="r_time", type="time", description="예약 시간", example="12:12:12"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Success"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Failed to save"),
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'salon_service_id' => 'required|numeric',
                'r_date' => 'required|date_format:Y-m-d',
                'r_time' => 'required|date_format:H:i',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $userId = auth('users')->id();

        $reservation = SalonReservation::create([
            'salon_service_id' => $validated['salon_service_id'],
            'user_id' => $userId,
            'reservation_date' => $validated['r_date'],
            'reservation_time' => $validated['r_time'],
        ]);

        if(!$reservation) return response()->json(['error' => 'Failed to reservation'], 500);

        return response()->json(['reservation' => $reservation], 201);
    }

    /**
     * @OA\Patch (
     *     path="/api/salon/reservation",
     *     tags={"미용실 - 예약"},
     *     summary="예약 상태 수정(관리자)(수정)",
     *     description="미용실 예약 상태를 수정",
     *     @OA\RequestBody(
     *         description="수정할 상태 및 아이디",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="id", type="integer", description="예약 아이디", example=1),
     *                 @OA\Property (property="status", type="boolean", description="true로 보내면 승인, false로 보내면 거절", example=true),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
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
                'id' => 'required|numeric',
                'status' => 'required|boolean'
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        try {
            $reservation = SalonReservation::findOrFail($validated['id']);
        } catch (ModelNotFoundException $modelException) {
            $errorMessage = $modelException->getMessage();
            return response()->json(['error' => $errorMessage], 404);
        }

        if($validated['status']) $reservation->status = 'confirm';
        else $reservation->status = 'reject';

        if(!$reservation->save()) return response()->json(['error' => 'Failed to update reservation status'], 500);

        return response()->json(['message' => 'Reservation status updated successfully']);
    }

    /**
     * @OA\Delete (
     *     path="/api/salon/reservation/{id}",
     *     tags={"미용실 - 예약"},
     *     summary="예약 취소(수정)",
     *     description="미용실 예약 취소",
     *      @OA\Parameter(
     *            name="id",
     *            description="취소할 예약의 아이디",
     *            required=true,
     *            in="path",
     *            @OA\Schema(type="integer"),
     *        ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function destroy(string $id)
    {
        if(!SalonReservation::destroy($id)) return response()->json(['error' => 'Failed to cancel reservation'], 500);

        return response()->json(['success' => 'Reservation canceled successfully']);
    }
}
