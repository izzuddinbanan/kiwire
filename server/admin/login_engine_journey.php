<?php

$kiw['module'] = "Login Engine -> Journey";
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

$kiw_db = Database::obtain();

$kiw_pages = $kiw_db->fetch_array("SELECT unique_id,page_name FROM kiwire_login_pages WHERE tenant_id = '{$_SESSION['tenant_id']}'");
$kiw_zones = $kiw_db->fetch_array("SELECT `name` FROM kiwire_zone WHERE tenant_id = '{$_SESSION['tenant_id']}'");

?>
<style>
    #multiple-list-group-a::-webkit-scrollbar {
        width: 12px;
        background-color: #F5F5F5;
    }

    #multiple-list-group-a::-webkit-scrollbar-thumb {
        border-radius: 10px;
        -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, .3);
        background-color: #555;
    }

    #multiple-list-group-b::-webkit-scrollbar {
        width: 12px;
        background-color: #F5F5F5;
    }

    #multiple-list-group-b::-webkit-scrollbar-thumb {
        border-radius: 10px;
        -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, .3);
        background-color: #555;
    }
</style>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_engine_journey_title">Journey</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_engine_journey_subtitle">
                                Login journey
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

                                <form class="update-form">

                                    <div class="tab-content">

                                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-journey pull-right" data-toggle="modal" data-target="#journey-modal">Add Journey</button>

                                        <div class="table-responsive">

                                            <table id="itemlist" class="table table-hover table-data">
                                                <thead>
                                                    <tr class="text-uppercase">
                                                        <th data-i18n="login_engine_journey_no">No</th>
                                                        <th data-i18n="login_engine_journey_name">Name</th>
                                                        <th data-i18n="login_engine_journey_prelogin">Pre-Login</th>
                                                        <th data-i18n="login_engine_journey_postlogin">Post-Login</th>
                                                        <th data-i18n="login_engine_journey_pages">Pages</th>
                                                        <th data-i18n="login_engine_journey_language">Language</th>
                                                        <th data-i18n="login_engine_journey_status">Status</th>
                                                        <th data-i18n="login_engine_journey_action">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <th data-i18n="login_engine_journey_loading">
                                                        Loading...
                                                    </th>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

</div>


<div class="modal fade text-left" id="journey-modal" role="dialog" aria-labelledby="mymodal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content" style="min-height: 600px; max-height: 800px; height: 90%;">
            <div class="modal-header">
                <h4 class="modal-title" id="mymodal" data-i18n="login_engine_journey_create">Create or Edit Login Journey</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="create-form" action="#">
                <div class="modal-body">

                    <div class="row">

                        <div class="col-sm-3">
                            <label data-i18n="login_engine_journey_name2">Name: </label>
                            <div class="form-group">
                                <input type="text" name="name" placeholder="" class="form-control">
                            </div>
                        </div>

                        <div class="col-sm-3">

                            <label data-i18n="login_engine_journey_prelogin2">Pre-Login: </label>
                            <div class="form-group">
                                <select name="pre_login" id="pre_login" data-journey="pre_login" class="select2">
                                    <option value="default" selected><span data-i18n="login_engine_journey_landing">Proceed to Landing Page</span></option>
                                    <option value="custom"><span data-i18n="login_engine_journey_redirect">Redirect to specific URL</option>
                                </select>
                            </div>

                            <div style="display: none;" class="pre_login">
                                <label data-i18n="login_engine_journey_prelogin_url">Pre-Login URL: </label>
                                <div class="form-group">
                                    <input type="text" name="pre_login_url" placeholder="" class="form-control">
                                </div>
                            </div>

                        </div>

                        <div class="col-sm-3">

                            <label data-i18n="login_engine_journey_postlogin2">Post-Login: </label>
                            <div class="form-group">
                                <select name="post_login" id="post_login" data-journey="post_login" class="select2">
                                    <option value="default" selected><span data-i18n="login_engine_journey_continue">User will continue their destination</span></option>
                                    <option value="campaign"><span data-i18n="login_engine_journey_campaign">Redirect to last page [ campaign ]</span></option>
                                    <option value="custom"><span data-i18n="login_engine_journey_specific">Redirect to specific URL</option>
                                </select>
                            </div>

                            <div style="display: none;" class="post_login">
                                <label data-i18n="login_engine_journey_postlogin_url">Post-Login URL: </label>
                                <div class="form-group">
                                    <input type="text" name="post_login_url" placeholder="" class="form-control">
                                </div>
                            </div>

                        </div>

                        <div class="col-sm-2">
                            <label data-i18n="login_engine_journey_language2">Language: </label>
                            <div class="form-group">
                                <input type="text" name="lang" placeholder="ISO Code [eg: us]" class="form-control">
                            </div>
                        </div>


                        <div class="col-sm-1">
                            <label>&nbsp;</label>
                            <div class="form-group">
                                <fieldset class="checkbox">
                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                        <input type="checkbox" name="status">
                                        <span class="vs-checkbox">
                                            <span class="vs-checkbox--check">
                                                <i class="vs-icon feather icon-check"></i>
                                            </span>
                                        </span>
                                        <span class="" data-i18n="login_engine_journey_enable">Enable</span>
                                    </div>
                                </fieldset>
                            </div>
                        </div>


                    </div>


                    <div class="row border" style="height: 200px; padding: 5px; overflow: hidden;">


                        <div class="col-sm-12" style="height: 200px; overflow: auto; margin-bottom: 10px;">

                            <div class="center-text" style="display: inline-block; white-space: nowrap; position: absolute; top: 80px; right: 10px; font-size: xx-large; font-weight: bolder; color: #ebebeb; overflow: hidden;"><span><i class="fa fa-arrow-left"></i></span> <span data-i18n="login_engine_journey_drag">DRAG YOUR JOURNEY PAGES HERE</span></div>

                            <ul class="list-group list-group-horizontal" id="multiple-list-group-a" style="width: 100%; height: 200px; overflow: auto;">

                            </ul>

                        </div>

                    </div>

                    <div class="row border-bottom" style="height: 200px; padding: 5px; overflow: hidden;">

                        <div class="col-sm-12" style="overflow: auto;">

                            <div class="center-text" style="display: inline-block; white-space: nowrap; position: absolute; top: 80px; right: 10px; font-size: xx-large; font-weight: bolder; color: #ebebeb;"><span><i class="fa fa-arrow-left"></i></span> <span data-i18n="login_engine_journey_pages_drag"> PAGES</div>

                            <ul class="list-group list-group-horizontal" id="multiple-list-group-b" style="width: 100%; height: 200px;overflow: auto;">

                                <?php foreach ($kiw_pages as $kiw_page) { ?>

                                    <?php if (file_exists(dirname(__FILE__, 2) . "/custom/{$_SESSION['tenant_id']}/thumbnails/{$kiw_page['unique_id']}.png")) {  ?>

                                        <li data-page-id="<?= $kiw_page['unique_id'] ?>" style="margin-right: 5px; list-style: none; position: relative; background-color: white;">
                                            <div style="padding: 5px; border: grey dashed thin; height: 180px; width: 180px; background-repeat: no-repeat; background-position: center center; background-image: url('/custom/<?= $_SESSION['tenant_id'] ?>/thumbnails/<?= $kiw_page['unique_id'] ?>.png'); background-position: center center; background-size: cover;">
                                                <span style="color: white; background-color: rgba(0, 0, 0, .3); padding: 5px;"><?= substr($kiw_page['page_name'], 0, 15) ?></span>
                                            </div>
                                        </li>

                                    <?php } ?>

                                <?php } ?>


                            </ul>


                        </div>


                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" data-i18n="login_engine_journey_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary btn-save-journey" data-i18n="login_engine_journey_save">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>



<div class="modal fade text-left show" id="preview-modal" role="dialog" aria-labelledby="myModalLabel1" aria-modal="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-body">

                <img class="space-image" src="" alt="" style="min-height: 500px; width: 100%;">

            </div>

        </div>
    </div>
</div>




<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>

<script src="/app-assets/vendors/js/extensions/dragula.min.js"></script>

<script>
    $(document).ready(function() {

        dragula([document.querySelector('#multiple-list-group-a'), document.querySelector('#multiple-list-group-b')]);

    });
</script>