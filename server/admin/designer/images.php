<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title>Images</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content=""> 
    <style>
        #files img {cursor: pointer; margin:20px; height:150px}
    </style> 
</head>
<body>

<?php


session_start();

$kiw_path = dirname(__FILE__, 3) . "/custom/{$_SESSION['tenant_id']}/uploads/";
$kiw_path_url = "/custom/{$_SESSION['tenant_id']}/uploads/";

$kiw_images_list = scandir($kiw_path);


foreach ($kiw_images_list as $image){

    if (!in_array($image, array(".", ".."))) {

        if (getimagesize($kiw_path . $image)[0] > 0) {

            $kiw_images[] = $kiw_path_url . $image;

        }

    }

}


?>

<div id="files">

    <?php foreach ($kiw_images as $kiw_image){ ?>
        <img src="<?= $kiw_image ?>" />
    <?php } ?>

</div>

<script src="/admin/designer/contentbuilder/jquery.min.js"></script>
<script>
    jQuery(document).ready(function ($) {

        $("img").click(function () {

            selectAsset($(this).attr('src'));

        });

    });

    function selectAsset(assetValue) {

        if (parent.selectImage) {

            parent.selectImage(assetValue);

        } else {

            var inp = parent.top.$('#active-input').val();

            parent.top.$('#' + inp).val(assetValue);

            if (window.frameElement.id == 'ifrFileBrowse') parent.top.$("#md-fileselect").data('simplemodal').hide();
            if (window.frameElement.id == 'ifrImageBrowse') parent.top.$("#md-imageselect").data('simplemodal').hide();

        }

    }
</script>
</body>
</html>