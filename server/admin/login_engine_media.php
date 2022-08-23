<?php

$kiw['module'] = "Login Engine -> Media";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_nav.php';
require_once 'includes/include_access.php';
require_once "includes/include_connection.php";


system("chmod 755 -R " . dirname(__FILE__, 2) . "/custom/");


$kiw_path = dirname(__FILE__, 2) . "/custom/{$_SESSION['tenant_id']}/uploads/";
$kiw_path_url = "/custom/{$_SESSION['tenant_id']}/uploads/";

$kiw_images_list = scandir($kiw_path);

foreach ($kiw_images_list as $image){

    if (!in_array($image, array(".", "..", "original-image"))) {

        $kiw_current_images = $kiw_path . $image;

        $kiw_images[] = array("name" => $image, "size" => filesize($kiw_current_images), "type" => mime_content_type($kiw_current_images), "file" => $kiw_path_url . $image);

    }

}



?>
<link rel="stylesheet" type="text/css" href="/assets/css/jquery.filer.css">
<link rel="stylesheet" type="text/css" href="/assets/css/themes/jquery.filer-dragdropbox-theme.css">

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_engine_media_title">Upload Media</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_engine_media_subtitle">
                                Upload custom media to this server
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section id="basic-tabs-components">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card overflow-hidden">
                        <div class="card-content">

                            <div class="card-body">

                                <form action="/admin/ajax/ajax_login_engine_media.php" method="post"
                                      enctype="multipart/form-data">
                                    <input type="file" name="files[]" id="filer_input" multiple="multiple" accept=".png, .jpg, .jpeg, .webp">
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                </form>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<?php

require_once "includes/include_footer.php";

?>
<script src="/assets/js/jquery-2.2.4.min.js"></script>
<script src="/assets/js/jquery.filer.js"></script>


<script>

    filer_default_opts = {
        changeInput2: '<div class="jFiler-input-dragDrop"><div class="jFiler-input-inner"><div class="jFiler-input-icon"><i class="icon-jfi-cloud-up-o"></i></div><div class="jFiler-input-text"><h3>Drag & drop your files here</h3> <span style="display:inline-block; margin: 15px 0">or</span></div><a class="jFiler-input-choose-btn btn-custom blue-light">Browse Files</a></div></div>',
        templates: {
            box: '<ul class="jFiler-items-list jFiler-items-grid"></ul>',
            item: '<li class="jFiler-item">\
                            <div class="jFiler-item-container">\
                                <div class="jFiler-item-inner">\
                                    <div class="jFiler-item-thumb">\
                                        <div class="jFiler-item-status"></div>\
                                        <div class="jFiler-item-thumb-overlay">\
    										<div class="jFiler-item-info">\
    											<div style="display:table-cell;vertical-align: middle;">\
    												<span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name}}</b></span>\
    												<span class="jFiler-item-others">{{fi-size2}}</span>\
    											</div>\
    										</div>\
    									</div>\
                                        {{fi-image}}\
                                    </div>\
                                    <div class="jFiler-item-assets jFiler-row">\
                                        <ul class="list-inline pull-left">\
                                            <li>{{fi-progressBar}}</li>\
                                        </ul>\
                                        <ul class="list-inline pull-right">\
                                            <li><a class="icon-jfi-check-circle jFiler-item-copy-action"></a></li>\
                                            <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                        </ul>\
                                    </div>\
                                </div>\
                            </div>\
                        </li>',
            itemAppend: '<li class="jFiler-item">\
                                <div class="jFiler-item-container">\
                                    <div class="jFiler-item-inner">\
                                        <div class="jFiler-item-thumb">\
                                            <div class="jFiler-item-status"></div>\
                                            <div class="jFiler-item-thumb-overlay">\
        										<div class="jFiler-item-info">\
        											<div style="display:table-cell;vertical-align: middle;">\
        												<span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name}}</b></span>\
        												<span class="jFiler-item-others">{{fi-size2}}</span>\
        											</div>\
        										</div>\
        									</div>\
                                            {{fi-image}}\
                                        </div>\
                                        <div class="jFiler-item-assets jFiler-row">\
                                            <ul class="list-inline pull-left">\
                                                <li><span class="jFiler-item-others">{{fi-icon}}</span></li>\
                                            </ul>\
                                            <ul class="list-inline pull-right">\
                                                <li><a class="icon-jfi-check-circle jFiler-item-copy-action"></a></li>\
                                                <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                            </ul>\
                                        </div>\
                                    </div>\
                                </div>\
                            </li>',
            progressBar: '<div class="bar"></div>',
            itemAppendToEnd: false,
            removeConfirmation: true,
            _selectors: {
                list: '.jFiler-items-list',
                item: '.jFiler-item',
                progressBar: '.bar',
                remove: '.jFiler-item-trash-action'
            }
        },
        dragDrop: {},
        uploadFile: {
            url: "/admin/ajax/ajax_login_engine_media.php?action=upload",
            data: {},
            type: 'POST',
            enctype: 'multipart/form-data',
            beforeSend: function(){},
            success: function(data, el){


                let mydata = JSON.parse(data)
                // el.find(".jFiler-item-title").find("b").html();

                let name_img = mydata['metas'][0]['name_webp'] ? mydata['metas'][0]['name_webp'] : mydata['metas'][0]['name'];

                el.find(".jFiler-item-title").html("<b title='"+ name_img +"'>"+ name_img +"</b>");


                var parent = el.find(".jFiler-jProgressBar").parent();

                el.find(".jFiler-jProgressBar").fadeOut("slow", function(){

                    var text = 'text-success';
                    if(mydata['messages'] != "Success") text =  'text-danger';

                    $("<div class=\"jFiler-item-others "+ text +" \"><i class=\"icon-jfi-check-circle\"></i> "+ mydata['messages']+ "</div>").hide().appendTo(parent).fadeIn("slow");

                });

                $(".jFiler-item-copy-action").off('click').on("click", function () {

                    let tenant_id = '<?= $_SESSION['tenant_id'] ?>';
                    let image_name = $(this).parents(".jFiler-item").find("b").html();

                    var input = document.createElement('input');

                    input.setAttribute('value', "/custom/" + tenant_id + "/uploads/" + image_name);
                    document.body.appendChild(input);

                    input.select();

                    document.execCommand('copy');
                    document.body.removeChild(input);

                    toastr.success("Path has been copied to your clipboard");

                });

            },
            error: function(el){

                var parent = el.find(".jFiler-jProgressBar").parent();

                el.find(".jFiler-jProgressBar").fadeOut("slow", function(){

                    $("<div class=\"jFiler-item-others text-error\"><i class=\"icon-jfi-minus-circle\"></i> Error</div>").hide().appendTo(parent).fadeIn("slow");

                });

            },
            statusCode: null,
            onProgress: null,
            onComplete: null
        },

    };



    $(document).ready(function() {

        $('#filer_input').filer({

            changeInput: filer_default_opts.changeInput2,
            showThumbs: true,
            theme: "dragdropbox",

            templates: filer_default_opts.templates,
            dragDrop: filer_default_opts.dragDrop,
            uploadFile: filer_default_opts.uploadFile,

            onRemove: function (itemEl, file, id, listEl, boxEl, newInputEl, inputEl) {

                $.post('/admin/ajax/ajax_login_engine_media.php?action=delete', {file: file.name});

            },

            files : <?= json_encode($kiw_images) ?>

        });


        $(".jFiler-item-copy-action").off('click').on("click", function () {

            let tenant_id = '<?= $_SESSION['tenant_id'] ?>';
            let image_name = $(this).parents(".jFiler-item").find("b").html();

            var input = document.createElement('input');

            input.setAttribute('value', "/custom/" + tenant_id + "/uploads/" + image_name);
            document.body.appendChild(input);

            input.select();

            document.execCommand('copy');
            document.body.removeChild(input);

            toastr.success("Path has been copied to your clipboard");

        });

    });

</script>

