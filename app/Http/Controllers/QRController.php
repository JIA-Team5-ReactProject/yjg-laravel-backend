<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\UsedQRCode;

class QRController extends Controller
{
    public function generator(Request $request)
    {
        $admin = Admin::findOrFail(2); //$request->id

        $adminName = $admin->name;

        //QR 생성
        return QrCode::format('svg')->size(300)->generate($adminName);
    }
}
