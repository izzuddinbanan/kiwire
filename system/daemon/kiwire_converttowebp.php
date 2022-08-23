<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING );

require_once dirname(__FILE__, 3) . "/server/libs/class.uploader.php";

require_once dirname(__FILE__, 3) . "/server/admin/includes/include_connection.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";


 
$uploader = new Uploader();

$kiw_clouds = $kiw_db->fetch_array("SELECT * FROM kiwire_clouds");

echo date("Y-m-d H:i:s") . " : Convert to webp started \n";
foreach($kiw_clouds as $kiw_cloud){

    $kiw_path = dirname(__FILE__, 3) . "/server/custom/{$kiw_cloud['tenant_id']}/uploads/";
    $kiw_path_url = "/server/custom/{$kiw_cloud['tenant_id']}/uploads/";

    $kiw_images_list = scandir($kiw_path);


    foreach ($kiw_images_list as $image){

        if (!in_array($image, array(".", "..", "original-image"))) {

            // $image = "measat-logo.webp";

            $source = $kiw_path . $image;

            $name_webp = pathinfo($kiw_path_url . $image, PATHINFO_FILENAME) . ".webp";
            
            
            //convert image to webp
            $extension = pathinfo($source, PATHINFO_EXTENSION);
            
            if($extension != 'webp'){
                
                
                if ($extension == 'jpeg' || $extension == 'jpg') 
                $image_gd = imagecreatefromjpeg($source);
                elseif ($extension == 'gif') 
                $image_gd = imagecreatefromgif($source);
                elseif ($extension == 'png') 
                $image_gd = imagecreatefrompng($source);
                
                if(imagewebp($image_gd, "{$kiw_path}{$name_webp}", 80)){

                    if(filesize($kiw_path.$name_webp) > 0){

                        if(!file_exists($kiw_path)) mkdir($kiw_path, 750, true);
    
                        $path_ori = "{$kiw_path}/original-image";
                        $name_ori = pathinfo($source, PATHINFO_FILENAME) . ".png";
    
                        if(!file_exists($path_ori)) mkdir($path_ori, 750, true);
    
                        rename($source, "{$path_ori}/{$name_ori}");
                        unset($source);
                    }
                    else
                        unlink($kiw_path.$name_webp);

                }
            }

        }

    }

}

echo date("Y-m-d H:i:s") . " : Convert to webp finished \n";

