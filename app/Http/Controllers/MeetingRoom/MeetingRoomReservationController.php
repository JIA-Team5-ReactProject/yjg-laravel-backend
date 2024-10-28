<?php

namespace App\Http\Controllers\MeetingRoom;

use App\Events\ServerSideEvent;
use App\Http\Controllers\Controller;
use App\Models\MeetingRoom;
use App\Models\MeetingRoomReservation;
use App\Services\NotificationService;
use App\Services\ReservedTimeService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Exception\MessagingException;

class MeetingRoomReservationController extends Controller
{
    public function __construct(protected NotificationService $service)
    {
    }

    public function authorize($ability, $arguments = [MeetingRoomReservation::class]): Response
    {
        return Parent::authorize($ability, $arguments);
    }
    /**
     * @OA\Get (
     *     path="/api/meeting-room/reservation",
     *     tags={"회의실 - 예약"},
     *     summary="예약 검색",
     *     description="주어진 날짜, 호실을 통해 조건에 맞는 예약을 검색합니다.",
     *     @OA\Parameter(
     *          name="date",
     *          description="조회할 날짜",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *          name="room_number",
     *          description="회의실의 번호",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="VaildationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date'        => 'required|date-format:Y-m-d',
                'room_number' => 'numeric|exists:meeting_rooms,room_number',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        // reservation_date는 필수 값
        $reservations = MeetingRoomReservation::with('user:id,name')->where('reservation_date', $validated['date'])->where('status', true);

        if(isset($validated['room_number'])) {
            $reservations = $reservations->where('meeting_room_number', $validated['room_number']);
        }

        // 유저 정보 중 이름만 객체에서 빼서, user_name 키로 저장
        foreach ($reservations as $reservation) {
            $userName = $reservation->user['name'];
            $reservation['user_name'] = $userName;
            unset($reservation['user']);
        }

        $reservations = $reservations->paginate(8);

        return response()->json(['reservations' => $reservations]);
    }

    /**
     * @OA\Get (
     *     path="/api/meeting-room/reservation/user",
     *     tags={"회의실 - 예약"},
     *     summary="유저의 예약",
     *     description="로그인한 유저의 회의실 예약 목록을 불러올 때 사용합니다.",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function userIndex(): JsonResponse
    {
        // 날짜와 시간이 빠른 순으로 정렬
        return response()->json(['meeting_room_reservations' => MeetingRoomReservation::with('user')
            ->where('user_id', auth('users')->id())->orderBy('reservation_date')->orderBy('reservation_s_time')->get()]);

    }

    /**
     * @OA\Post (
     *     path="/api/meeting-room/reservation",
     *     tags={"회의실 - 예약"},
     *     summary="예약하기",
     *     description="회의실 예약 시 사용합니다.",
     *     @OA\RequestBody(
     *         description="예약 관련 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="meeting_room_number", type="string", description="예약할 방 번호", example="206"),
     *                 @OA\Property (property="reservation_date", type="date", description="예약할 날짜", example="2024-03-01"),
     *                 @OA\Property (property="reservation_s_time", type="time", description="예약 시작(분까지)", example="17:00"),
     *                 @OA\Property (property="reservation_e_time", type="time", description="예약 종료(분까지)", example="20:00"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'meeting_room_number' => 'required|numeric',
                'reservation_date' => 'required|date_format:Y-m-d',
                'reservation_s_time' => 'required|date_format:H:i',
                'reservation_e_time' => 'required|date_format:H:i',
            ]);
        } catch (ValidationException $exception) {
            $errorStatus = $exception->status;
            $errorMessage = $exception->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        // 회의실의 예약 가능 여부 체크
        try {
            $meetingRoom = MeetingRoom::findOrFail($validated['meeting_room_number']);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => __('messages.404')], 404);
        }

        if(!$meetingRoom->open) return response()->json(['error' => __('messages.403')], 403);

        // 유저 아이디를 받아옴
        $validated['user_id'] = auth('users')->id();

        // 기존에 예약된 시간 예외처리
        $reservedTimes = new ReservedTimeService($validated['reservation_date'], $validated['meeting_room_number']);

        foreach ($reservedTimes() as $reservedTime) {
            if($reservedTime == $validated['reservation_s_time'] ||
                $reservedTime == $validated['reservation_e_time']) {
                return response()->json(['error' => __('messages.409')], 409); // conflict 에러
            }
        }

        $reservation = MeetingRoomReservation::create($validated);

        if(!$reservation) return response()->json(['error' => __('messages.500')], 500);

        // 예약 데이터 SSE 전송
        event(new ServerSideEvent('events.meeting_room', $reservation));

        return response()->json(['message' => __('messages.200'), 'reservation' => $reservation], 201);
    }

    /**
     * @OA\Get (
     *     path="/api/meeting-room/reservation/{id}",
     *     tags={"회의실 - 예약"},
     *     summary="특정 예약",
     *     description="특정 아이디에 해당하는 예약 정보를 받아올 때 사용합니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="예약 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function show(string $id): JsonResponse
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
     *     tags={"회의실 - 예약"},
     *     summary="예약 거절(관리자)",
     *     description="관리자의 사유에 의해 예약을 거절할 때 사용합니다. status를 false로 바꿉니다.",
     *     @OA\Parameter(
     *          name="id",
     *          description="예약을 거절할 ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="ModelNotFoundException"),
     *     @OA\Response(response="422", description="ValidationException"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function reject(string $id): JsonResponse
    {
        try {
            $this->authorize('admin');
        } catch (AuthorizationException) {
            return $this->denied(__('auth.denied'));
        }

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

        try {
            $reservation = MeetingRoomReservation::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => __('messages.404')], 404);
        }

        $reservation->status = false;

        if(!$reservation->save()) return response()->json(['error' => __('messages.500')], 500);

        if($reservation->user['push_enabled']) {
            $token = $reservation->user['fcm_token'];

            $notificationBody = __('notification.reserved_date') . $reservation['reservation_date'] . ' ' . $reservation['reservation_s_time'] . '~' . $reservation['reservation_e_time'] .
                __('notification.room_number') . ': ' . $reservation['meeting_room_number'];

            // 알림 전송
            try {
                $this->service->postNotification(__('notification.meeting_room_reject'), $notificationBody, $token, 'meeting', $reservation->id);
            } catch (MessagingException) {
                return response()->json(['error' => __('messages.500.push')], 500);
            }
        }

        return response()->json(['message' => __('messages.200')]);
    }

    /**
     * @OA\Delete (
     *     path="/api/meeting-room/reservation/{id}",
     *     tags={"회의실 - 예약"},
     *     summary="회의실의 예약을 삭제할 때 사용합니다.",
     *     description="",
     *     @OA\Parameter(
     *          name="id",
     *          description="삭제할 예약의 아이디",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="ServerError"),
     * )
     */
    public function destroy(string $id): JsonResponse
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

        try {
            $reservation = MeetingRoomReservation::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => __('messages.404')], 404);
        }

        if(!$reservation->delete()) return response()->json(['error' => __('messages.500')], 500);

        return response()->json(['message' => __('messages.200')]);
    }
}
