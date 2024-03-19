<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Resources\SemesterApplyResource;
use App\Models\RestaurantSemester;
use App\Models\RestaurantSemesterMealType;
use App\Models\SemesterMealType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

// 예외 처리

class RestaurantSemesterController extends Controller
{
    /**
     * @OA\Post (
     * path="/api/restaurant/semester",
     * tags={"식수"},
     * summary="식수 학기 신청",
     * description="식수 학기 신청을 처리합니다",
     *     @OA\RequestBody(
     *         description="학생 식사 신청 정보",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="payment", type="boolean", description="입금 확인", example=false),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function store(Request $request)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'meal_type' => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            $semesterMealType = SemesterMealType::where("meal_type", $validatedData["meal_type"])
            ->first();
            
            $user_id = auth('users')->id();

            $restaurantSemester = RestaurantSemester::create([
                'user_id' => $user_id
            ]);
            Log::info('유저 아이디: ' . $restaurantSemester->user_id);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }

        try {
            RestaurantSemesterMealType::create([
            'restaurant_semester_id' => $restaurantSemester->id,
            'semester_meal_type_id' => $semesterMealType->id
            ]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
        return response()->json(['message' => '식수 학기 신청이 완료되었습니다.']);
    }

    /**
         * @OA\Get (
         * path="/api/restaurant/semester/g/payment{id}",
         * tags={"식수"},
         * summary="식수 학기 신청 입금여부",
         * description="식수 학기 신청의 입금여부를 확인 합니다",
         *     @OA\Parameter(
         *           name="id",
         *           description="조회 할 식수 신청 아이디",
         *           required=true,
         *           in="path",
         *           @OA\Schema(type="integer"),
         *     ),
         *  @OA\Response(response="200", description="Success"),
         *  @OA\Response(response="500", description="Fail"),
         * )
         */
    public function getPayment($id)
    {
        try {
            $paymentData = RestaurantSemester::where('user_id', $id)->pluck('payment');
            return response()->json(['payment_data' => $paymentData]);
        } catch (\Exception $exception) {
            return response()->json(['error' => '페이먼트 데이터 조회 중 오류가 발생했습니다.'], 500);
        }
    }

       /**
     * @OA\Post (
     *     path="/api/restaurant/semester/p/payment/{id}",
     *     tags={"식수"},
     *     summary="학기 입금여부 수정",
     *     description="학기 입금여부를 수정",
     *      @OA\Parameter(
     *           name="id",
     *           description="확인할 식수신청 id",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *          ),
     *     @OA\RequestBody(
     *         description="수정할 입금여부(true,false), 신청id",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="payment", type="boolean", description="입금여부", example="true"),
     *              )
     *         )
     *     ),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="422", description="Validation Exception"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function setPayment(Request $request, $id)
    {
        try {
            // 유효성 검사
            $validatedData = $request->validate([
                'payment' => 'required|boolean',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            $apply_id = RestaurantSemester::findOrFail($id);
                $apply_id->payment = $validatedData['payment'];
                $apply_id->save();
            } catch (\Exception $exception) {
                return response()->json(['error' => $exception->getMessage()], 500);
            }
            if($validatedData['payment'] == true)
                return response()->json(['message' => '입금 확인 수정완료.']);
            else
            return response()->json(['message' => 'false값이 확인됨']);
    }

    /**
     * @OA\Delete (
     *     path="/api/restaurant/semester/delete/{id}",
     *     tags={"식수"},
     *     summary="학기 식수 신청 삭제",
     *     description="학기 식수 신청 삭제",
     *     @OA\Parameter(
     *           name="id",
     *           description="삭제할 학기 식수 신청 아이디",
     *           required=true,
     *           in="path",
     *           @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function delete($id)
    {
        try {
            $RestaurantSemester = RestaurantSemester::findOrFail($id);
            $RestaurantSemester->delete();

            return response()->json(['message' => '학기 식수 신청이 삭제되었습니다.']);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }



    /**
     * @OA\Get (
     *     path="/api/restaurant/semester/apply",
     *     tags={"식수"},
     *     summary="학기 식수 신청 리스트 가져오기",
     *     description="학기 식수 신청 리스트 가져오기",
     *     
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getRestaurantApply()
    {
        try{
            $applyData = RestaurantSemester::with('semesterMealType:id,meal_type,date', 'user:id,phone_number,name,student_id')->paginate(5);
            return $applyData;
            //return SemesterApplyResource::collection($applyData);
        }catch (\Exception $exception) {
            return response()->json(['applyData' => []]);
        }
    }


    /**
     * @OA\Get (
     * path="/api/restaurant/semester/show",
     * tags={"식수"},
     * summary="학기 식수 신청 리스트 검색",
     * description="학기 식수 신청 리스트 검색하기",
     *     @OA\RequestBody(
     *         description="찾고싶은 학생 이름",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                 @OA\Property (property="name", type="string", description="학생이름", example="권지훈"),
     *             )
     *         )
     *     ),
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function show(Request $request)
    {
        try{
            $name = $request->input('name');
            $applyData = RestaurantSemester::with('semesterMealType:id,meal_type,date', 'user:id,phone_number,name,student_id')
                ->whereHas('user', function ($query) use ($name) {
                    $query->where('name', 'like', '%' . $name . '%');
                })
                ->paginate(5);
            return $applyData;
            //return SemesterApplyResource::collection($applyData);
        } catch (\Exception $exception) {
            return response()->json(['applyData' => []]);
        }
    }   

//if (strlen($request->type) == 1 && preg_match('/^[A-Z]$/', $request->type)) 알파벳 대문자 1개일때
}
