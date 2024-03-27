<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="/resources/css/app.css">
</head>
<body>
    <div id="header" style="background-color: #2596be; text-align: center; padding: 1.5%">
        <img src="https://d214004cc270e2.cloudfront.net/school-name.png"  alt="school_logo"/>
    </div>
    <div id="content" style="text-align: center; padding: 3%;">
        <h3>인증번호를 확인해주세요.</h3>
        <p style="font-size: 2rem"><strong>{{$secret}}</strong></p>
        <p>비밀번호 초기화를 위한 인증번호입니다. 10분 내에 인증번호를 입력해주세요.</p>
    </div>
</body>
</html>
