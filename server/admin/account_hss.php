<?php

$kiw['module'] = "Account -> HSS";
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

// $kiw_fields = @file_get_contents(dirname(__FILE__, 2) . "/custom/{$_SESSION['tenant_id']}/data-mapping.json");

// $kiw_fields = json_decode($kiw_fields, true);

// if (!is_array($kiw_fields)) $kiw_fields = array();


$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_int_hss WHERE tenant_id = '$tenant_id' LIMIT 1");
if (empty($kiw_row)) {

    $kiw_db->query("INSERT INTO kiwire_int_hss(tenant_id) VALUE('{$tenant_id}')");

}

?>


<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">HSS / Sim Card</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Manage HSS / Sim Card
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- 
    <div class="row">
        <div class="col-12 mb-1">
            <button id="filter-btn" class="float-right btn btn-icon btn-primary btn-xs fa fa-filter"></button>
        </div>
    </div> -->


    <div class="content-body">

        <!--section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="col-12">
                            <div class="form-group row">
                                <h6 class="text-bold-500" data-i18n="login_logins_error_date_search">FILTER :</h6>
                            </div>
                        </div>

                            <div class="col-12">
                                <div class="form-group row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="filter_username" data-i18n="filter_username">Code: </label>
                                            <input type="text" placeholder="" name="filter_username" id="filter_username" value="" class="form-control" />
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="filter_status" data-i18n="filter_status">Status: </label>
                                            <select name="filter_status" id="filter_status" class="form-control">
                                                <option value="">All Status</option>
                                                <option value="active">Active</option>
                                                <option value="suspend">Suspend</option>
                                                <option value="expired">Expired</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="filter_profile" data-i18n="filter_profile">Profile: </label>
                                        <input type="text" name="filter_profile" id="filter_profile" value="" class="form-control" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group row">
                                    <div class="col-md-4">
                                        <div class="form-group" style="position:relative; left:auto; display:block;">
                                            <label for="filter_created_date" data-i18n="filter_created_date">Creation Date</label>
                                            <input type="text" class="form-control format-picker" name="filter_created_date" id="filter_created_date" value=''>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group" style="position:relative; left:auto; display:block;">
                                            <label for="filter_expired_date" data-i18n="filter_expired_date">Expiry Date</label>
                                            <input type="text" class="form-control format-picker" name="filter_expired_date" id="filter_expired_date" value=''>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name='search' id='search' class="btn btn-primary waves-effect waves-light btn-search" data-i18n="login_logins_error_search">Search</button>
                            </div>

                    </div>
                </div>
            </div>
        </section-->

        <section id="css-classes" class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                    
                    <?php 
                    if($kiw_row["last_test_status"] != "success") {
                    ?>
                        <div class="alert alert-danger" role="alert">
                            Please check your configuration with HSS at Integration -> HSS.
                        </div>
                    <?php 
                    }
                    ?>
                    
                        <button type="button" class="btn btn-primary waves-effect waves-light pull-right create-btn-voucher" data-toggle="modal" data-target="#inlineForm" data-i18n="button_add_voucher">Add Sim Card</button>

                        <div class="table-responsive">
                            <table class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="table_thead_no">No</th>
                                        <th data-i18n="table_thead_code">ID Simcard / IMSI</th>
                                        <!-- <th data-i18n="table_thead_status">Private Key Simcard / KI</th> -->
                                        <!-- <th data-i18n="table_thead_profile">Link Mobile Number / ISDN</th> -->
                                        <th data-i18n="table_thead_creation_date">Is Sync</th>
                                        <th data-i18n="table_thead_creation_date">Creation Date</th>
                                        <th data-i18n="table_thead_expiry_date">Expiry Date</th>
                                        <th data-i18n="table_thead_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th data-i18n="table_tbody_loading">
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



<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Sim Card</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#" method="post">

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-6">
                            <label data-i18n="">HLRSN: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="hlrsn" name="hlrsn" class="form-control" value="1" required placeholder="1">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="">ID Sim Card / IMSI: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="imsi" name="imsi" value="502201000000001" class="form-control" required placeholder="502201000000001">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="">Private Key Simcard / KI: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="ki" name="ki" value="FF111111FFFF222222FFFFABCD000001" class="form-control" required placeholder="FF111111FFFF222222FFFFABCD000001">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="">Card Type: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="card_type" name="card_type" value="USIM" class="form-control" required placeholder="USIM">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="">ALG: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="alg" name="alg" value="MILENAGE" class="form-control" required placeholder="MILENAGE">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="">OPSNO: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="opsno" name="opsno" value="1" class="form-control" required placeholder="1">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="">Key Type: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="key_type" name="key_type" value="CLEARKEY" class="form-control" required placeholder="CLEARKEY">
                            </div>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <label data-i18n="">Link Mobile Number / ISDN: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="isdn" name="isdn" value="50220100001" class="form-control" required placeholder="50220100001">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="">TPL Type: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="tpltype" name="tpltype" value="normal" class="form-control" required placeholder="normal">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="">TPL ID: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" id="tplid" name="tplid" value="1" class="form-control" required placeholder="1">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="">Profile: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <fieldset class="form-group">
                                    <select class="select2 form-control" name="plan" id="plan" data-style="btn-default" tabindex="-98" required>

                                        <?php

                                        $kiw_row = $kiw_db->fetch_array("SELECT name FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'");

                                        foreach ($kiw_row as $record) {

                                            echo "<option value='{$record['name']}'> {$record['name']} </option> \n";
                                        }

                                        ?>

                                    </select>
                                </fieldset>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label data-i18n="">Zone Restriction: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <fieldset class="form-group">
                                    <select class="select2 form-control" name="zone" id="zone" data-style="btn-default" tabindex="-98" required>

                                        <option value="none" data-i18n="">None</option>

                                        <?php

                                        $kiw_row = $kiw_db->fetch_array("SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}'");

                                        foreach ($kiw_row as $record) {

                                            echo "<option value='{$record['name']}'> {$record['name']} </option> \n";
                                        }

                                        ?>

                                    </select>
                                </fieldset>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group" style="position:relative; left:auto; display:block;">
                                <label for="modal_1_label_expiry" data-i18n="modal_1_label_expiry">Expiry Date: </label> <span class="text-danger">*</span>
                                <input type="text" class="form-control format-picker" name="date_expiry" id="date_expiry" value='' required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_label_remark">Remark: </label>
                            <div class="form-group">
                                <input type="text" id="remark" name="remark" class="form-control">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_button_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_footer_button_create">Create</button>

                </div>

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                
            </form>
        </div>
    </div>
</div>



<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>
<script src="/assets/js/datejs/build/date.js"></script>

<link rel="stylesheet" href="/assets/css/bootstrap-datepicker.css">
<script src="/assets/js/bootstrap-datepicker.min.js"></script>