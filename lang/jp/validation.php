<?php

return [
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

