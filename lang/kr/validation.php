<?php

return [
//    'attributes' => [
//        'name' => '이름',
//        'student_id' => '학번',
//        'phone_number' => '휴대폰 번호',
//        'email' => '이메일',
//        'password' => '비밀번호',
//        'admin_id' => '관리자 아이디',
//        'salon_privilege' => '관리자 권한',
//        'admin_privilege' => '행정 권한',
//        'restaurant_privilege' => '식당 권한',
//        'approve' => '승인 여부',
//        'new_password' => '새로운 비밀번호',
//    ],
//    // Type
//    'numeric' => ':attribute 은(는) 숫자이어야 합니다.',
//    'string' => ':attribute 은(는) 문자열이어야 합니다.',
//    'boolean' => ':attribute 은(는) boolean 타입이어야 합니다.',
//    'array' => ':attribute 는 배열 타입이어야 합니다.',
//
//    // rule
//    'unique' => '이미 사용 중인 :attribute 입니다.',
//    'required' => ':attribute 를 입력해주세요.',
//    'required_with' => ':attribute 를 입력해주세요.',
//    'email' => ':attribute 형식을 확인하세요.',
//    'in' => ':attribute 의 값은 허용되지 않는 값입니다.',
//    'date_format' => ':attribute 값이 날짜(시간) 형식과 일치하지 않습니다.',
//    'date' => ':attribute 의 값은 날짜 형식이어야 합니다.',
//    'exists' => '해당하는 :attribute 이(가) 존재하지 않습니다.',
//    'current_password' => '기존 비밀번호가 일치하지 않습니다.',
    'attributes' => [
        'name' => '名前',
        'student_id' => '学番',
        'phone_number' => '電話番号',
        'email' => 'メール',
        'password' => '暗証番号',
        'admin_id' => '管理者のID',
        'salon_privilege' => '管理者の権限',
        'admin_privilege' => '行政の権限',
        'restaurant_privilege' => '食堂の権限',
        'approve' => '承認の可否',
        'new_password' => '新しい暗証番号',
    ],
    // Type
    'numeric' => ':attribute は数字である必要があります。',
    'string' => ':attribute は文字列である必要があります。',
    'boolean' => ':attribute はブーリアンタイプである必要があります。',
    'array' => ':attribute は配列である必要があります。',

    // rule
    'unique' => 'すでに使われている:attributeです。',
    'required' => ':attributeを入力してください',
    'required_with' => ':attributeを入力してください',
    'email' => ':attribute形式を確認してください',
    'in' => ':attributeの値は許容されない値です。',
    'date_format' => ':attributeの値が定義した日付(時間)の形式と一致しません。',
    'date' => ':attributeの値は日付形式である必要があります。',
    'exists' => '該当する:attributeが存在しません。',
    'current_password' => '既存のパスワードが一致しません。',
];

