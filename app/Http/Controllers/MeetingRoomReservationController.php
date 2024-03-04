<?php

namespace App\Http\Controllers;

use App\Models\MeetingRoomReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MeetingRoomReservationController extends Controller
{
    /**
     * @OA\Get (
     *     path="/api/meeting-room/reservation",
     *     tags={"회의실"},
     *     summary="전체 보기",
     *     description="전체 보기",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['meeting_room_reservations' => MeetingRoomReservation::with('user')->get()]);
    }

    /**
     * @OA\Get (
     *     path="/api/meeting-room/reservation/user",
     *     tags={"회의실"},
     *     summary="유저의 예약",
     *     description="로그인한 유저의 회의실 예약 목록을 반환",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function userIndex(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(['meeting_room_reservations' => MeetingRoomReservation::with('user')->where('user_id', $request->user()->id)->get()]);
    }

    /**
     * @OA\Post (
     *     path="/api/meeting-room/reservation",
     *     tags={"회의실"},
     *     summary="예약하기",
     *     description="회의실 예약하기",
     *     @OA\RequestBody(
     *         description="예약 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="속성명", type="타입", description="설명", example="예시"),
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
        try {
            $validated = $request->validate([
                'meeting_room_number' => 'required|numeric',
                'reservation_date' => 'required|date_format:Y-m-d',
                'reservation_s_time' => 'required|date_format:H:i:s',
                'reservation_e_time' => 'required|date_format:H:i:s',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $validated['user_id'] = $request->user()->id;

        //TODO: 해당 호실, 해당 시간 예약이 있는지 없는지

        $reserved = MeetingRoomReservation::where('meeting_room_number', $validated['meeting_room_number'])
            ->where('reservation_date', $validated['reservation_date'])
            ->where('reservation_s_time', $validated['reservation_s_time'])->get();

        if(empty($reserved)) return response()->json(['error' => '이미 예약된 시간입니다.'], 409);


        $reservation = MeetingRoomReservation::create($validated);

        if(!$reservation) return response()->json(['error' => '예약에 실패하였습니다.'], 500);

        return response()->json(['success' => '성공적으로 예약되었습니다.', 'reservation' => $reservation], 201);
    }

    /**
     * @OA\Get (
     *     path="/api/meeting-room/reservation/{id}",
     *     tags={"회의실"},
     *     summary="특정 예약",
     *     description="아이디에 해당하는 예약 정보를 받아옴",
     *     @OA\Parameter(
     *          name="id",
     *          description="예약 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:meeting_room_reservations,id|numeric'
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        return response()->json(['meeting_room_reservation' => MeetingRoomReservation::with('user')->findOrFail($id)]);
    }

    /**
     * @OA\Patch (
     *     path="/api/meeting-room/reservation/reject/{id}",
     *     tags={"회의실"},
     *     summary="예약 거절",
     *     description="관리자의 사유에 의해 예약 거절",
     *     @OA\Parameter(
     *          name="id",
     *          description="예약을 거절할 ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function reject(string $id)
    {
        $validator = Validator::make(['id'=> $id], [
            'id' => 'required|exists:meeting_room_reservations,id|numeric'
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $reservation = MeetingRoomReservation::findOrFail($id);

        $reservation->status = false;

        if(!$reservation->save()) return response()->json(['error' => '예약 상태 변경에 실패하였습니다.'], 500);

        return response()->json(['success' => '예약 상태 변경에 성공하였습니다.']);
    }

    /**
     * @OA\Delete (
     *     path="/api/meeting-room/reservation/{id}",
     *     tags={"회의실"},
     *     summary="회의실 예약 삭제",
     *     description="",
     *     @OA\Parameter(
     *          name="id",
     *          description="삭제할 예약의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function destroy(string $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:meeting_room_reservations,id|numeric'
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $reservation = MeetingRoomReservation::findOrFail($id);

        if(!$reservation->delete()) return response()->json(['error' => '예약 삭제에 실패하였습니다.'], 500);

        return response()->json(['success' => '예약이 삭제되었습니다.']);
    }
}
