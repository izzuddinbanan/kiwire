<?php

$kiw['module'] = "Integration -> LDAP";
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


$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_int_ldap WHERE tenant_id = '$tenant_id'");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_int_ldap(tenant_id) VALUE('$tenant_id')");


?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_ldap_title">LDAP</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="integration_ldap_subtitle">
                                Manage LDAP connection
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
                            <div class="card-header">
                                <button type="button" class="btn btn-primary waves-effect waves-light create-btn-ldap" style="display:none;" data-toggle="modal" data-target="#inlineForm" data-i18n="integration_ldap_add">Add Group Mapping</button>
                            </div>
                            <div class="card-body">

                                <form id="update-form">

                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                    
                                    <ul class="nav nav-tabs" role="tablist">

                                        <li class="nav-item">
                                            <a class="nav-link active" id="main-tab" data-toggle="tab" href="#main" aria-controls="main" role="tab" aria-selected="true" data-i18n="integration_ldap_main">MAIN</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="mapping-tab" data-toggle="tab" href="#mapping" aria-controls="mapping" role="tab" aria-selected="false" data-i18n="integration_ldap_mapping">MAPPING</a>
                                        </li>

                                    </ul>

                                    <br><br>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="main" aria-labelledby="main-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_ldap_enable">Enable</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" <?= ($kiw_row['enabled'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="enabled"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

<!-- 
                                            <div class="col-12">
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
                                                        <span data-i18n="integration_ldap_ip">LDAP IP/Hostname</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="host" id="host" value="<?= $kiw_row['host']; ?>" class="form-control" />
                                                        <span>
                                                            <div style="font-size: smaller;padding: 10px;" data-i18n="integration_ldap_hostname_addr">Hostname / IP Address</div>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_ldap_port">LDAP Port</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="port" id="port" value="<?= $kiw_row['port']; ?>" class="form-control" />
                                                        <span>
                                                            <div style="font-size: smaller;padding: 10px;" data-i18n="integration_ldap_hostname_port">Hostname / IP Port</div>
                                                        </span>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_ldap_rdn">Relative Distinguished Names (RDN)</span>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" name="rdn" id="rdn" value="<?= $kiw_row['rdn']; ?>" class="form-control" />
                                                        <span>
                                                            <div style="font-size: smaller;padding: 10px;" data-i18n="integration_ldap_change">Change RDN to your LDAP setting, eg for OpenLDAP it be uid : uid={{username}},dc=example,dc=com</div>
                                                        </span>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <span data-i18n="integration_ldap_link">Link With Profile</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <select name="profile_master" id="profile_master" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <option value="none" data-i18n="integration_ldap_none">None</option>
                                                            <?php

                                                            $rows = "SELECT name FROM kiwire_profiles WHERE tenant_id = '{$tenant_id}'";
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
                                                        <span data-i18n="integration_ldap_zone_restriction">Zone Restriction</span>
                                                    </div>
                                                    <div class="col-md-10">

                                                        <select name="allowed_zone_master" id="allowed_zone_master" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                            <option value="none" data-i18n="integration_ldap_none2">None</option>

                                                            <?php

                                                            $sql = "SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}' GROUP BY name ORDER BY name";
                                                            $rows = $kiw_db->fetch_array($sql);

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
                                                        <a href="javascript:void(0)" class="btn btn-warning waves-effect waves-light btn-test" data-i18n="integration_ldap_test">Test</a>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>


                                        <div class="tab-pane" id="mapping" aria-labelledby="mapping-tab" role="tabpanel">

                                            <div class="table-responsive">
                                                <table id="itemlist" class="table table-hover table-data">
                                                    <thead>
                                                        <tr class="text-uppercase">
                                                            <th data-i18n="integration_ldap_no">No</th>
                                                            <th data-i18n="integration_ldap_group">Group Name</th>
                                                            <th data-i18n="integration_ldap_link2">Link To Profile</th>
                                                            <th data-i18n="integration_ldap_zone_restriction2">Zone Restriction</th>
                                                            <th data-i18n="integration_ldap_priority">Priority</th>
                                                            <th data-i18n="integration_ldap_status">Status</th>
                                                            <th data-i18n="integration_ldap_action">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <th data-i18n="integration_ldap_loading">
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
                                <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="integration_ldap_save">Save</button>
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
                <h4 class="modal-title" id="myModalLabel33" data-i18n="integration_ldap_add_group">Add Group Mapping</h4>

                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                <div class="modal-body">

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <label data-i18n="integration_ldap_enable2">Enable: </label>
                            <input type="checkbox" class="custom-control-input" name=status id=status <?= ($kiw_row['status'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                            <label class="custom-control-label" for="status"></label>
                        </div>
                    </div>

                    <label data-i18n="integration_ldap_group2">Group Name: </label>
                    <div class="form-group">
                        <input type="text" name="group_name"  id="group_name" class="form-control">
                    </div>

                    <label data-i18n="integration_ldap_link3">Link With Profile: </label>
                    <div class="form-group">

                        <select name="profile_group" id="profile_group" class="select2 form-control" data-style="btn-default" tabindex="-98">
                            <option value="none" data-i18n="integration_ldap_none3">None</option>
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

                    <label data-i18n="integration_ldap_zone_restriction3">Zone Restriction: </label>
                    <div class="form-group">

                        <select name="allowed_zone_group" id="allowed_zone_group" class="select2 form-control" data-style="btn-default" tabindex="-98">
                            <option value="none" data-i18n="integration_ldap_none4">None</option>
                            <?php

                            $sql = "SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}' GROUP BY name ORDER BY name";

                            $rows = $kiw_db->fetch_array($sql);

                            foreach ($rows as $record) {

                                $selected = "";

                                if ($record['name'] == $kiw_row['allowed_zone']) $selected = 'selected="selected"';

                                echo "<option value =\"$record[name]\" $selected> $record[name]</option> \n";

                            }
                            ?>
                        </select>

                    </div>

                    <label data-i18n="integration_ldap_priority2">Priority: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="priority" id="priority" value="" class="form-control" required>
                    </div>

                </div>
                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="integration_ldap_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="integration_ldap_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="integration_ldap_update">Update</button>

                </div>

            </form>
        </div>
    </div>
</div>


<div class="modal fade text-left" id="test-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="smtp-modal-title" data-i18n="integration_ldap_test_connection">Test LDAP connection</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <form class="create-form" action="#">

                    <label data-i18n="integration_ldap_username">Username: </label>
                    <div class="form-group">
                        <input type="text" name="username" class="form-control">
                    </div>

                    <label data-i18n="integration_ldap_password">Password: </label>
                    <div class="form-group">
                        <input type="password" name="password" value="" class="form-control">
                    </div>

                </form>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal" data-i18n="integration_ldap_close">Close</button>
                <button type="button" class="btn btn-primary waves-effect waves-light btn-test-ldap" data-i18n="integration_ldap_test_btn">Test</button>
            </div>

        </div>

    </div>
</div>


<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>