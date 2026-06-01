<?php
// ============================================================
//  api.php  ·  Hejaili Digital Repository
//  مزامنة بيانات الأقسام مع التحقق من تسجيل الدخول (Firebase)
//  انسخ هذا الملف كاملاً والصقه مكان القديم — لا تحتاج تعديل أي سطر.
// ============================================================

header('Content-Type: application/json');

// ===== الإعدادات (مضبوطة لموقعك، لا حاجة لتغييرها) =====
$PROJECT_ID  = "hejaili-ec4b9";
$WEB_API_KEY = "AIzaSyDF3GHcEs-By2HQBpF0905LLdV6lV2wzO4";

// النطاقات المسموح لها بالاتصال (نطاقك فقط — وليس * )
$ALLOWED_ORIGINS = [
    "https://hejaili.com",
    "https://www.hejaili.com"
];

// السماح للنطاق القادم إن كان ضمن القائمة
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $ALLOWED_ORIGINS, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// طلب الفحص المسبق (CORS preflight)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }

// ===== قراءة رمز الدخول من الترويسة: Authorization: Bearer <token> =====
function getBearerToken() {
    $auth = '';
    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $k => $v) {
            if (strtolower($k) === 'authorization') { $auth = $v; break; }
        }
    }
    if ($auth === '' && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if ($auth === '' && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if (preg_match('/Bearer\s+(.+)/i', $auth, $m)) return trim($m[1]);
    return '';
}

// ===== التحقق من صحة الرمز لدى Google والتأكد أنه يخص مشروعك =====
function verifyFirebaseToken($idToken, $webApiKey) {
    if ($idToken === '') return false;
    $url = "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=" . $webApiKey;
    $payload = json_encode(['idToken' => $idToken]);

    // المحاولة الأولى: cURL (المتوفر في أغلب الاستضافات)
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $res  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        // البديل: file_get_contents إن لم يتوفر cURL
        $ctx = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => $payload,
            'timeout' => 10,
            'ignore_errors' => true,
        ]]);
        $res  = @file_get_contents($url, false, $ctx);
        $code = 200; // نتحقق من المحتوى أدناه
    }

    if (!$res) return false;
    $data = json_decode($res, true);
    return isset($data['users'][0]['localId']) ? $data['users'][0] : false;
}

// ===== تطبيق الحماية: لا يمر أي طلب بدون رمز دخول صحيح =====
$user = verifyFirebaseToken(getBearerToken(), $WEB_API_KEY);
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// (اختياري) لو أردت قصر الوصول على بريد معيّن فقط، أزل علامات التعليق:
// $allowedEmails = ['family@hejaili.com'];
// if (!in_array(($user['email'] ?? ''), $allowedEmails, true)) {
//     http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit;
// }

// ===== المستخدم موثّق: ننفّذ القراءة/الكتابة =====
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['module']) && isset($input['data'])) {
        $module = preg_replace('/[^a-zA-Z0-9_-]/', '', $input['module']);
        if ($module === '') { http_response_code(400); echo json_encode(['error' => 'Bad Request']); exit; }
        file_put_contents("data_$module.json", json_encode($input['data']));
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request']);
    }
} else if ($method === 'GET') {
    $module = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['module'] ?? '');
    if ($module === '') { http_response_code(400); echo json_encode(['error' => 'Bad Request']); exit; }
    $file = "data_$module.json";
    echo file_exists($file) ? file_get_contents($file) : json_encode([]);
}
?>