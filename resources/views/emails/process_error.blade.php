<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<p>Could not process {{$contract_title}} . Please see the contract <a href="{{ $contract_detail_url }}"> here</a>
</p>

<div>
    Document processing started  At {{$start_time}}(GMT)
</div>
<div>
    Error: {{$error}}
</div>
</body>
</html>