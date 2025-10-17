<?php
/**
 * Phương pháp 3: Bypass qua hook modification
 * 
 * Sửa đổi các hook functions để bỏ qua kiểm tra license
 */

$warranty_file = '/workspace/warranty_management.php';

// Đọc nội dung file
$content = file_get_contents($warranty_file);

// Bypass function warranty_management_appint
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

// Bypass function warranty_management_preactivate
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

// Thực hiện thay thế
$content = str_replace($old_appint, $new_appint, $content);
$content = str_replace($old_preactivate, $new_preactivate, $content);

// Ghi file đã sửa đổi
if (file_put_contents($warranty_file, $content)) {
    echo "✓ Đã sửa đổi các hook functions để bypass\n";
} else {
    echo "✗ Không thể sửa đổi file\n";
}
?>