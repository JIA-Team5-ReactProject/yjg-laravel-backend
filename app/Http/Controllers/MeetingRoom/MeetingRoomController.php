<?php

namespace App\Http\Controllers\MeetingRoom;

use App\Http\Controllers\Controller;
use App\Models\MeetingRoom;
use App\Services\ReservedTimeService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MeetingRoomController extends Controller
{
    public function authorize($ability, $arguments = [MeetingRoom::class]): Response
    {
        return Parent::authorize($ability, $arguments);
    }
    /**
     * @OA\Get (
     *     path="/api/meeting-room",
     *     tags={"회의실"},
     *     summary="회의실 목록",
     *     description="전체 회의실 목록을 불러올 때 사용합니다.",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(['meeting_rooms' => MeetingRoom::all()]);
    }

    /**
     * @OA\Post (
     *     path="/api/meeting-room",
     *     tags={"회의실"},
     *     summary="회의실 추가(관리자)",
     *     description="회의실 추가 시 사용합니다.",
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
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied(__('auth.denied'));
        }

        try {
            $validated = $request->validate([
                'room_number' => 'required|numeric|unique:meeting_rooms,room_number',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error'=>$errorMessage], $errorStatus);
        }

        $meetingRoom = MeetingRoom::create([
            'room_number' => $validated['room_number'],
        ]);

        if(!$meetingRoom) return response()->json(['error' => __('messages.500')], 500);

        return response()->json(['message' => __('messages.200'), 'meeting_room' => $meetingRoom], 201);
    }

    /**
     * @OA\Get (
     *     path="/api/meeting-room/check",
     *     tags={"회의실"},
     *     summary="회의실의 예약된 시간 목록",
     *     description="특정 날짜 및 특정 회의실의 예약된 시간 목록을 불러올 때 사용합니다.",
     *     @OA\Parameter(
     *          name="date",
     *          description="조회할 날짜",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *          name="room_number",
     *          description="회의실의 룸 번호",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="integer"),
     *      ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function checkReservation(Request $request): JsonResponse
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

        // ReservedTimeService 클래스를 이용
        $reservedTimes = new ReservedTimeService($validated['date'], $validated['room_number']);

        return response()->json(['reservations' => $reservedTimes()]);
    }

    /**
     * @OA\Patch (
     *     path="/api/meeting-room/{id}",
     *     tags={"회의실"},
     *     summary="회의실 상태 변경(관리자)",
     *     description="회의실을 닫거나 열 때 사용합니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="변경할 회의실 룸 번호",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string"),
     *      ),
     *     @OA\RequestBody(
     *         description="회의실 오픈 여부",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="open", type="boolean", description="오픈 여부", example=true),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Created"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:meeting_rooms,room_number|string'
        ]);

        try {
            $validator->validate();
            $validated = $request->validate([
                'open' => 'required|boolean',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()]);
        }

        try {
            $meetingRoom = MeetingRoom::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => __('messages.404')], 404);
        }

        $meetingRoom->open = $validated['open'];

        if(!$meetingRoom->save()) return response()->json(['error' => __('messages.500')], 500);

        return response()->json(['message' => __('messages.200')]);
    }

    /**
     * @OA\Delete (
     *     path="/api/meeting-room/{id}",
     *     tags={"회의실"},
     *     summary="회의실 삭제(관리자)",
     *     description="특정 회의실을 삭제할 때 사용합니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="회의실의 룸 번호",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied(__('auth.denied'));
        }

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:meeting_rooms,room_number|string'
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        try {
            $meetingRoom = MeetingRoom::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => __('messages.404')], 404);
        }

        if(!$meetingRoom->delete()) return response()->json(['error' => __('messages.500')], 500);

        return response()->json(['success' => __('messages.200')]);
    }
}
