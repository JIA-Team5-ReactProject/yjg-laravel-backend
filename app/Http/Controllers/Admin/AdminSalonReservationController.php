<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalonReservation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminSalonReservationController extends Controller
{
    /**
     * @OA\Get (
     *     path="/api/admin/salon-reservation",
     *     tags={"미용실"},
     *     summary="예약 검색",
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
            $startDate = date('Y-m-d', strtotime($validated['r_date']));
            $query = $query->where('reservation_date', $startDate);
        }

        if(isset($request->r_time)) {
            $startTime = $request->r_time;
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
     * @OA\Patch (
     *     path="/api/admin/salon-reservation",
     *     tags={"미용실"},
     *     summary="예약 정보 업데이트(관리자)",
     *     description="미용실 예약 상태를 업데이트",
     *     @OA\RequestBody(
     *         description="업데이트할 상태 및 아이디",
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
    public function update(Request $request)
    {
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
}
