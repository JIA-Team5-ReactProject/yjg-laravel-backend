<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SalonReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserSalonReservationController extends Controller
{
    /**
     * @OA\Get (
     *     path="/api/user/salon-reservation",
     *     tags={"학생"},
     *     summary="예약 정보 가져오기",
     *     description="현재 로그인한 유저의 예약 정보를 불러옴",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function index(Request $request)
    {
        //TODO: 현재 로그인과 연동하여 테스트 필요
        return response()->json(['reservations' => SalonReservation::with(['salonPrice.salonService'])->where('user_id', $request->user()->id)->get()]);
    }
    /**
     * @OA\Post (
     *     path="/api/user/salon-reservation",
     *     tags={"미용실"},
     *     summary="예약",
     *     description="학생 미용실 예약",
     *     @OA\RequestBody(
     *         description="예약 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="salon_price_id", type="integer", description="미용실 가격 아이디", example="관리자"),
     *                 @OA\Property (property="user_id", type="integer", description="유저 아이디", example=1),
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
                'salon_price_id' => 'required|numeric',
                'user_id' => 'required|numeric',
                'r_date' => 'required|date',
                'r_time' => 'required|time',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $reservation = SalonReservation::create([
            'salon_price_id' => $validated['salon_price_id'],
            'user_id' => $validated['user_id'],
            'reservation_date' => $validated['r_date'],
            'reservation_time' => $validated['r_time'],
        ]);

        if(!$reservation) return response()->json(['error' => 'Failed to reservation'], 500);

        return response()->json(['reservation' => $reservation], 201);
    }
    /**
     * @OA\Delete (
     *     path="/api/admin/salon-reservation/{id}",
     *     tags={"미용실"},
     *     summary="예약 취소",
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
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:App\Models\SalonReservation,id',
        ]);
        //TODO: 로그인 로직 구현 완료 시 예약 취소하는 사람의 아이디(현재 로그인 된 유저)와 예약의 아이디가 일치한지 확인
        try {
            $validated = $validator->validate();
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        if(!SalonReservation::destroy($validated['id'])) return response()->json(['error' => 'Failed to cancel reservation'], 500);

        return response()->json(['success' => 'Reservation canceled successfully']);
    }
}
