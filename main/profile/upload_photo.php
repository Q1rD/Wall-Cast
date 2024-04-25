<?php
if ($_FILES["image"]["error"] == UPLOAD_ERR_OK) {
    $login = $_COOKIE["login"];
    $target_file = sprintf("avatars/%s.png", $login);
    
    if (file_exists($target_file)) {
        // Если файл существует, удаляем его
        unlink($target_file);
    }
    
    if ($_FILES["image"]["error"] == UPLOAD_ERR_OK) {
        $image_info = getimagesize($_FILES["image"]["tmp_name"]);
        $width = $image_info[0];
        $height = $image_info[1];

        if ($width >= 100 || $height >= 100) {
            $new_image = imagecreatetruecolor(100, 100);
            $uploaded_image = imagecreatefromstring(file_get_contents($_FILES["image"]["tmp_name"]));
            imagecopyresampled($new_image, $uploaded_image, 0, 0, 0, 0, 100, 100, $width, $height);
            imagejpeg($new_image, $target_file);
            imagedestroy($new_image);
            imagedestroy($uploaded_image);
            echo 200;
        } else {
            echo 0;
        }
    } else {
        echo 400;
    }
}
?>