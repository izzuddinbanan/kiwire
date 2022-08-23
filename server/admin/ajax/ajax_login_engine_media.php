<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 3) . "/libs/class.uploader.php";


if ($_GET['action'] == "upload"){

    $uploader = new Uploader();

    $data = $uploader->upload($_FILES['files'], array('limit' => 10,
        'maxSize' => 5,
        'extensions' => null,
        'required' => false,
        'uploadDir' => dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/uploads/",
        'title' => array('name'),
        'removeFiles' => true,
        'perms' => null,
        'onCheck' => null,
        'onError' => null,
        'onSuccess' => null,
        'onUpload' => null,
        'onComplete' => null,
        'onRemove' => 'onFilesRemoveCallback'
    ));


    if ($data['isComplete']) {

        $img_path = dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/uploads/";
        $files = $data['data'];

        $imgwebp = $img_path . $files['metas'][0]['name_webp'];

        if (file_exists($imgwebp)) {

            if(filesize($imgwebp) > 0){
                $files['messages'] = 'Success';
            }
            else{

                $files['messages'] = 'Image Corrupted';
    
                unlink($imgwebp);
            }


        }

       

        $files['is_success'] = true;

        echo json_encode($files);

    }


    if ($data['hasErrors']) {

        $errors = $data['errors'];
        $errors['is_success'] = false;

        echo json_encode($errors);

    }

} elseif ($_GET['action'] == "delete") {

    if (isset($_REQUEST['file']) && !empty($_REQUEST['file'])){

        $kiw_path = dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/uploads/";

        if (file_exists($kiw_path . $_REQUEST['file'])){

            unlink($kiw_path . $_REQUEST['file']);

        }

        // For WEBP
        if(in_array(pathinfo($_REQUEST['file'], PATHINFO_EXTENSION), ['jpeg', 'jpg', 'gif', 'png'])) {
        
        
            $webp_name = pathinfo($_REQUEST['file'], PATHINFO_FILENAME) . ".webp";
            
            if (file_exists($kiw_path . "/{$webp_name}")){

                unlink($kiw_path . "/{$webp_name}");

            }

        }

    }

}


function onFilesRemoveCallback($removed_files)
{
    foreach ($removed_files as $key => $value) {

        $file = dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/uploads/{$value}";

        if (file_exists($file)) {

            unlink($file);

        }

    }

    return $removed_files;

}