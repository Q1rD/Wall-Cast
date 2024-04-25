<?php

$url = isset($_POST['url']) ? $_POST['url'] : '';

if (isset($url)) {
    switch ($url) {
        case '/1':
            $js_scripts = array('/auth/gen.js');
            break;
        case '/messages1':
            $js_scripts = array('/messages/gen.js');
            break;
        case '/subscriptions1':
            $js_scripts = array('/auth/gen.js');
            break;
        case '/profile1':
            $js_scripts = array('/auth/gen.js');
            break;
    }
    $html_content = file_get_contents(substr($url, 1) . '.php');
    $response = array(
        'html' => $html_content,
        'scripts' => $js_scripts,
        'info' => $url
    );

    header('Content-Type: application/json');
    echo json_encode($response);
}

?>