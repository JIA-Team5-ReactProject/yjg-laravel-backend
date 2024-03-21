<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Resources\SemesterApplyResource;
use App\Models\RestaurantSemester;
use App\Models\RestaurantSemesterMealType;
use App\Models\SemesterMealType;
use App\Models\User;
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
            $validatedData = $request->validate([
                'meal_type' => 'required|string',
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        try {
            $user_id = auth('users')->id();
            $restaurantSemester = RestaurantSemester::create([
                'user_id' => $user_id
            ]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }

        $semesterMealType = SemesterMealType::where("meal_type", $validatedData["meal_type"])
                                        ->first();

        
            RestaurantSemesterMealType::create([
            'restaurant_semester_id' => $restaurantSemester->id,
            'semester_meal_type_id' => $semesterMealType->id
            ]);
    
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
    public function getPayment()
    {
        $user_id = auth('users')->id();
        Log::info('유저 아이디: ' . $user_id);
        try {
            $paymentData = RestaurantSemester::where('user_id', $user_id)->pluck('payment');
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
     *     summary="학기 식수 신청 리스트 가져오기(삭제예정)",
     *     description="학기 식수 신청 리스트 가져오기",
     *     
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Fail"),
     * )
     */
    public function getRestaurantApply()
    {
        try{
            $applyData = RestaurantSemester::with('semesterMealType:id,meal_type', 'user:id,phone_number,name,student_id')->paginate(5);
            return $applyData;
            //return SemesterApplyResource::collection($applyData);
        }catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()]);
        }
    }


    /**
     * @OA\Get (
     * path="/api/restaurant/semester/show",
     * tags={"식수"},
     * summary="학기 식수 신청 리스트 보기, 검색",
     * description="학기 식수 신청 리스트 보기, 검색하기",
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
        try {
            $name = $request->input('name');
            $allData = RestaurantSemester::with('semesterMealType:id,meal_type', 'user:id,phone_number,name,student_id');
            
            if ($name === null || $name === '') {
                $applyData = $allData->paginate(5);
            } else {
                $applyData = $allData->whereHas('user', function ($allData) use ($name) {
                    $allData->where('name', 'like', '%' . $name . '%');
                })->paginate(5);
            }
    
            return $applyData;
        } catch (\Exception $exception) {
            return response()->json(['applyData' => []]);
        }
    }

    
    /**
     * @OA\Get (
     * path="/api/restaurant/semester/show/user",
     * tags={"식수"},
     * summary="학기 식수 유저 정보",
     * description="학기 식수 유조 정보 확인",
     *    
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function showUser(){
        try{
            $user_id = auth('users')->id();
            $userData = User::select('id', 'phone_number', 'name', 'student_id')->where('id', $user_id)->first();
            
            return response()->json(['userData' => $userData]);
        }catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()]);
        }
    }

/**
     * @OA\Get (
     * path="/api/restaurant/semester/show/user/after",
     * tags={"식수"},
     * summary="학기 식수 유저 정보(신청 후)",
     * description="학기 식수 유조 정보 확인(신청 후)",
     *    
     *  @OA\Response(response="200", description="Success"),
     *  @OA\Response(response="500", description="Fail"),
     * )
     */
    public function showUserAfter(){
        try{
            $user_id = auth('users')->id();
            $allData = RestaurantSemester::with('semesterMealType:id,meal_type', 'user:id,phone_number,name,student_id');
    
    
            $applyData = $allData->where('user_id', $user_id)->paginate(5);
            
            return response()->json(['userData' => $applyData ]);
        }catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()]);
        }
    }
}
