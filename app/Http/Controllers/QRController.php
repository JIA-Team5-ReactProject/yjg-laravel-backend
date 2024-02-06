<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\UsedQRCode;

class QRController extends Controller
{
    public function generator(Request $request) { 
        $admin = Admin::findOrFail(1); //임시  //$request->id

        $adminName = $admin->name;
        
        //QR 생성
        $qrCode = QrCode::format('svg')->size(300)->generate($adminName);

        // 생성된 QR 코드 및 유효시간을 응답으로 보냄
        return response()->json(['qrCode' => $qrCode], 200)
        ->header('Content-Type', 'application/json');
    }

    public function invalidateQR(Request $request)
    {
        // 여기에서 프론트엔드에서 전달된 QR 코드 정보를 받아옴
        $qrCode = $request->input('qrCode');

        // QR 코드를 사용된 것으로 기록
        $this->markCodeAsUsed($qrCode);

        return response()->json(['message' => 'QR 코드가 무효화되었습니다.'], 200)
            ->header('Content-Type', 'application/json');
    }

    private function markCodeAsUsed($qrCode)
    {
        // 사용된 QR 코드 기록
        UsedQRCode::create([
            'used_code' => $qrCode,
        ]);
    }
}
