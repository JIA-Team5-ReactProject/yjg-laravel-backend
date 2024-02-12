<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalonReservation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
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
     *                 @OA\Property (property="status", type="char", description="예약의 상태(SCR 중 하나로 보내면 됨, 각각 Submit, Confirm, Reject 임)", example="S"),
     *                 @OA\Property (property="start_date", type="date", description="검색 시작일", example="2001-01-29"),
     *                 @OA\Property (property="end_date", type="date", description="검색 종료일", example="2023-02-12"),
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
        try {
            $validated = $request->validate([
                'status' => 'size:1',
                'start_date' => 'date',
                'end_date' => 'date',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        if(isset($validated['status'])) {
            $reservations = SalonReservation::where('status', $validated['status'])->get();
        }
        else {
            $reservations = SalonReservation::all();
        }

        if(isset($validated['start_date'])) {
            $start_date = date('Y-m-d 00:00:00', strtotime($validated['start_date']));
            $reservations = $reservations->where('reservation_date', '>=', $start_date);
        }

        if(isset($validated['end_date'])) {
            $end_date = date('Y-m-d 23:59:59', strtotime($validated['end_date']));
            $reservations = $reservations->where('reservation_date', '<=', $end_date);
        }

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
     *                 @OA\Property (property="status", type="char", description="예약의 상태(C,R 중 하나로 보내면 됨, 각각 Confirm, Reject 임)", example="R"),
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

        if($validated['status']) $reservation->status = 'C';
        else $reservation->status = 'R';

        if(!$reservation->save()) return response()->json(['error' => 'Failed to update reservation status'], 500);

        return response()->json(['message' => 'Reservation status updated successfully']);
    }
}
