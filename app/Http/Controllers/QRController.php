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
     *     path="/api/user/qr",
     *     tags={"QR코드"},
     *     summary="QR코드 생성",
     *     description="QR코드 생성해서 이미지로 반환",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Validation Error"),
     * )
     */
    public function generator(Request $request)
    {
        $user_id = auth('users')->id();
        $userData = User::select('id', 'email')->where('id', $user_id)->first();

        // 사용자 정보를 문자열로 변환하여 QR 코드에 사용합니다.
        //QR생성  웹에서 볼려면'svg'
        return QrCode::format('svg')->size(300)->encoding('UTF-8')->generate($userData);
    }

    public function check(Request $request)
    {
        
    }
}
