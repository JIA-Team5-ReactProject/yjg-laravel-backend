<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\UsedQRCode;
use Illuminate\Support\Facades\Log;

class QRController extends Controller
{
    /**
     * @OA\GET (
     *     path="/api/admin/qr",
     *     tags={"관리자"},
     *     summary="QR코드 생성",
     *     description="QR코드 생성해서 이미지로 반환",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Error"),
     * )
     */
    public function generator(Request $request)
    {
        $user = User::findOrFail(1); //$request->id

        $userName = $user->name;
        $userId = $user->id;

        $userData = $userName . ' ' . $userId;
        Log::info('유저 데이터: ' . $userData);

        //QR생성  웹에서 볼려면'svg'
        return QrCode::format('png')->size(300)->encoding('UTF-8')->generate($userData);
    }

    // public function generator(Request $request)
    // {
    //     $user = User::findOrFail(1); //$request->id

        
    //     $userId = $user->id;

    //     //QR 생성
    //     return QrCode::format('svg')->size(300)->generate($userId);
    // }
}
