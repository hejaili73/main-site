<?php
// السماح بالاتصال والمزامنة
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// 🔒 كلمة المرور السرية لضمان عدم تلاعب أي شخص خارجي ببياناتك
$SECRET_KEY = "Hejaili_2026_Secure";

$method = $_SERVER['REQUEST_METHOD'];

// 1. استقبال وحفظ البيانات القادمة من المتصفح (من اللابتوب أو الجوال)
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // التحقق من الرقم السري واسم القسم
    if (isset($input['key']) && $input['key'] === $SECRET_KEY && isset($input['module'])) {
        $module = preg_replace('/[^a-zA-Z0-9_-]/', '', $input['module']);
        $file_path = 'data_' . $module . '.json'; // سينشئ ملف data_travel.json أو data_thawab.json
        
        file_put_contents($file_path, json_encode($input['data']));
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized Access']);
    }
} 
// 2. إرسال البيانات إلى المتصفح عند فتح الموقع (المزامنة)
else if ($method === 'GET') {
    $key = isset($_GET['key']) ? $_GET['key'] : '';
    $module = isset($_GET['module']) ? $_GET['module'] : '';
    
    if ($key === $SECRET_KEY && $module !== '') {
        $module = preg_replace('/[^a-zA-Z0-9_-]/', '', $module);
        $file_path = 'data_' . $module . '.json';
        
        if (file_exists($file_path)) {
            echo file_get_contents($file_path);
        } else {
            echo json_encode([]); // إرسال بيانات فارغة في أول استخدام
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized Access']);
    }
}
?>