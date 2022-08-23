<?php

$kiw['module'] = "Policy -> Zone Restriction";
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

$kiw_zones = $kiw_db->fetch_array("SELECT `name` FROM kiwire_zone WHERE tenant_id = '{$_SESSION['tenant_id']}'");


?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="policy_zone_restriction_title">Zone Restriction</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="policy_zone_restriction_subtitle">
                                Manage zone restriction policy
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-zone_restriction pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="policy_zone_restriction_add">Add Zone Restriction</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="policy_zone_restriction_tno">No</th>
                                        <th data-i18n="policy_zone_restriction_tgroup_name">Group Name</th>
                                        <th data-i18n="policy_zone_restriction_taction">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  <th data-i18n="policy_zone_restriction_loading">
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


<!-- Modal -->
<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="policy_zone_restriction_add_edit"> Add or Edit Zone Restriction</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">
                
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                <div class="modal-body">

                    <label data-i18n="policy_zone_restriction_group_name">Group Name: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="name" id="name" value="" class="form-control" required>
                    </div>

                    <label data-i18n="policy_zone_restriction_permission">Permission Control: </label>
                    <div class="col-md-12 border">

                        <div class="form-group">

                            <?php foreach ($kiw_zones as $kiw_zone) { ?>

                                <div class="custom-control custom-checkbox" style="margin-top: 10px; margin-bottom: 10px; display: block;">
                                    <input type="checkbox" class="custom-control-input" name="zone[]" id="<?= $kiw_zone['name'] ?>" value="<?= $kiw_zone['name'] ?>">
                                    <label for="<?= $kiw_zone['name'] ?>" class="custom-control-label">
                                        <?= $kiw_zone['name'] ?>
                                    </label>
                                </div>

                            <?php } ?>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="policy_zone_restriction_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="policy_zone_restriction_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="policy_zone_restriction_update">Update</button>

                </div>

            </form>
        </div>
    </div>
</div>


<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>
