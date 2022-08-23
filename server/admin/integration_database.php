<?php

$kiw['module'] = "Integration -> Database";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_int_external_db WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_int_external_db(tenant_id) VALUE('{$tenant_id}')");

?>

<form id="update-form" class="form-horizontal" method="post">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="integration_db_title">External Database</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active" data-i18n="integration_db_subtitle">
                                    Manage connection to external database for user verification
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
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="general" aria-labelledby="general-tab" role="tabpanel">
                                            <form id="update-form" class="form-horizontal" method="post">

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-2">
                                                            <span data-i18n="integration_db_enable">Enable</span>
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
                                                            <span data-i18n="integration_db_server">Server IP / Domain</span>
                                                        </div>
                                                        <div class="col-md-10"><input type="text" name="host" id="host" value="<?= $kiw_row['host']; ?>" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-2">
                                                            <span data-i18n="integration_db_port">Port Number</span>
                                                        </div>
                                                        <div class="col-md-10"><input type="text" name="port" id="port" value="<?= $kiw_row['port']; ?>" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-2">
                                                            <span data-i18n="integration_db_username">Username</span> </div>
                                                        <div class="col-md-10"><input type="text" name="user" id="user" value="<?= $kiw_row['user']; ?>" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-2">
                                                            <span data-i18n="integration_db_pass">Password</span> </div>
                                                        <div class="col-md-10"><input type="password" name="pass" id="pass" value="<?= $kiw_row['pass']; ?>" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-2">
                                                            <span data-i18n="integration_db_name">Database Name</span>
                                                        </div>
                                                        <div class="col-md-10"><input type="text" name="dbname" id="dbname" value="<?= $kiw_row['dbname']; ?>" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-2">
                                                            <span data-i18n="integration_db_query">Query</span>
                                                        </div>
                                                        <div class="col-md-10"><input type="text" name="command" id="command" value="<?= $kiw_row['command']; ?>" class="form-control" />
                                                            <div class="col-md-12 col-md-offset-2" style="margin-top: 2px;"><span style="font-weight: bold;" >{{username}}</span> will be replace by username and <span style="font-weight: bold;">{{password}}</span> will be replace by password</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-2">
                                                            <span data-i18n="integration_db_type">Database Type</span>
                                                        </div>
                                                        <div class="col-md-10">
                                                            <select name="dbtype" id="dbtype" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                                <option value="mysql" <?= ($kiw_row['dbtype'] == "mysql" ? "selected" : "") ?>>MySQL</option>
                                                                <option value="mssql" <?= ($kiw_row['dbtype'] == "mssql" ? "selected" : "") ?>>MSSQL</option>
                                                                <!-- <option value="oracle" <?= ($kiw_row['dbtype'] == "oracle" ? "selected" : "") ?>>Oracle DB</option> -->
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-2">
                                                            <span data-i18n="integration_db_link">Link With Profile</span>
                                                        </div>
                                                        <div class="col-md-10">
                                                            <select name="profile" id="profile" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                                <option value="none" data-i18n="integration_db_none">None</option>
                                                                <?
                                                                $sql = "select * from kiwire_profiles where tenant_id = '{$tenant_id}' group by name";
                                                                $rows = $kiw_db->fetch_array($sql);
                                                                foreach ($rows as $record) {
                                                                    $selected = "";
                                                                    if ($record['name'] == $kiw_row['profile']) $selected = 'selected="selected"';
                                                                    echo "<option value =\"$record[name]\" $selected> $record[name]</option> \n";
                                                                }
                                                                ?>

                                                            </select>

                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-2">
                                                            <span data-i18n="integration_db_zone_restriction">Zone Restriction</span>
                                                        </div>
                                                        <div class="col-md-10">
                                                            <select name="allowed_zone" id="allowed_zone" class="select2 form-control" data-style="btn-default" tabindex="-98">
                                                                <option value="none" data-i18n="integration_db_none2">None</option>
                                                                <?
                                                                $sql = "select * from kiwire_allowed_zone where tenant_id = '{$tenant_id}' group by name order by name";
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
                                                    <div class="form-group row">
                                                        <div class="col-md-2">
                                                            <span data-i18n="integration_db_validity">Validity</span> 
                                                        </div>
                                                        <div class="col-md-10"><input type="text" name="validity" id="validity" value="<?= $kiw_row['validity']; ?>" class="form-control" />
                                                            <div style="font-size: smaller; padding: 10px; " data-i18n="integration_db_days">Days</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>

                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="text-right col-md-3">
                                                            <span data-i18n="integration_hss_last_test">&nbsp;</span>
                                                            &nbsp;
                                                        </div>
                                                        <div class="col-md-7">
                                                            <button type="button" class="btn btn-warning test-button waves-effect waves-light" data-i18n="integration_db_test">Test Connection </button>&nbsp;

                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <input type="hidden" name="update" value="true" />
                                                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="integration_db_save">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>


<?php

require_once "includes/include_footer.php";

?>


<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>