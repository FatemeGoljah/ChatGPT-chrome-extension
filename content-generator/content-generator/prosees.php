<?php
require_once('../../wp-load.php');

// بررسی اتصال به دیتابیس
try {
    $db = new PDO("mysql:host=localhost;dbname=omir1111_register", "omir1111_ADfateme", "7!nw5rFQUVDh");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

//if (!isset($_POST['register_form_nonce']) || !wp_verify_nonce($_POST['register_form_nonce'], 'register_form_action')) {
    //die('Security check failed.');
//}

//error_log('Final price: ' . $_POST['final_price']);

if (isset($_POST['firstname']) && 
    isset($_POST['lastname']) && 
    isset($_POST['latest_diploma']) && 
    isset($_POST['d_brith']) && 
    isset($_POST['m_brith']) && 
    isset($_POST['y_brith']) && 
    isset($_POST['b_country']) && 
    isset($_POST['b_city']) && 
    isset($_POST['sex']) &&
    (isset($_FILES['mainimage']) || isset($_POST['mainimage_notready'])) 
    //&&
    //(isset($_FILES['spouse_mainimage']) || isset($_POST['spouse_mainimage_notready']))//&&
//

) {
    // پاکسازی ورودی‌های فرم
    $firstname = sanitize_text_field($_POST['firstname']);
    $lastname = sanitize_text_field($_POST['lastname']);
    $latest_diploma = sanitize_text_field($_POST['latest_diploma']);
    $d_brith = sanitize_text_field($_POST['d_brith']);
    $m_brith = sanitize_text_field($_POST['m_brith']);
    $y_brith = sanitize_text_field($_POST['y_brith']);
    $b_country = sanitize_text_field($_POST['b_country']);
    $b_city = sanitize_text_field($_POST['b_city']);
    $sex = sanitize_text_field($_POST['sex']);
    $mainimage_notready = isset($_POST['mainimage_notready']) ? 1 : 0;
    $spouce_us_citizen = isset($_POST['spouce_us_citizen']) ? 'yes' : 'no';
    $marriage_status = isset($_POST['marriage_status']) ? sanitize_text_field($_POST['marriage_status']) : '';
    $number_of_children = isset($_POST['number_of_children']) ? sanitize_text_field($_POST['number_of_children']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phonenumber = isset($_POST['phonenumber']) ? sanitize_text_field($_POST['phonenumber']) : '';
    $spouse_fname = isset($_POST['spouse_fname']) ? sanitize_text_field($_POST['spouse_fname']) : '';
    $spouse_lname = isset($_POST['spouse_lname']) ? sanitize_text_field($_POST['spouse_lname']) : '';
    $spouse_latest_diploma = isset($_POST['spouse_latest_diploma']) ? sanitize_text_field($_POST['spouse_latest_diploma']) : '';
    $spouse_d_brith = isset($_POST['spouse_d_brith']) ? sanitize_text_field($_POST['spouse_d_brith']) : '';
    $spouse_m_brith = isset($_POST['spouse_m_brith']) ? sanitize_text_field($_POST['spouse_m_brith']) : '';
    $spouse_y_brith = isset($_POST['spouse_y_brith']) ? sanitize_text_field($_POST['spouse_y_brith']) : '';
    $spouse_b_country = isset($_POST['spouse_b_country']) ? sanitize_text_field($_POST['spouse_b_country']) : '';
    $spouse_b_city = isset($_POST['spouse_b_city']) ? sanitize_text_field($_POST['spouse_b_city']) : '';
    $spouse_sex = isset($_POST['spouse_sex']) ? sanitize_text_field($_POST['spouse_sex']) : '';
    $twochansesignup = isset($_POST['twochansesignup']) ? 1 : 0;
    $spouse_mainimage_notready = isset($_POST['spouse_mainimage_notready']) ? 1 : 0;
    $spouse_mainimage = isset($_FILES['spouse_mainimage']) && $_FILES['spouse_mainimage']['error'] == UPLOAD_ERR_OK;
    
    $mainimage = isset($_FILES['$mainimage']) && $_FILES['$mainimage']['error'] == UPLOAD_ERR_OK;
    $r_country = isset($_POST['r_country']) ? sanitize_text_field($_POST['r_country']) : '';
    $r_state = '';
$r_ostan_text = '';

// بررسی و پاک‌سازی ورودی‌های POST
if (isset($_POST['r_state'])&& $r_country==="IRN") {
    $r_state = sanitize_text_field($_POST['r_state']);
} elseif (isset($_POST['r_ostan_text'])) {
    $r_ostan_text = sanitize_text_field($_POST['r_ostan_text']);
}

// اگر کشور ایران باشد و فیلد انتخابی استان (`r_state`) خالی باشد، فیلد متنی (`r_ostan_text`) را به‌عنوان استان استفاده کنید
if ($r_state === '' && !empty($r_ostan_text)) {
    $r_state = $r_ostan_text;
}

    
    $r_city = isset($_POST['r_city']) ? sanitize_text_field($_POST['r_city']) : '';
    $postalcode = isset($_POST['postalcode']) ? sanitize_text_field($_POST['postalcode']) : '';
    $nopostalcode = isset($_POST['nopostalcode']) ? 1 : 0;
    $r_address = isset($_POST['r_address']) ? sanitize_text_field($_POST['r_address']) : '';


    
    
    for ($i = 1; $i <= 8; $i++) {
    $fname_key = "child_{$i}_fname";
    $lname_key = "child_{$i}_lname";
    $d_brith_key = "child_{$i}_d_brith";
    $m_brith_key = "child_{$i}_m_brith";
    $y_brith_key = "child_{$i}_y_brith";
    $b_country_key = "child_{$i}_b_country";
    $b_city_key = "child_{$i}_b_city";
    $sex_key = "child_{$i}_sex";
    $mainimage_key = "child_{$i}_mainimage";
    $mainimage_notready_key = "child_{$i}_mainimage_notready";

    ${"child_{$i}_fname"} = isset($_POST[$fname_key]) ? sanitize_text_field($_POST[$fname_key]) : '';
    ${"child_{$i}_lname"} = isset($_POST[$lname_key]) ? sanitize_text_field($_POST[$lname_key]) : '';
    ${"child_{$i}_d_brith"} = isset($_POST[$d_brith_key]) ? sanitize_text_field($_POST[$d_brith_key]) : '';
    ${"child_{$i}_m_brith"} = isset($_POST[$m_brith_key]) ? sanitize_text_field($_POST[$m_brith_key]) : '';
    ${"child_{$i}_y_brith"} = isset($_POST[$y_brith_key]) ? sanitize_text_field($_POST[$y_brith_key]) : '';
    ${"child_{$i}_b_country"} = isset($_POST[$b_country_key]) ? sanitize_text_field($_POST[$b_country_key]) : '';
    ${"child_{$i}_b_city"} = isset($_POST[$b_city_key]) ? sanitize_text_field($_POST[$b_city_key]) : '';
    ${"child_{$i}_sex"} = isset($_POST[$sex_key]) ? sanitize_text_field($_POST[$sex_key]) : '';
    ${"child_{$i}_mainimage_notready"} = isset($_POST[$mainimage_notready_key]) ? 1 : 0;
    ${"child_{$i}_mainimage"} = isset($_FILES[$mainimage_key]) && $_FILES[$mainimage_key]['error'] == UPLOAD_ERR_OK;
}

 



    // متغیرهای مربوط به تصویر
    $uploadOk = 1;

    // ایجاد پوشه با شماره سفارش
    $target_dir = "uploads/";
    $orderDir = $target_dir . 'order_';
    
    
    
    // بررسی آپلود فایل
if (
    (!$mainimage_notready && isset($_FILES['mainimage']) && $_FILES['mainimage']['error'] === UPLOAD_ERR_OK) ||
    $mainimage_notready ||
      (!$child_1_mainimage && isset($_FILES['mainimage']) && $_FILES['mainimage']['error'] === UPLOAD_ERR_OK) ||
    $child_1_mainimage_notready ||
     (!$child_2_mainimage && isset($_FILES['mainimage']) && $_FILES['mainimage']['error'] === UPLOAD_ERR_OK) ||
    $child_2_mainimage_notready ||
     (!$child_3_mainimage && isset($_FILES['mainimage']) && $_FILES['mainimage']['error'] === UPLOAD_ERR_OK) ||
    $child_3_mainimage_notready ||
     (!$child_4_mainimage && isset($_FILES['mainimage']) && $_FILES['mainimage']['error'] === UPLOAD_ERR_OK) ||
    $child_4_mainimage_notready ||
     (!$child_5_mainimage && isset($_FILES['mainimage']) && $_FILES['mainimage']['error'] === UPLOAD_ERR_OK) ||
    $child_5_mainimage_notready ||
     (!$child_6_mainimage && isset($_FILES['mainimage']) && $_FILES['mainimage']['error'] === UPLOAD_ERR_OK) ||
    $child_6_mainimage_notready ||
     (!$child_7_mainimage && isset($_FILES['mainimage']) && $_FILES['mainimage']['error'] === UPLOAD_ERR_OK) ||
    $child_7_mainimage_notready ||
     (!$child_8_mainimage && isset($_FILES['mainimage']) && $_FILES['mainimage']['error'] === UPLOAD_ERR_OK) ||
    $child_8_mainimage_notready ||
    
        (!$spouse_mainimage_notready && isset($_FILES['spouse_mainimage']) && $_FILES['spouse_mainimage']['error'] === UPLOAD_ERR_OK) ||
    $spouse_mainimage_notready
) {
 

        

           /*
           // افزودن محصول به سبد خرید
        $product_id = 1701;
        $quantity = 1;
        $order->add_product(wc_get_product($product_id), $quantity);

        // تنظیم جزئیات مشتری
        $order->set_billing_first_name($firstname);
        $order->set_billing_last_name($lastname);
        $order->set_billing_email($email);
        $order->set_billing_phone($phonenumber);

        // ثبت سفارش
        $order->calculate_totals();
        $order->save();

        // دریافت شماره سفارش و وضعیت آن
        $order_id = $order->get_id(); 
        $order_number = $order->get_order_number();
        $order_status = $order->get_status(); // وضعیت به صورت کد


        // هدایت کاربر به صفحه پرداخت
        $custom_checkout_url = 'https://applyno.com/checkout'; // URL صفحه اختصاصی
        $custom_checkout_url = esc_url($custom_checkout_url);
        
     
        
        
        */
      
    // خالی کردن سبد خرید
WC()->cart->empty_cart();  
      
      
    // دریافت اطلاعات از فرم

// تعیین شناسه محصول بر اساس شرایط
if ($marriage_status === "MARRIED" && $twochansesignup === 0) {
    $product_id = 1714; // محصول متاهل
} elseif ($twochansesignup === 1 && $marriage_status === "MARRIED") {
    $product_id = 1715; // محصول فعال
} else {
    $product_id = 1713; // محصول مجرد
}

 


    if ($number_of_children !== "127") {
                    // افزودن محصولات فرزندان
        for ($i = 0; $i < $number_of_children; $i++) {
    WC()->cart->add_to_cart(1716); // اضافه کردن محصول فرزند

} 


             
         }
         
         
         
         $address = array(
	'first_name' => $firstname,
	'last_name'  => $lastname,
	'email'      => $email,
	'phone'      => $phonenumber,
	'address_1'  => $r_address,
	'city'       => $r_city,
	'state'      => $r_state,
	'postcode'   => $r_postcode,
	'country'    => $r_country 
);

// ایجاد یک سفارش جدید
$order = wc_create_order();
WC()->session->set('custom_order_id', $order->get_id());


// هنگامی که سفارش ایجاد می‌شود، شناسه آن را در سشن ذخیره می‌کنیم
function save_order_id_to_session($order_id) {
    // شناسه سفارش را در سشن ذخیره می‌کنیم 
    WC()->session->set('custom_order_id', $order_id);
}
add_action('woocommerce_checkout_order_processed', 'save_order_id_to_session');

if (!$order) {
    echo 'خطا در ایجاد سفارش.';
    exit;
}

// تنظیم آدرس‌ها برای سفارش
$order->set_address($address, 'billing');
$order->set_address($address, 'shipping');

// افزودن محصولات موجود در سبد خرید به سفارش
foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
    $product = wc_get_product($cart_item['product_id']);
    $quantity = $cart_item['quantity'];

    // افزودن محصول و تعداد به سفارش
    $order->add_product($product, $quantity);

}

 $order->add_product( wc_get_product( $product_id ), $quantity );
// محاسبه کل سفارش
// تغییر وضعیت سفارش به در حال پرداخت
$order->update_status('pending', 'Order created and awaiting payment.');

// ذخیره شناسه سفارش در سشن برای استفاده در صفحه‌ی پرداخت
WC()->session->set('custom_order_id', $order->get_id());


// افزودن محصول به سبد خرید
WC()->cart->add_to_cart($product_id, 1); // اضافه کردن محصول اصلی





// تنظیم جزئیات مشتری
$order->set_billing_first_name($firstname);
$order->set_billing_last_name($lastname);
$order->set_billing_email($email);
$order->set_billing_phone($phonenumber);


$order->calculate_totals();
$order->save();


 
      // دریافت شماره سفارش و وضعیت آن
        $order_id = $order->get_id(); 
        $order_number = $order->get_order_number();
        $order_status = $order->get_status(); // وضعیت به صورت کد


   
     // هدایت به صفحه پرداخت
$checkout_url = $order->get_checkout_payment_url();



      
      
        
        if (!$mainimage_notready 
        && isset($_FILES['mainimage']) 
        && $_FILES['mainimage']['error'] === UPLOAD_ERR_OK 
        ||
             !$child_1_mainimage_notready 
        && isset($_FILES['child_1_mainimage']) 
        && $_FILES['child_1_mainimage']['error'] === UPLOAD_ERR_OK  ||
      
        !$child_2_mainimage_notready 
        && isset($_FILES['child_2_mainimage']) 
        && $_FILES['child_2_mainimage']['error'] === UPLOAD_ERR_OK  ||
         
              !$child_3_mainimage_notready 
        && isset($_FILES['child_3_mainimage']) 
        && $_FILES['child_3_mainimage']['error'] === UPLOAD_ERR_OK  ||
           
              !$child_4_mainimage_notready 
        && isset($_FILES['child_4_mainimage']) 
        && $_FILES['child_4_mainimage']['error'] === UPLOAD_ERR_OK  ||
          
              !$child_5_mainimage_notready 
        && isset($_FILES['child_5_mainimage']) 
        && $_FILES['child_5_mainimage']['error'] === UPLOAD_ERR_OK  ||      
       
        !$child_6_mainimage_notready 
        && isset($_FILES['child_6_mainimage']) 
        && $_FILES['child_6_mainimage']['error'] === UPLOAD_ERR_OK  ||
         
          !$child_7_mainimage_notready 
        && isset($_FILES['child_7_mainimage']) 
        && $_FILES['child_7_mainimage']['error'] === UPLOAD_ERR_OK  ||
          
          !$child_8_mainimage_notready 
        && isset($_FILES['child_8_mainimage']) 
        && $_FILES['child_8_mainimage']['error'] === UPLOAD_ERR_OK  ||

         !$spouse_mainimage_notready 
        && isset($_FILES['spouse_mainimage']) 
        && $_FILES['spouse_mainimage']['error'] === UPLOAD_ERR_OK  
     
   





        
        ){
            
        // مسیر کامل فایل جدید
        $orderDir .= $order_number;
        if (!is_dir($orderDir)) {
            if (!mkdir($orderDir, 0777, true)) {
                echo json_encode(['message' => 'Failed to create directory.']);
                exit;
            }
        }

if (!$mainimage_notready) {
    $fileExtension = pathinfo(basename($_FILES["mainimage"]["name"]), PATHINFO_EXTENSION);
    $newFileName = $order_number . '_mainimage.' . $fileExtension;
    $uploadFilePath = $orderDir . '/' . $newFileName;

    // بررسی وجود فایل و ایجاد نام منحصربه‌فرد در صورت نیاز
    $counter = 1;
    while (file_exists($uploadFilePath)) {
        $newFileName = $order_number . '_' . $counter . '.' . $fileExtension;
        $uploadFilePath = $orderDir . '/' . $newFileName;
        $counter++;
    }
}

if (!$spouse_mainimage_notready) {
    $spouse_fileExtension = pathinfo(basename($_FILES["spouse_mainimage"]["name"]), PATHINFO_EXTENSION);
    $spouse_newFileName = $order_number . '_spouse_mainimage.' . $spouse_fileExtension;
    $uploadspouseFilePath = $orderDir . '/' . $spouse_newFileName;
    $counter = 1;
    while (file_exists($uploadspouseFilePath)) {
        $spouse_newFileName = $order_number . '_' . $counter . '.' . $spouse_fileExtension;
        $uploadspouseFilePath = $orderDir . '/' . $spouse_newFileName;
        $counter++;
    }
}


for ($i = 1; $i <= $number_of_children; $i++) {
    $child_mainimage_notready_var = 'child_' . $i . '_mainimage_notready';
    $child_mainimage_file_var = 'child_' . $i . '_mainimage';

if (!$$child_mainimage_notready_var) {
    // بررسی وجود ایندکس در $_FILES و اطمینان از عدم وجود خطا در آپلود
    if (isset($_FILES[$child_mainimage_file_var]) && $_FILES[$child_mainimage_file_var]['error'] == 0) {
        if (!empty($_FILES[$child_mainimage_file_var]['name'])) {
            // استخراج پسوند فایل
            $child_fileExtension = pathinfo(basename($_FILES[$child_mainimage_file_var]["name"]), PATHINFO_EXTENSION);
            $child_newFileName = $order_number . '_child_' . $i . '_mainimage.' . $child_fileExtension;
            $uploadchild_FilePath = $orderDir . '/' . $child_newFileName;
            $counter = 1;
            while (file_exists($uploadchild_FilePath)) {
                $child_newFileName = $order_number . '_' . $counter . '_child_' . $i . '.' . $child_fileExtension;
                $uploadchild_FilePath = $orderDir . '/' . $child_newFileName;
                $counter++;
            }

            // کد برای آپلود فایل در مسیر مشخص شده
            if (move_uploaded_file($_FILES[$child_mainimage_file_var]['tmp_name'], $uploadchild_FilePath)) {
                    ${'child_' . $i . '_mainimage'} = $uploadchild_FilePath;
                    $uploadOk = 1;// ذخیره مسیر فایل در متغیر مربوطه
                // می‌تونی اینجا هر کاری با فایل آپلود شده انجام بدی
            } else {
                error_log("خطا در آپلود فایل برای فرزند $i.");
            }
        } else {
            error_log("فایلی برای فرزند $i آپلود نشده است.");
        }
    } else {
        // در صورت وجود خطا در آپلود فایل
        error_log("خطا در آپلود فایل برای فرزند $i.");
    }
}
    
    
}



// آپلود تصویر اصلی
if ($uploadOk && !$mainimage_notready) {
    if (move_uploaded_file($_FILES["mainimage"]["tmp_name"], $uploadFilePath)) {
        $mainimage = $uploadFilePath;
    } else {
        echo "خطا در آپلود فایل تصویر.";
        $uploadOk = 0;
    }
}

// آپلود تصویر همسر
if ($uploadOk && !$spouse_mainimage_notready) {
    if (move_uploaded_file($_FILES["spouse_mainimage"]["tmp_name"], $uploadspouseFilePath)) {
        $spouse_mainimage = $uploadspouseFilePath;
    } else {
        echo "خطا در آپلود فایل تصویر.";
        $uploadOk = 0;
    }
}

// شرایط برای مشخص کردن وضعیت تصاویر
if ($mainimage_notready) {
    $mainimage = 'notready';
} elseif ($spouse_mainimage_notready) {
    $spouse_mainimage = 'notready';
} else {
    for ($i = 1; $i <= $number_of_children; $i++) {
        $child_mainimage_notready_var = 'child_' . $i . '_mainimage_notready';
        if ($$child_mainimage_notready_var) {
            ${'child_' . $i . '_mainimage'} = 'notready';
        }
    }
}

// اگر هیچ عکسی انتخاب نشده باشد
if ($uploadOk == 0) {
    echo 'لطفاً یک عکس انتخاب کنید یا گزینه "عکس موجود نیست" را انتخاب کنید.';
    exit;
}
}}




    // ذخیره در دیتابیس
    if ($uploadOk || $mainimage_notready) {
        $sql = "INSERT INTO user (firstname, lastname,
        latest_diploma, d_brith, m_brith, y_brith, 
        b_country, b_city, sex, email, phonenumber, mainimage,
        mainimage_notready, marriage_status, number_of_children, spouce_us_citizen, 
        order_id, order_number, order_status,
        spouse_fname,
	spouse_lname,
	spouse_latest_diploma,
	spouse_d_brith,
	spouse_m_brith,
	spouse_y_brith,	
	spouse_b_country,
	spouse_b_city,
	spouse_sex,
	spouse_mainimage_notready,	
	spouse_mainimage,
	twochansesignup,
	r_country,
    r_state,
    r_city	,
    postalcode,
    nopostalcode,
    r_address,
    child_1_fname,
child_1_lname,
child_1_d_brith ,
child_1_m_brith,
child_1_y_brith ,
child_1_b_country,
child_1_b_city ,
child_1_sex ,
child_1_mainimage_notready,
child_1_mainimage,
    child_2_fname,
child_2_lname,
child_2_d_brith ,
child_2_m_brith,
child_2_y_brith ,
child_2_b_country,
child_2_b_city,
child_2_sex ,
child_2_mainimage_notready,
child_2_mainimage ,
child_3_fname,
child_3_lname,
child_3_d_brith,
child_3_m_brith,
child_3_y_brith,
child_3_b_country,
child_3_b_city,
child_3_sex,
child_3_mainimage_notready,
child_3_mainimage,

child_4_fname,
child_4_lname,
child_4_d_brith,
child_4_m_brith,
child_4_y_brith,
child_4_b_country,
child_4_b_city,
child_4_sex,
child_4_mainimage_notready,
child_4_mainimage,

child_5_fname,
child_5_lname,
child_5_d_brith,
child_5_m_brith,
child_5_y_brith,
child_5_b_country,
child_5_b_city,
child_5_sex,
child_5_mainimage_notready,
child_5_mainimage,

child_6_fname,
child_6_lname,
child_6_d_brith,
child_6_m_brith,
child_6_y_brith,
child_6_b_country,
child_6_b_city,
child_6_sex,
child_6_mainimage_notready,
child_6_mainimage,

child_7_fname,
child_7_lname,
child_7_d_brith,
child_7_m_brith,
child_7_y_brith,
child_7_b_country,
child_7_b_city,
child_7_sex,
child_7_mainimage_notready,
child_7_mainimage,

child_8_fname,
child_8_lname,
child_8_d_brith,
child_8_m_brith,
child_8_y_brith,
child_8_b_country,
child_8_b_city,
child_8_sex,
child_8_mainimage_notready,
child_8_mainimage

        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?,?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?)";
        $stmtinsert = $db->prepare($sql);
        $result = $stmtinsert->execute([$firstname, $lastname, $latest_diploma, $d_brith, $m_brith, $y_brith, $b_country, $b_city, $sex, $email, $phonenumber, $mainimage, $mainimage_notready, $marriage_status, $number_of_children, $spouce_us_citizen, $order_id, $order_number, $order_status
        ,$spouse_fname ,
	$spouse_lname ,
	$spouse_latest_diploma ,
	$spouse_d_brith ,
	$spouse_m_brith ,
	$spouse_y_brith	 ,
	$spouse_b_country ,
	$spouse_b_city ,
	$spouse_sex ,
	$spouse_mainimage_notready,	
	$spouse_mainimage,
	$twochansesignup,
	$r_country,
    $r_state,
    $r_city	,
    $postalcode,
    $nopostalcode,
    $r_address,
    $child_1_fname,
$child_1_lname,
$child_1_d_brith ,
$child_1_m_brith,
$child_1_y_brith ,
$child_1_b_country,
$child_1_b_city ,
$child_1_sex ,
$child_1_mainimage_notready  ,
$child_1_mainimage ,
    $child_2_fname,
$child_2_lname,
$child_2_d_brith ,
$child_2_m_brith,
$child_2_y_brith ,
$child_2_b_country,
$child_2_b_city,
$child_2_sex ,
$child_2_mainimage_notready  ,
$child_2_mainimage,
$child_3_fname,
$child_3_lname,
$child_3_d_brith,
$child_3_m_brith,
$child_3_y_brith,
$child_3_b_country,
$child_3_b_city,
$child_3_sex,
$child_3_mainimage_notready,
$child_3_mainimage,

$child_4_fname,
$child_4_lname,
$child_4_d_brith,
$child_4_m_brith,
$child_4_y_brith,
$child_4_b_country,
$child_4_b_city,
$child_4_sex,
$child_4_mainimage_notready,
$child_4_mainimage,

$child_5_fname,
$child_5_lname,
$child_5_d_brith,
$child_5_m_brith,
$child_5_y_brith,
$child_5_b_country,
$child_5_b_city,
$child_5_sex,
$child_5_mainimage_notready,
$child_5_mainimage,

$child_6_fname,
$child_6_lname,
$child_6_d_brith,
$child_6_m_brith,
$child_6_y_brith,
$child_6_b_country,
$child_6_b_city,
$child_6_sex,
$child_6_mainimage_notready,
$child_6_mainimage,

$child_7_fname,
$child_7_lname,
$child_7_d_brith,
$child_7_m_brith,
$child_7_y_brith,
$child_7_b_country,
$child_7_b_city,
$child_7_sex,
$child_7_mainimage_notready,
$child_7_mainimage,

$child_8_fname,
$child_8_lname,
$child_8_d_brith,
$child_8_m_brith,
$child_8_y_brith,
$child_8_b_country,
$child_8_b_city,
$child_8_sex,
$child_8_mainimage_notready,
$child_8_mainimage

        ]);






        if ($result) {
            // ارسال پاسخ JSON
            echo json_encode([
                'message' => 'سفارش با موفقیت ثبت شد. شماره سفارش: ' . $order_number,
                'order_status' => $order_status,
                'checkout_url' => $checkout_url
            ]);
        } else {
            echo json_encode(['message' => 'خطا در ذخیره اطلاعات در پایگاه داده.']);
        }
    } else {
        echo 'خطا در آپلود فایل.';
    }
} else {
    echo 'لطفاً همه فیلدهای مورد نیاز را پر کنید.';
}
?>
