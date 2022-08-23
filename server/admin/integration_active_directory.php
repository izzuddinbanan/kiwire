<?php

$kiw['module'] = "Integration -> Active Directory";
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

require_once dirname(__FILE__, 2) . "/libs/adldap/adLDAP.php";


$kiw_db = Database::obtain();


$kiw_row = $kiw_db->query_first("SELECT * FROM  kiwire_int_msad WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_int_msad(tenant_id) VALUE('$tenant_id')");



if (!empty($kiw_row['host']) && !empty($kiw_row['adminuser']) && !empty($kiw_row['adminpw']) && !empty($kiw_row['accsuffix'])) {


    try {


        $kiw_connection = new adLDAP(array(
            'account_suffix'     => $kiw_row['accsuffix'],
            'domain_controllers' => explode(",", $kiw_row['host']),
            'base_dn'            => $kiw_row['basedn'],
            'admin_username'     => $kiw_row['adminuser'],
            'admin_password'     => $kiw_row['adminpw'],
        ));


        $kiw_group_list = $kiw_connection->group()->all();


    } catch (Exception $e) {

        echo "";

    }
}



?>

<style>
    .ui-timepicker-container{ 
        z-index:1151 !important; 
    }
</style>
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_ad_title">Active Directory</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_ad_subtitle">
                                Authentication with microsoft active directory
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
                            <div class="card-header ">
                            <button type="button" class="btn btn-primary waves-effect waves-light create-btn-ad" style="display:none;" data-i18n="integration_adadd">Add Active D.</button> 
                            </div>
                            <div class="card-body">

                                <form class="update-form">

                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                                    <ul class="nav nav-tabs" role="tablist">

                                        <li class="nav-item">
                                            <a class="nav-link active" id="main-tab" data-toggle="tab" href="#main" aria-controls="main" role="tab" aria-selected="true" data-i18n="integration_ad_main">MAIN</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="mapping-tab" data-toggle="tab" href="#mapping" aria-controls="mapping" role="tab" aria-selected="false" data-i18n="integration_ad_mapping">MAPPING</a>
                                        </li>

                                    </ul>

                                    <br><br>
                                    <div class="tab-content">

                                        <div class="tab-pane active" id="main" aria-labelledby="main-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_ad_enable1">Enable</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" <?= ($kiw_row['enabled'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="enabled"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="">24 Hours</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="is_24" id="is_24" <?= ($kiw_row['is_24_hour'] == "1") ? 'checked' : '' ?> value="1" class="toggle" />
                                                            <label class="custom-control-label" for="is_24"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 time" style="display: <?= $kiw_row['is_24_hour'] ? 'none' : 'block' ?>">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="">Start Time</span> 
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name=start_time id=start_time value="<?= $kiw_row['start_time']; ?>" class="form-control datetime"  />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 time" style="display: <?= $kiw_row['is_24_hour'] ? 'none' : 'block' ?>">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="">Stop Time</span> 
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name=stop_time id=stop_time value="<?= $kiw_row['stop_time']; ?>" class="form-control datetime"  />
                                                    </div>
                                                </div>
                                            </div> -->

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_ad_domain_controller">Domain Controller</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name=host id=host value="<?= $kiw_row['host']; ?>" class="form-control" required />
                                                        <div style="font-size: smaller; padding: 10px;" data-i18n="integration_ad_hostname">Hostname / IP Address</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_ad_acc_suffix">Account Suffix</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="accsuffix" id="accsuffix" value="<?= $kiw_row['accsuffix']; ?>" class="form-control" />
                                                        <div style="font-size: smaller; padding-top: 10px;" data-i18n="integration_ad_suffix_domain">Account suffix for your domain.eg:@mydomain.local</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_ad_username">Domain Admin Username</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="adminuser" id="adminuser" value="<?= $kiw_row['adminuser']; ?>" class="form-control" required />
                                                        <div style="font-size: smaller; padding-top: 10px;" data-i18n="integration_ad_admin_username">AD Administrator/Domain Admin Username</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_ad_password">Domain Admin Password</span> <span class="text-danger">*</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="password" name="adminpw" id="adminpw" value="<?= $kiw_row['adminpw']; ?>" class="form-control" required />
                                                        <div style="font-size: smaller; padding-top: 10px;" data-i18n="integration_ad_pass_suffix_domain">Account suffix for your domain.eg:@mydomain.local</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_ad_base">Base DN</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="basedn" id="basedn" value="<?= $kiw_row['basedn']; ?>" class="form-control" />
                                                        <div style="font-size: smaller; padding-top: 10px;" data-i18n="integration_ad_optional">Optional. Your base DN can be located in the extended attributes in Active Directory Users and Computers MMC. eg: DC=mydomain,DC=local</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_ad_link_profile1">Link With Profile</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <select name="profile_master" id="profile_master" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <option value="none" data-i18n="integration_ad_none1">None</option>
                                                            <?php

                                                            $rows = "SELECT DISTINCT(name) AS name FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'";
                                                            $rows = $kiw_db->fetch_array($rows);

                                                            foreach ($rows as $record) {

                                                                $selected = "";

                                                                if ($record['name'] == $kiw_row['profile']) $selected = 'selected="selected"';

                                                                echo "<option {$selected} value=\"" . $record['name'] . "\"> " . $record['name'] . " </option> \n";
                                                            }

                                                            ?>

                                                        </select>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_ad_zone_restriction1">Zone Restriction</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <select name="allowed_zone_master" id="allowed_zone_master" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <option value="none" data-i18n="integration_ad_none2">None</option>
                                                            <?php

                                                            $rows = "SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}' GROUP BY name ORDER BY name";
                                                            $rows = $kiw_db->fetch_array($rows);

                                                            foreach ($rows as $record) {

                                                                $selected = "";

                                                                if ($record['name'] == $kiw_row['allowed_zone']) $selected = 'selected="selected"';

                                                                echo "<option value =\"$record[name]\" $selected> $record[name]</option> \n";
                                                            }

                                                            ?>
                                                        </select>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <hr>
                                                <div class="form-group row">
                                                    <div class="text-right col-md-3"></div>
                                                    <div class="col-md-7">
                                                        <a href="javascript:void(0)" class="btn btn-warning waves-effect waves-light btn-test" data-i18n="integration_ad_test">Test</a>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>


                                        <div class="tab-pane" id="mapping" aria-labelledby="mapping-tab" role="tabpanel">

                                            <div class="table-responsive">
                                                <table id="itemlist" class="table table-hover table-data">
                                                    <thead>
                                                        <tr class="text-uppercase">
                                                            <th data-i18n="integration_ad_no">No</th>
                                                            <th data-i18n="integration_ad_group_name1">Group Name</th>
                                                            <th data-i18n="integration_ad_link_profile2">Link To Profile</th>
                                                            <th data-i18n="integration_ad_zone_restriction2">Zone Restriction</th>
                                                            <th data-i18n="integration_ad_priority1">Priority</th>
                                                            <th data-i18n="integration_ad_status">Status</th>
                                                            <th data-i18n="integration_ad_action">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <th data-i18n="integration_ad_loading">
                                                            Loading..
                                                        </th>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="integration_ad_save">Save</button>
                                                                                               
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="integration_ad_add_edit">Add or Edit Active Directory</h4>

                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                <div class="modal-body">

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <label data-i18n="integration_ad_enable2">Enable: </label>
                            <input type="checkbox" class="custom-control-input" name=status id=status <?= ($kiw_row['status'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                            <label class="custom-control-label" for="status"></label>
                        </div>
                    </div>

                    <label data-i18n="integration_ad_group_name2">Group Name: </label>
                    <div class="form-group">

                        <select name="group_name" id="group_name" class="form-control">
                            <option value="none
                            " data-i18n="integration_ad_none3">None</option>
                            <?php

                            if ($kiw_group_list) {

                                foreach ($kiw_group_list as $kiw_group) {

                                    echo "<option value='$kiw_group'>$kiw_group</option>";
                                }
                            }

                            ?>
                        </select>

                    </div>

                    <label data-i18n="integration_ad_link_profile3">Link With Profile: </label>
                    <div class="form-group">

                        <select name="profile_group" id="profile_group" class="select2 form-control" data-style="btn-default" tabindex="-98">
                            <option value="none" data-i18n="integration_ad_none4">None</option>
                            <?php

                            $rows = "SELECT * FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'";
                            $rows = $kiw_db->fetch_array($rows);

                            foreach ($rows as $record) {

                                $selected = "";

                                if ($record['name'] == $kiw_row['profile']) $selected = 'selected="selected"';

                                echo "<option value =\"$record[name]\" $selected> $record[name]</option> \n";
                            }

                            ?>

                        </select>
                    </div>

                    <label data-i18n="integration_ad_zone_restriction3">Zone Restriction: </label>
                    <div class="form-group">

                        <select name="allowed_zone_group" id="allowed_zone_group" class="select2 form-control" data-style="btn-default" tabindex="-98">
                            <option value="none" data-i18n="integration_ad_none5">None</option>
                            <?php

                            $rows = "SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}' GROUP BY name ORDER BY name";
                            $rows = $kiw_db->fetch_array($rows);

                            foreach ($rows as $record) {

                                $selected = "";

                                if ($record['name'] == $kiw_row['allowed_zone']) $selected = 'selected="selected"';

                                echo "<option value =\"$record[name]\" $selected> $record[name]</option> \n";
                            }

                            ?>
                        </select>

                    </div>

                    <label data-i18n="integration_ad_priority2">Priority: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="priority" id="priority" value="" class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="integration_ad_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="integration_ad_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="integration_ad_update">Update</button>

                </div>


            </form>
        </div>
    </div>
</div>


<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>


<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>