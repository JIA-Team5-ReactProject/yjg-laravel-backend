<?php

namespace App\Tasks;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\App;
use Kreait\Firebase\Exception\MessagingException;

class RestaurantWeekendNotification
{
    public function __invoke(): \Illuminate\Http\JsonResponse
    {
        $users = User::query()->where('push_enabled', true)->whereNot('fcm_token', null)->get();

        if($users->isNotEmpty()) {
            $tokens = [];

            // 알림 설정한 유저의 토큰을 $tokens 배열에 담음
            foreach ($users as $user) {
                $tokens[] = $user->fcm_token;
            }

            try {
                App::call(function(NotificationService $service) use ($tokens) {
                    $service->postNotificationMulticast('식사 신청 오픈', '주말 식사 신청이 오픈되었습니다.', $tokens, 'restaurant');
                });
            } catch (MessagingException) {
                return response()->json(['error' => '알림 전송에 실패하였습니다.'], 500);
            }
        }
        return response()->json(['message' => '알림이 발송되었습니다.']);
    }
}
