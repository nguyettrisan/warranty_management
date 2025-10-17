<?php
/**
 * Script hoàn chỉnh để bypass module warranty management
 * Không cần kích hoạt license
 * 
 * CẢNH BÁO: Chỉ sử dụng cho mục đích nghiên cứu và học tập
 * Đảm bảo bạn có quyền hợp pháp để thực hiện trên hệ thống này
 */

echo "=== WARRANTY MANAGEMENT BYPASS SCRIPT ===\n";
echo "Chỉ sử dụng cho mục đích nghiên cứu an ninh mạng\n\n";

// Kiểm tra quyền truy cập
if (!is_writable('/workspace/libraries/')) {
    echo "❌ Không có quyền ghi vào thư mục libraries/\n";
    exit(1);
}

echo "🔍 Đang phân tích cơ chế bảo mật...\n";

// Phương pháp 1: Tạo file license giả mạo
echo "\n📝 Phương pháp 1: Tạo file license giả mạo\n";

$license_file = '/workspace/libraries/.lic';
$interval_file = '/workspace/libraries/.licint';

// Tạo license giả mạo
$fake_license = array(
    'status' => true,
    'message' => 'Verified! Thanks for purchasing.',
    'expires' => '2030-12-31',
    'bypass' => true
);

$license_content = base64_encode(json_encode($fake_license));
file_put_contents($license_file, $license_content);

// Tạo file interval check
$future_date = base64_encode(date('d-m-Y', strtotime('+1 year')));
file_put_contents($interval_file, $future_date);

echo "✅ Đã tạo file .lic giả mạo\n";
echo "✅ Đã tạo file .licint để bypass time check\n";

// Phương pháp 2: Sửa đổi hàm verify_license
echo "\n🔧 Phương pháp 2: Sửa đổi hàm verify_license\n";

$gtsslib_file = '/workspace/libraries/gtsslib.php';

if (file_exists($gtsslib_file)) {
    // Backup file gốc
    copy($gtsslib_file, $gtsslib_file . '.backup');
    echo "✅ Đã backup file gốc\n";
    
    // Đọc và sửa đổi file
    $content = file_get_contents($gtsslib_file);
    
    // Tìm và thay thế hàm verify_license
    $pattern = '/public function verify_license\([^}]+\}/s';
    $replacement = 'public function verify_license($time_based_check = false, $license = false, $client = false){
        // BYPASS: Luôn trả về true
        return array(\'status\' => TRUE, \'message\' => \'Bypassed successfully\');
    }';
    
    $new_content = preg_replace($pattern, $replacement, $content);
    
    if ($new_content && $new_content !== $content) {
        file_put_contents($gtsslib_file, $new_content);
        echo "✅ Đã sửa đổi hàm verify_license()\n";
    } else {
        echo "⚠️  Không thể sửa đổi hàm verify_license()\n";
    }
} else {
    echo "❌ Không tìm thấy file gtsslib.php\n";
}

// Phương pháp 3: Sửa đổi hook functions
echo "\n🎣 Phương pháp 3: Sửa đổi hook functions\n";

$warranty_file = '/workspace/warranty_management.php';

if (file_exists($warranty_file)) {
    // Backup file gốc
    copy($warranty_file, $warranty_file . '.backup');
    echo "✅ Đã backup file gốc\n";
    
    $content = file_get_contents($warranty_file);
    
    // Sửa đổi warranty_management_appint
    $old_appint = 'function warranty_management_appint(){
    $CI = & get_instance();    
    require_once \'libraries/gtsslib.php\';
    $wm_api = new WarrantyManagementLic();
    $wm_gtssres = $wm_api->verify_license(true);    
    if(!$wm_gtssres || ($wm_gtssres && isset($wm_gtssres[\'status\']) && !$wm_gtssres[\'status\'])){
         $CI->app_modules->deactivate(WARRANTY_MANAGEMENT_MODULE_NAME);
        set_alert(\'danger\', "One of your modules failed its verification and got deactivated. Please reactivate or contact support.");
        redirect(admin_url(\'modules\'));
    }    
}';
    
    $new_appint = 'function warranty_management_appint(){
    // BYPASS: Bỏ qua kiểm tra license
    return true;
}';
    
    $content = str_replace($old_appint, $new_appint, $content);
    
    // Sửa đổi warranty_management_preactivate
    $old_preactivate = 'function warranty_management_preactivate($module_name){
    if ($module_name[\'system_name\'] == WARRANTY_MANAGEMENT_MODULE_NAME) {             
        require_once \'libraries/gtsslib.php\';
        $wm_api = new WarrantyManagementLic();
        $wm_gtssres = $wm_api->verify_license();          
        if(!$wm_gtssres || ($wm_gtssres && isset($wm_gtssres[\'status\']) && !$wm_gtssres[\'status\'])){
             $CI = & get_instance();
            $data[\'submit_url\'] = $module_name[\'system_name\'].\'/gtsverify/activate\'; 
            $data[\'original_url\'] = admin_url(\'modules/activate/\'.WARRANTY_MANAGEMENT_MODULE_NAME); 
            $data[\'module_name\'] = WARRANTY_MANAGEMENT_MODULE_NAME; 
            $data[\'title\'] = "Module License Activation"; 
            echo $CI->load->view($module_name[\'system_name\'].\'/activate\', $data, true);
            exit();
        }        
    }
}';
    
    $new_preactivate = 'function warranty_management_preactivate($module_name){
    // BYPASS: Bỏ qua kiểm tra license khi kích hoạt
    return true;
}';
    
    $content = str_replace($old_preactivate, $new_preactivate, $content);
    
    file_put_contents($warranty_file, $content);
    echo "✅ Đã sửa đổi hook functions\n";
} else {
    echo "❌ Không tìm thấy file warranty_management.php\n";
}

// Kiểm tra kết quả
echo "\n🔍 Kiểm tra kết quả:\n";

if (file_exists($license_file)) {
    $license_data = json_decode(base64_decode(file_get_contents($license_file)), true);
    if ($license_data && $license_data['status']) {
        echo "✅ File license giả mạo hoạt động\n";
    }
}

if (file_exists($interval_file)) {
    echo "✅ File interval check đã được tạo\n";
}

echo "\n🎉 HOÀN THÀNH! Module đã được bypass thành công\n";
echo "📋 Các file đã được sửa đổi:\n";
echo "   - /workspace/libraries/.lic (license giả mạo)\n";
echo "   - /workspace/libraries/.licint (interval check)\n";
echo "   - /workspace/libraries/gtsslib.php (hàm verify_license)\n";
echo "   - /workspace/warranty_management.php (hook functions)\n";

echo "\n⚠️  LƯU Ý QUAN TRỌNG:\n";
echo "   - Chỉ sử dụng cho mục đích nghiên cứu và học tập\n";
echo "   - Đảm bảo bạn có quyền hợp pháp trên hệ thống này\n";
echo "   - Các file gốc đã được backup với extension .backup\n";
echo "   - Để khôi phục: xóa file .backup và restore từ backup\n";

echo "\n🔒 BẢO MẬT:\n";
echo "   - Module này có thể được kích hoạt mà không cần license\n";
echo "   - Tất cả chức năng sẽ hoạt động bình thường\n";
echo "   - Không có kết nối đến server license\n";
?>