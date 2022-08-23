<?php

$kiw['module'] = "Integration -> SMS";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM  kiwire_facebook_reputation WHERE tenant_id = '{$tenant_id}'");

$no = 1;

?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_fb_title">Facebook</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_fb_subtitle">
                                Add Facebook Pages for Reviews
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-facebook pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="integration_fb_add">Add Facebook Page</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="integration_fb_no">No</th>
                                        <th data-i18n="integration_fb_page_id">Page ID</th>
                                        <th data-i18n="integration_fb_page_name">Page Name</th>
                                        <th data-i18n="integration_fb_last_update">Last Update</th>
                                        <th data-i18n="integration_fb_status">Status</th>
                                        <th data-i18n="integration_fb_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  <th data-i18n="integration_fb_loading">
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

<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>
