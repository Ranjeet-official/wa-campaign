pdf.blade.php
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #007aff; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #007aff; }
        .header p { margin: 4px 0 0; color: #666; font-size: 11px; }
        .msg-row { margin-bottom: 10px; width: 100%; }
        .msg-bubble {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 8px;
            max-width: 70%;
        }
        .msg-user { background: #007aff; color: #fff; float: right; }
        .msg-bot { background: #e9ecef; color: #333; float: left; }
        .msg-time { font-size: 9px; opacity: 0.7; margin-top: 3px; }
        .clear { clear: both; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Chat Conversation</h2>
        <p>
            Visitor: {{ $userInfo->user_name ?? 'Guest Visitor' }}
            @if($userInfo->user_email) | {{ $userInfo->user_email }} @endif
            @if($userInfo->user_phone) | {{ $userInfo->user_phone }} @endif
        </p>
        <p>Generated on: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    @foreach($conversations as $msg)
        <div class="msg-row">
            <div class="msg-bubble {{ $msg->sender === 'user' ? 'msg-user' : 'msg-bot' }}">
                <div>{{ $msg->message }}</div>
                <div class="msg-time">{{ \Carbon\Carbon::parse($msg->created_at)->format('d M Y, h:i A') }}</div>
            </div>
            <div class="clear"></div>
        </div>
    @endforeach

</body>
</html>