<?php
$BOT_TOKEN = "74093551:AAEFqTLmb8EbJPHIt4WRYc3DfMLRXNiKbrQ";

$authorized_numbers = [
    '+989121234567' => 'محمد',
    '+989151234567' => 'فاطمه',
    '+989391234567' => 'احمد'
];

// دریافت داده از تلگرام
$input = file_get_contents('php://input');
$update = json_decode($input, true);

// برای تست GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "🤖 Telegram Bot is running on Railway!";
    exit;
}

if ($update && isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    
    // اگر کاربر شماره فرستاد
    if (isset($message['contact'])) {
        $phone = normalizePhone($message['contact']['phone_number']);
        
        if (isset($authorized_numbers[$phone])) {
            $name = $authorized_numbers[$phone];
            sendMessage($chat_id, "✅ {$name} عزیز، خوش آمدید!");
        } else {
            sendMessage($chat_id, "❌ شما مجاز به استفاده از این ربات نیستید.");
        }
    }
    // اگر دستور /start فرستاد
    elseif (isset($message['text']) && $message['text'] == '/start') {
        $keyboard = [
            'keyboard' => [
                [
                    [
                        'text' => '📱 اشتراک گذاری شماره تلفن',
                        'request_contact' => true
                    ]
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ];
        
        $text = "🔒 برای استفاده از این ربات، لطفا شماره تلفن خود را به اشتراک بگذارید.\n\n";
        $text .= "روش کار:\n";
        $text .= "1. روی دکمه پایین کلیک کنید\n";
        $text .= "2. شماره خود را تأیید کنید";
        
        sendMessage($chat_id, $text, $keyboard);
    }
}

function normalizePhone($phone) {
    // حذف فاصله و کاراکترهای خاص
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    // نرمال‌سازی شماره ایرانی
    if (substr($phone, 0, 1) == '0') {
        $phone = '+98' . substr($phone, 1);
    }
    elseif (substr($phone, 0, 1) == '9' && substr($phone, 0, 1) != '+') {
        $phone = '+98' . $phone;
    }
    
    return $phone;
}

function sendMessage($chat_id, $text, $reply_markup = null) {
    global $BOT_TOKEN;
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $text
    ];
    
    if ($reply_markup) {
        $data['reply_markup'] = json_encode($reply_markup);
    }
    
    $url = "https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage";
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return $result;
}
?>