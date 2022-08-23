<?php

$kiw['module'] = "Login Engine -> Desiger Tool -> List";
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

?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_engine_page_designer_title">Page Designer</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_engine_page_designer_subtitle">
                                Create or design page
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="content-body">
        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">

                        <button type="button" onClick="window.location = '/admin/designer/'" class="btn btn-primary waves-effect waves-light create-btn-page_designer pull-right" data-toggle="modal" data-target="#inlineForm">Add Page</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="login_engine_page_designer_table_#">#</th>
                                        <th data-i18n="login_engine_page_designer_table_id">ID</th>
                                        <th data-i18n="login_engine_page_designer_table_name">Name</th>
                                        <th data-i18n="login_engine_page_designer_table_updated">Updated On</th>
                                        <th data-i18n="login_engine_page_designer_table_remark">Remark</th>
                                        <th data-i18n="login_engine_page_designer_table_default">Default</th>
                                        <th data-i18n="login_engine_page_designer_table_type">Type</th>
                                        <th data-i18n="login_engine_page_designer_table_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  <th data-i18n="login_engine_page_designer_loading">
                                    Loading...
                                  </th>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<div class="modal fade text-left" id="duplicate_modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="mythememodal" data-i18n="login_engine_page_designer_duplicate">Duplicate Page ( <span class="duplicate_name"></span> )</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <form class="create-form" action="#">

                    <label data-i18n="login_engine_page_designer_name">Page Name:</label>
                    <div class="form-group">
                        <input type="text" name="page_name" class="form-control" value="">
                    </div>
                    
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                </form>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-danger" data-dismiss="modal" data-i18n="login_engine_page_designer_cancel_btn">Cancel</button>
                <button type="button" class="btn btn-primary btn-duplicate-page" data-i18n="login_engine_page_designer_duplicate_btn">Duplicate</button>

            </div>

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
