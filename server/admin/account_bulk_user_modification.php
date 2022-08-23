<?php

$kiw['module'] = "Account -> Bulk User Modification";
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

<form class="form form-horizontal" method="post">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Bulk User Modification</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active" data-i18n="subtitle">
                                    Edit multiple accounts
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

                            <div class="form-body mt-2">

                                <div class="row">
                                    <div class="col-12  d-none d-md-block">
                                        <div class="form-group row m-1">

                                            <button type="button"
                                                    class="btn btn-primary pull-left   mr-1 mb-75 btn-select-all" data-i18n="button_select_all">
                                                Select All
                                            </button>
                                            <button type="button"
                                                    class="btn btn-primary pull-left mr-1 mb-75 btn-clear-all" data-i18n="button_clear_all">Clear
                                                All
                                            </button>
                                            <button type="button"
                                                    class="btn btn-primary pull-left mr-1 mb-75 btn-extend-selected" data-i18n="button_extend_expiry">
                                                Extend Expiry Date
                                            </button>
                                            <button type="button"
                                                    class="btn btn-primary pull-left mr-1 mb-75 btn-change-profile" data-i18n="button_change_profile">
                                                Change Profile
                                            </button>
                                            <button type="button"
                                                    class="btn btn-primary pull-left mr-1 mb-75 btn-reset-account" data-i18n="button_reset_account">
                                                Reset Account
                                            </button>
                                            <button type="button"
                                                    class="btn btn-primary pull-left mr-1 mb-75 btn-export" data-i18n="button_export">Export
                                            </button>
                                            <button type="button"
                                                    class="btn btn-primary pull-left mr-1 mb-75 btn-delete-user" data-i18n="button_delete_user">Delete
                                                User
                                            </button>

                                        </div>
                                    </div>

                                    <div class="col-12 d-sm-block  d-xs-block d-md-none mb-2">

                                        <div class="dropdown">
                                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-list"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item btn-select-all" href="javascript:;">Select All</a>
                                                <a class="dropdown-item btn-clear-all" href="javascript:;">Clear All</a>
                                                <a class="dropdown-item btn-extend-selected" href="javascript:;">Extend Expiry Date</a>
                                                <a class="dropdown-item btn-change-profile" href="javascript:;">Change Profile</a>
                                                <a class="dropdown-item btn-reset-account" href="javascript:;">Reset Account</a>
                                                <a class="dropdown-item btn-export" href="javascript:;">Export</a>
                                                <a class="dropdown-item btn-delete-user" href="javascript:;">Delete User</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-sm-12 col-xs-12 col-md-6">
                                        <div class="form-group row">

                                            <div class="col-md-4">
                                                <span data-i18n="span_select_new_profile"><b>Select New Profile</b></span>
                                            </div>

                                            <div class="col-md-8">
                                                <fieldset class="form-group">
                                                    <select class="select2 form-control" name="profile" id="profile"
                                                            data-style="btn-default">

                                                        <option value="none" data-i18n="select_profile_option_none">None</option>

                                                        <?php

                                                        $kiw_row = $kiw_db->fetch_array("SELECT name FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}' GROUP BY name");

                                                        foreach ($kiw_row as $record) {

                                                            echo "<option value='{$record['name']}'> {$record['name']} </option> \n";

                                                        }

                                                        ?>

                                                    </select>
                                                </fieldset>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="col-sm-12 col-xs-12 col-md-6">
                                        <div class="form-group row">

                                            <div class="col-md-3">
                                                <span data-i18n="span_select_date"><b>Select Date</b></span>
                                            </div>

                                            <div class="col-md-9">
                                                <input type="text" id="expiry" name="expiry" class="form-control format-picker" placeholder="DD-MM-YYYY">
                                            </div>

                                        </div>
                                    </div>
                                </div>


                            </div>


                            <div class="table-responsive">
                                <table id="itemlist" class="table table-hover table-data">
                                    <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="table_header_no">No</th>
                                        <th data-i18n="table_header_username">Username</th>
                                        <th data-i18n="table_header_profile">Profile</th>
                                        <th data-i18n="table_header_expiry">Expiry Date</th>
                                        <th data-i18n="table_header_selected">Selected?</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <th  data-i18n="table_body_loading">
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
    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
</form>

<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>
<script src="/assets/js/datejs/build/date.js"></script>
