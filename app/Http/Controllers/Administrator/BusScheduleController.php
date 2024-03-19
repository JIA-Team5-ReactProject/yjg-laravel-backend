<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\BusRound;
use App\Models\BusRoute;
use App\Models\BusSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BusScheduleController extends Controller
{
    /**
     * @OA\Post (
     *     path="/api/bus/schedule",
     *     tags={"버스"},
     *     summary="버스 시간표 추가",
     *     description="버스 시간표를 추가",
     *     @OA\RequestBody(
     *         description="추가할 시간표 내용,노선,회차",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="round_id", type="string", description="버스회차id", example="1"),
     *                 @OA\Property (property="station", type="string", description="정류장", example="태전역"),
     *                 @OA\Property (property="bus_time", type="date_format", description="버스시간", example="08:00"),
     *              )
     *         )
     *     ),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function store(Request $request)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                
                'station' => 'required|string',
                'bus_time' => 'required|date_format:H:i'
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }


        // try{
        //     $busRoute = BusRoute::where('weekend', $validatedData['weekend'])
        //                      ->where('semester', $validatedData['semester'])
        //                      ->where('bus_route_direction', $validatedData['bus_route_direction'])
        //                      ->firstOrFail(); // 일치하는 항목이 없으면 예외 발생
        // } catch (\Exception $exception) {
        //     return response()->json(['error' => $exception->getMessage()], 404);
        // }

        //$existingRound = BusRound::where('id', $validatedData['round_id'])->pluck('id');

        //라운드에 같은 값이 있으면 새로 안만들고 없으면 새로 BusRound테이블에 추가
        // if ($existingRound->isEmpty()) {
        //     try {
        //         $busRound = BusRound::create([
        //             'round' => $validatedData['round_id'],
        //             'bus_route_id' => $busRoute->id,
        //         ]);
        //     } catch (\Exception $exception) {
        //         return response()->json(['error' => $exception->getMessage()], 404);
        //     }
        // } else {
        //    $busRound = $validatedData['round_id'];
        // }

        $busRoute = BusRound::where('id', $request->round_id)
            ->first();
        $busTime = Carbon::createFromFormat('H:i', $validatedData['bus_time'])->format('H:i');
        Log::info('시간: '.$busTime );

        try{
            BusSchedule::create([
                'bus_round_id' => $busRoute->id,
                'station' => $validatedData['station'],
                'bus_time' => $validatedData['bus_time'],
            ]);
            return response()->json(['message' => '버스 시간표 등록이 확인 되었습니다.']);
        }catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
     /**
     * @OA\Patch (
     *     path="/api/bus/round/{id}",
     *     tags={"버스"},
     *     summary="버스 회차 수정",
     *     description="버스 회차를 수정",
     *      @OA\Parameter(
     *           name="id",
     *           description="수정할 회차 id",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *          ),
     *     @OA\RequestBody(
     *         description="수정할 회차 내용, 회차id",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="round", type="string", description="회차", example="3회차"),
     *              )
     *         )
     *     ),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'round' => 'required|string',
            ]);

            // 수정할 버스 회차 찾기
            $busRound = BusRound::findOrFail($id);

            $busRound->update([
                'round' => $validatedData['round'],
            ]);

            return response()->json(['message' => '버스 회차 수정이 완료되었습니다.']);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }
    }

    /**
     * @OA\Delete (
     *     path="/api/bus/schedule/{id}",
     *     tags={"버스"},
     *     summary="버스 시간표 삭제",
     *     description="버스 시간표 삭제",
     *     @OA\Parameter(
     *           name="id",
     *           description="삭제할 버스 시간표의 아이디",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function destroySchedule($id)
    {
        try {
            // 삭제할 버스 시간표를 찾습니다.
            $busSchedule = BusSchedule::findOrFail($id);

            // 버스 시간표를 삭제합니다.
            $busSchedule->delete();

            return response()->json(['message' => '버스 시간표가 성공적으로 삭제되었습니다.']);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }



    /**
     * @OA\Post (
     *     path="/api/bus/round",
     *     tags={"버스"},
     *     summary="버스 회차 추가",
     *     description="버스 회차 추가 ",
     *     @OA\RequestBody(
     *         description="추가할 버스 회차",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="round", type="string", description="회차", example="1"),
     *                 @OA\Property (property="weekend", type="boolean", description="주말/평일", example="true"),
     *                 @OA\Property (property="semester", type="boolean", description="학기/방학", example="false"),
     *                 @OA\Property (property="bus_route_direction", type="string", description="버스 방향", example="B"),
     *              )
     *         )
     *     ),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function addRound(Request $request)
    {
        Log::info('요일' . date('w'));
        try {
            // 유효성 검사
            $validatedData = $request->validate([
              'round' => 'required|string',
              'weekend' => 'required|boolean',
              'semester' => 'required|boolean',
              'bus_route_direction' => 'required|string',

            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }

        $route_id = BusRoute::where('weekend', $validatedData['weekend'])
            ->where('semester', $validatedData['semester'])
            ->where('bus_route_direction', $validatedData['bus_route_direction'])
            ->first();

        Log::info('라우트 아이디: ' . $route_id->id);

        try{
            BusRound::create([
                'round' => $validatedData['round'],
                'bus_route_id' => $route_id->id
            ]);
        }catch(\Exception $exception){
            return response()->json(['error' => $exception->getMessage()], 404);
        }
        return response()->json(['message' => '버스 회차가 추가 되었습니다.']);
    }




    /**
     * @OA\Get (
     *     path="/api/bus/round",
     *     tags={"버스"},
     *     summary="해당 버스 회차 가져오기",
     *     description="해당하는 id의 버스 회차 리스트 가져오기",
     *     @OA\RequestBody(
     *     description="가져오고 싶은 회차의 bus_route값",
     *     required=false,
     *         @OA\MediaType(
     *         mediaType="application/json",
     *             @OA\Schema (
     *                  @OA\Property (property="weekend", type="boolean", description="주말/평일", example=true),
     *                  @OA\Property (property="semester", type="boolean", description="학기/방학", example=true),
     *                  @OA\Property (property="bus_route_direction", type="string", description="버스 노선 ", example="B"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function getRound(Request $request)
    {
        $errorBox = [];
        try {
            // 유효성 검사//쿼리파라미터 형식
            $validatedData = $request->validate([
              'weekend' => 'required|boolean',
              'semester' => 'required|boolean',
              'bus_route_direction' => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['roundDate' => []], 404);
        }


        try {
            $RouteId = BusRoute::where('weekend', $validatedData['weekend'])
                                  ->where('semester', $validatedData['semester'])
                                  ->where('bus_route_direction', $validatedData['bus_route_direction'])
                                  ->pluck('id');

            if ($RouteId->isEmpty()) {
                throw new \Exception('해당하는 노선을 찾을 수 없습니다.');
            }

            $matchingRound = BusRound::where('bus_route_id', $RouteId)
                                    ->select('id', 'round')
                                    ->get();

            if ($matchingRound->isEmpty()) {
                throw new \Exception('해당하는 회차를 찾을 수 없습니다.');
            }
            return response()->json(['roundDate' => $matchingRound]);
        } catch (\Exception $exception) {
            return response()->json(['roundDate' => []]);
        }
    }


    /**
         * @OA\Get (
         * path="/api/bus/round/schedule/{id}",
         * tags={"버스"},
         * summary="해당 회차의 버스 시간표",
         * description="해당 회차의 버스 시간표를 확인 합니다",
         *
         *         description="해당 회차의 버스 시간표",
         *         @OA\Parameter(
         *           name="id",
         *           description="가져올 버스 회차의 아이디",
         *           required=true,
         *           in="path",
         *           @OA\Schema(type="integer"),
         *          ),
         *
         *  @OA\Response(response="200", description="Success"),
         *  @OA\Response(response="500", description="Fail"),
         * )
         */
    public function getRoundSchedule($id)
    {
        try {

            // bus_round_id에 해당하는 모든 bus_schedule 데이터를 조회합니다.
            $schedules = BusSchedule::where('bus_round_id', $id)->get();

            if ($schedules->isEmpty()) {
                return response()->json(['schedules' => []]);
            }
           
            //Log::info('조회된 스케줄: ', ['schedules' => $schedules->toArray()]);

            // 조회된 데이터를 JSON 형태로 반환합니다.
            return response()->json(['schedules' => $schedules]);
        } catch (\Exception $exception) {
            // 예외 발생 시 에러 메시지를 반환합니다.
            return response()->json(['schedules' => []]);
        }

    }

    /**
     * @OA\Delete (
     *     path="/api/bus/round/{id}",
     *     tags={"버스"},
     *     summary="버스 회차 삭제",
     *     description="버스 회차 삭제",
     *     @OA\Parameter(
     *           name="id",
     *           description="삭제할 버스 회차의 아이디",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function roundDestroy($id)
    {
        try {
            // 삭제할 버스 시간표를 찾습니다.
            $busRound = BusRound::findOrFail($id);

            // 버스 시간표를 삭제합니다.
            $busRound->delete();

            return response()->json(['message' => '버스 시간표가 성공적으로 삭제되었습니다.']);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }
    }


    /**
     * @OA\Get (
     *     path="/api/bus/round/appSchedule",
     *     tags={"버스"},
     *     summary="App 에서 해당 버스 회차, 스케줄 가져오기",
     *     description="App 해당하는 버스의 회차, 스케줄 리스트 가져오기",
     *     @OA\RequestBody(
     *     description="가져오고 싶은 회차의 bus_route값",
     *     required=false,
     *         @OA\MediaType(
     *         mediaType="application/json",
     *             @OA\Schema (
     *                  @OA\Property (property="weekend", type="boolean", description="주말/평일", example=true),
     *                  @OA\Property (property="semester", type="boolean", description="학기/방학", example=true),
     *                  @OA\Property (property="bus_route_direction", type="string", description="버스 노선 ", example="s_english"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Server Error"),
     * )
     */
    public function getRoundAndSchedule(Request $request)
    {
        try {
            // 유효성 검사//쿼리파라미터 형식
            $validatedData = Validator::make($request->query(),[
              'weekend' => 'required|boolean',
              'semester' => 'required|boolean',
              'bus_route_direction' => 'required|string',
            ])->validate();
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }


        try {
            $RouteId = BusRoute::where('weekend', $validatedData['weekend'])
                                  ->where('semester', $validatedData['semester'])
                                  ->where('bus_route_direction', $validatedData['bus_route_direction'])
                                  ->first();

            $matchingRound = BusRound::where('bus_route_id', $RouteId->id)
                                    ->select('id', 'round')
                                    ->pluck('id');
                                    Log::info('라운드드 아이디: ' . $matchingRound);
            
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }

        try{
            $matchingSchedule = BusSchedule::whereIn('bus_round_id', $matchingRound)->select('station', 'bus_time','bus_round_id')->get();
            //$exGroupedSchedules = $matchingSchedule->groupBy('bus_round_id');

              // 그룹화된 결과를 담을 배열 초기화
            $groupedSchedules = [];

            foreach ($matchingSchedule as $schedule) {
                // BusRound 모델을 사용하여 라운드 정보를 가져옴
                $round = BusRound::find($schedule->bus_round_id);
                // BusRound 모델의 round 값으로 그룹화된 결과에 추가
                $groupedSchedules[$round->round][] = [
                    'station' => $schedule->station,
                    'bus_time' => $schedule->bus_time,
                    'bus_round_id' => $schedule->bus_round_id,
                ];
            }

            return response()->json(['schedules' => $groupedSchedules]);
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }
}
