<?php

namespace App\Http\Controllers;

use App\Models\MeetingRoom;
use App\Models\MeetingRoomReservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MeetingRoomController extends Controller
{
    private array $messages = [
        'exists' => '해당하는 회의실이 존재하지 않습니다.',
    ];
    /**
     * @OA\Get (
     *     path="/api/meeting-room",
     *     tags={"회의실"},
     *     summary="목록",
     *     description="회의실 목록",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['meeting_rooms' => MeetingRoom::all()]);
    }

    /**
     * @OA\Post (
     *     path="/api/admin/meeting-room",
     *     tags={"회의실"},
     *     summary="추가",
     *     description="회의실 추가",
     *     @OA\RequestBody(
     *         description="설명",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="room_number", type="string", description="추가할 회의실의 번호", example="203"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Created"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'room_number' => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $meetingRoom = MeetingRoom::create([
            'room_number' => $validated['room_number'],
        ]);

        if(!$meetingRoom) return response()->json(['error' => '회의실 추가에 실패하였습니다.'], 500);

        return response()->json(['success' => '회의실이 추가되었습니다.', 'meeting_room' => $meetingRoom], 201);
    }

    /**
     * @OA\Get (
     *     path="/api/meeting-room/{room_number}",
     *     tags={"회의실"},
     *     summary="회의실의 목록",
     *     description="특정 회의실의 예약 목록",
     *     @OA\Parameter(
     *          name="roomNumber",
     *          description="회의실 번호",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function show(string $roomNumber)
    {
        $validator = Validator::make([
            'room_number' => $roomNumber
        ], [
            'room_number' => 'required|numeric|exists:meeting_rooms,room_number'
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }
        return response()->json(['reservations' => MeetingRoomReservation::where('meeting_room_number', $roomNumber)->get()]);
    }

    /**
     * @OA\Get (
     *     path="/api/meeting-room/check",
     *     tags={"회의실"},
     *     summary="예약된 시간 목록",
     *     description="특정 날짜 및 특정 회의실의 예약된 시간 목록",
     *     @OA\Parameter(
     *          name="date",
     *          description="조회할 날짜",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *          name="roomNumber",
     *          description="회의실의 룸 번호",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="integer"),
     *      ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function checkReservation(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'date'        => 'required|date_format:Y-m-d',
                'room_number' => 'required|numeric|exists:meeting_rooms,room_number',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $reservations = MeetingRoomReservation::query();

        if(isset($validated['date'])) {
            $reservations = $reservations->where('reservation_date', $validated['date']);
        }

        if (isset($validated['room_number'])) {
            $reservations = $reservations->where('meeting_room_number' , $validated['room_number']);
        }

        $reservations  = $reservations->get();
        $reservedTimes = [];

        foreach ($reservations as $reservation) {
            $start = Carbon::parse($reservation->reservation_s_time);
            $end   = Carbon::parse($reservation->reservation_e_time);
            $currentHour = $start->copy();
            while ($currentHour < $end) {
                $reservedTimes[] = $currentHour->copy()->format('H:i');
                $currentHour->addHour();
            }
        }

        return response()->json(['reservations' => $reservedTimes]);
    }

    /**
     * @OA\Delete (
     *     path="/api/admin/meeting-room/{id}",
     *     tags={"회의실"},
     *     summary="회의실 삭제",
     *     description="특정 회의실 삭제",
     *     @OA\Parameter(
     *          name="id",
     *          description="회의실의 룸 번호",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make([$id], [
            'id' => 'required|exists:meeting_rooms,room_number|string'
        ], $this->messages);

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $meetingRoom = MeetingRoom::findOrFail($id);

        if(!$meetingRoom->delete()) return response()->json(['error' => '회의실 삭제에 실패하였습니다.'], 500);

        return response()->json(['success' => '회의실이 삭제되었습니다.']);
    }
}
