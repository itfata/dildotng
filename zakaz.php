<?php
// ========== ОБРАБОТКА ЗАКАЗА ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    session_start();

    $utm_keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
    $utms = [];

    foreach ($utm_keys as $key) {
    if (isset($_COOKIE[$key])) {
        $utms[$key] = $_COOKIE[$key];
    } else {
        $utms[$key] = '';
    }
    }

    // Формируем массив с товарами в заказе
    $products_list_dop = array(
        0 => array(
            'product_id' => '184',
            'price'      => '799',
            'count'      => '1'
        )
    );

    $products_dop = urlencode(serialize($products_list_dop));
    $sender_dop = urlencode(serialize($_SERVER));

    // Параметры запроса к CRM
    $data_dop = array(
        'key'             => 'c8885773370934ea4e28830776091029', // ваш CRM-токен
        'order_id'        => number_format(round(microtime(true) * 10), 0, '.', ''),
        'country'         => 'UA',
        'office'          => '2',
        'products'        => $products_dop,
        'bayer_name'      => $_POST['name'],
        'phone'           => $_POST['phone'],
        'email'           => $_POST['email'] ?? '',
        'comment'         => $_POST['comment'] ?? '',
        'delivery'        => $_REQUEST['1'],        // способ доставки (id в CRM)
        'delivery_adress' => $_REQUEST['address'], // адрес доставки
        'payment'         => '',
        'sender'          => $sender_dop,
        'utm_source'      => $utms['utm_source'] ?? '',
        'utm_medium'      => $utms['utm_medium'] ?? '',
        'utm_term'        => $utms['utm_term'] ?? '',
        'utm_content'     => $utms['utm_content'] ?? '',
        'utm_campaign'    => $utms['utm_campaign'] ?? '',
        'additional_1'    => $_POST['product_name'],
        'additional_2'    => '',                               // Дополнительное поле 2
        'additional_3'    => '',                               // Дополнительное поле 3
        'additional_4'    => '' 
    );

    // Отправляем заказ в CRM
    $curl_dop = curl_init();
    curl_setopt($curl_dop, CURLOPT_URL, 'http://slowride.lp-crm.biz/api/addNewOrder.html');
    curl_setopt($curl_dop, CURLOPT_POST, true);
    curl_setopt($curl_dop, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_dop, CURLOPT_POSTFIELDS, $data_dop);
    $out_dop = curl_exec($curl_dop);
    curl_close($curl_dop);

    // Отправка уведомления на email
    $recepient = "skripart.off@gmail.com";
    $sitename = "prem.mimipiki.shop";
    $domain = "prem.mimipiki.shop";

    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);

    $message = "Ім'я: $name\nТелефон: $phone\nМісто: {$_POST['address']}\nВідділення: {$_POST['post']}";
    $headers = "From: $sitename <no-reply@$domain>\r\nContent-type: text/plain; charset=utf-8 \r\n";
    $pagetitle = "Нове замовлення з сайту \"$sitename\"";
    mail($recepient, $pagetitle, $message, $headers);

    // Telegram (по желанию)
    /*
    $telegramBotToken = 'ВАШ_ТОКЕН';
    $telegramChatId = 'ВАШ_CHAT_ID';
    $telegramMessage = "✅ Нове замовлення:\nІм'я: {$name}\nТелефон: {$phone}\nМісто: {$_POST['address']}\nВідділення: {$_POST['post']}";
    file_get_contents("https://api.telegram.org/bot{$telegramBotToken}/sendMessage?" . http_build_query([
        'chat_id' => $telegramChatId,
        'text' => $telegramMessage
    ]));
    */

    // ✅ РЕДИРЕКТ НА СТРАНИЦУ СПАСИБО
    header("Location: https://prem.mimipiki.shop/thx");
    exit;
}
?>
