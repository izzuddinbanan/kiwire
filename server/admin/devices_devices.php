<?php

$kiw['module'] = "Device -> Device";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Devices</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Add monitoring or mapping devices
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-device pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="btn_add_device">Add Device</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">No</th>
                                        <th data-i18n="thead_identity">Identity</th>
                                        <th data-i18n="thead_ip">IP Address</th>
                                        <th data-i18n="thead_type">Type</th>
                                        <th data-i18n="thead_vendor">Vendor</th>
                                        <th data-i18n="thead_loc">Location</th>
                                        <th data-i18n="thead_description">Description</th>
                                        <?php if ($_SESSION['access_level'] == "superuser"){ ?>
                                            <th data-i18n="thead_tenant">Tenant</th>
                                        <?php } ?>
                                        <th data-i18n="thead_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th data-i18n="tbody_loading">
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
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Device</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">

                <div class="modal-body">
                    <div class="row">

                        <!-- <div class="col-md-2">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="integration_radius_enable">24 hours: </label>
                                    <input type="checkbox" class="custom-control-input" name="enabled" id="enabled" value="1" class="toggle" />
                                    <label class="custom-control-label" for="enabled"></label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12"></div>

                        <div class="col-md-6 time">
                            <label data-i18n="">Start Time: </label>
                            <div class="form-group">
                                <input type="text" name="start_time" id="start_time" value="" class="form-control datetime">
                            </div>
                        </div>

                        <div class="col-md-6 time">
                            <label data-i18n="">Stop Time: </label>
                            <div class="form-group">
                                <input type="text" name="stop_time" id="stop_time" value=""  class="form-control datetime">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <label data-i18n="integration_radius_enable">is Virtual ?: </label>
                                    <input type="checkbox" class="custom-control-input" name="is_virtual" id="is_virtual" value="1" class="toggle" />
                                    <label class="custom-control-label" for="is_virtual"></label>
                                </div>
                                <small>**This feature will overwrite nasid with ssid</small>
                            </div>
                        </div> -->


                        <div class="col-md-12">
                            <label data-i18n="modal_1_device_type">Device Type: </label>
                            <div class="form-group">
                                <select name="device_type" id="device_type" class="select2 form-control change-device-type" data-style="btn-default">
                                    <option value="controller" data-i18n="device_type_controller">Controller</option>
                                    <option value="wifiap" data-i18n="device_type_wifiap">Wifi Access Point</option>
                                    <option value="switch" data-i18n="device_type_switch">Switch</option>
                                    <option value="router" data-i18n="device_type_router">Router</option>
                                    <option value="tmruijie" data-i18n="device_type_tmruijie">Traffic Management - Ruijie: RG-PA*</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6 controller provider-input">
                            <div class="col-12">
                                <div class="form-group row" id="vendor_input">
                                    <label data-i18n="modal_1_vendor">Vendor: </label>
                                    <select name="vendor" id="vendor" class="select2 form-control change-vendor" data-style="btn-default">
                                        <option value="mikrotik" data-i18n="vendor_mikrotik">Mikrotik</option>
                                        <option value="wifidog" data-i18n="vendor_wifidog">WifiDog</option>
                                        <option value="cmcc" data-i18n="vendor_cmcc">CMCC</option>
                                        <option value="cisco_wlc" data-i18n="vendor_cisco_wlc">Cisco WLC</option>
                                        <option value="nomadix" data-i18n="vendor_nomadix">Nomadix</option>
                                        <option value="nomadix_xml" data-i18n="vendor_nomadix_xml">Nomadix - XML</option>
                                        <option value="meraki" data-i18n="vendor_meraki">Meraki</option>
                                        <option value="fortigate" data-i18n="vendor_fortigate">FortiOS</option>
                                        <option value="fortiap" data-i18n="vendor_fortiap">FortiAP</option>
                                        <option value="ruckus_ap" data-i18n="vendor_ruckus_ap">Ruckus AP</option>
                                        <option value="ruckus_scg" data-i18n="vendor_ruckus_scg">Ruckus SCG</option>
                                        <option value="ruckus_vsz" data-i18n="vendor_ruckus_vsz">Ruckus vSZ</option>
                                        <option value="motorola" data-i18n="vendor_motorola">Zebra Wifi</option>
                                        <option value="xirrus" data-i18n="vendor_xirrus">Xirrus</option>
                                        <option value="chillispot" data-i18n="vendor_chillispot">Chillispot</option>
                                        <option value="aruba" data-i18n="vendor_aruba">Aruba</option>
                                        <option value="aruba_os" data-i18n="vendor_aruba_os">Aruba OS</option>
                                        <option value="cambium" data-i18n="vendor_cambium">Cambium</option>
                                        <option value="huawei" data-i18n="vendor_huawei">Huawei</option>
                                        <option value="huawei-nce" data-i18n="">Huawei NCE</option>
                                        <option value="huawei-cloud-ugw" data-i18n="">Huawei Cloud UGW</option>
                                        <option value="pfsense" data-i18n="vendor_pfsense">Pfsense</option>
                                        <option value="sundray" data-i18n="vendor_sundray">Sundray</option>
                                        <option value="engenius" data-i18n="vendor_engenius">EnGenius</option>
                                        <option value="ubnt" data-i18n="vendor_ubnt">Ubiquiti</option>
                                        <option value="ubnt_controller" data-i18n="vendor_ubnt_controller">Ubiquiti Controller</option>
                                        <option value="other" data-i18n="vendor_other">Other</option>
                                        <option value="virtual-nas" data-i18n="virtual_nas">virtual NAS</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    
                        <div class="col-md-6">
                            <label data-i18n="modal_1_identity">Identity: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                            <input type="text" name="unique_id" id="unique_id" class="form-control required" placeholder="eg: Identity name set in mikrotik"  required/>
                        </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_device_ip">IP Address: </label> <span class="text-danger">*</span>
                            <div class="form-group">
                                <input type="text" name="device_ip" id="device_ip" class="form-control required" placeholder="eg: 10.10.20.1" required/>
                                <label data-i18n="modal_1_device_ip_label">IP Address of the device must be unique</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 wifidog provider-input">
                            <div class="col-12 ">
                                <div class="form-group row">
                                    <label data-i18n="modal_1_username">Username: </label>
                                    <input type="text" name="username" id="username" value="" placeholder="Username" class="form-control "/>
                                </div>
                            </div>
                        </div>
                    
                        <div class="col-md-6  wifidog provider-input">
                            <div class="col-12">
                                <div class="form-group row">
                                    <label data-i18n="modal_1_password">Password: </label>
                                    <input type="password" name="password" id="password" value="" placeholder="Uppercase/lowercase/numeric/special character" class="form-control"/>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label data-i18n="modal_1_address">Address: </label>
                            <div class="form-group">
                                <textarea rows="3" cols="5" id="location" name="location" class="auto form-control"></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-6  wifidog provider-input">
                            <div class="col-12">
                                <div class="form-group row">
                                    <label data-i18n="modal_1_shared_secret">Shared Secret Key: </label>
                                    <input type="text" name="shared_secret" id="shared_secret" value="" placeholder="Secret Key" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 wifidog provider-input">
                            <div class="col-12">
                                <div class="form-group row">
                                    <label data-i18n="modal_1_coa_port">COA Port: </label>
                                    <input type="text" name="coa_port" id="coa_port" value="" placeholder="eg: 3799" class="form-control" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label data-i18n="modal_1_description">Description: </label>
                            <div class="form-group">
                                <input type="text" name="description" id="description" value="" placeholder="remark" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label data-i18n="modal_1_monitor_method">Monitoring Method: </label>
                                <select name="monitor_method" id="monitor_method" class="select2 form-control" data-style="btn-default">
                                    <option value="none" data-i18n="monitor_method_none">None</option>
                                    <option value="ping" data-i18n="monitor_method_ping">Ping</option>
                                    <option value="snmp" data-i18n="monitor_method_snmp">SNMP</option>
                                    <option value="wifidog" data-i18n="monitor_method_wifidog">Wifidog</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6 wifidog-only provider-input">
                            <div class="">
                                <div class="form-group">
                                    <label data-i18n="modal_1_seamless_type">Seamless Roaming Interval: </label>
                                    <select name="seamless_type" id="seamless_type" class="select2 form-control" data-style="btn-default">
                                        <option value="disabled" data-i18n="seamless_type_disabled">Disabled</option>
                                        <option value="hour" data-i18n="seamless_type_hour">1 Hour</option>
                                        <option value="day" data-i18n="seamless_type_day">1 Day</option>
                                        <option value="week" data-i18n="seamless_type_week">1 Week</option>
                                        <option value="month" data-i18n="seamless_type_month">1 Month</option>
                                        <option value="unlimited" data-i18n="seamless_type_unlimited">Unlimited</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    
                        <div class="col-md-6">
                            <label data-i18n="modal_1_community">Community: </label>
                            <div class="form-group">
                                <input type="text" name="community" id="community" value="" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="snmpv" data-i18n="modal_1_snmpv">SNMP Version: </label>
                            <div class="form-group">
                                <select name="snmpv" id="snmpv" class="select2 form-control">
                                    <option value="1" data-i18n="snmpv_1">1</option>
                                    <option value="2c" data-i18n="snmpv_2c">2c</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="mib" data-i18n="modal_1_mib">MIB: </label>
                            <div class="form-group">
                                <?php
                                $mib_list = $kiw_db->fetch_array("SELECT `mib_name` FROM `kiwire_nms_mib` WHERE `tenant_id`='$tenant_id'");
                                ?>
                                <select name="mib" id="mib" class="select2 form-control">
                                    <option value=""></option>
                                    <?php foreach ($mib_list as $indexkey => $e_mib) : ?>
                                        <option value="<?= $e_mib['mib_name'] ?>"><?= $e_mib['mib_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_btn_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_footer_btn_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="modal_1_footer_btn_update">Update</button>

                </div>

            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade text-left" id="import-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <form class="import_account" action="">

                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_2_title">Import Devices</h4>
                    <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-12">
                            <div style="margin-bottom: 10px;">
                                <span class="" data-i18n="modal_2_span_template">Only file that using our template will be processed.</span> <br>
                                <a href="/user/templates/nas-import-template.csv" download>
                                    <span class="flang-form_3_desc_2" data-i18n="modal_2_span_download_sample">Click here to download sample template.</span>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group">
                                <label data-i18n="modal_3_label_csv">CSV with device information: </label>
                                <div class="custom-file">
                                    <input type="file" name="accounts_file" accept=".csv" class="custom-file-input" />
                                    <label class="custom-file-label" for="logo" data-i18n="modal_3_label_choose_file">Choose file</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <label data-i18n="modal_1_device_type">Device Type: </label>
                            <div class="form-group">
                                <select name="device_type" class="select2 form-control change-device-type" data-style="btn-default">
                                    <option value="controller" data-i18n="device_type_controller">Controller</option>
                                    <option value="wifiap" data-i18n="device_type_wifiap">Wifi Access Point</option>
                                    <option value="switch" data-i18n="device_type_switch">Switch</option>
                                    <option value="router" data-i18n="device_type_router">Router</option>
                                    <option value="tmruijie" data-i18n="device_type_tmruijie">Traffic Management - Ruijie: RG-PA*</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-12 controller provider-input">
                            <div class="col-12">
                                <div class="form-group row" >
                                    <label data-i18n="modal_1_vendor">Vendor: </label>
                                    <select name="vendor" class="select2 form-control change-vendor" data-style="btn-default">
                                        <option value="mikrotik" data-i18n="vendor_mikrotik">Mikrotik</option>
                                        <option value="wifidog" data-i18n="vendor_wifidog">WifiDog</option>
                                        <option value="cmcc" data-i18n="vendor_cmcc">CMCC</option>
                                        <option value="cisco_wlc" data-i18n="vendor_cisco_wlc">Cisco WLC</option>
                                        <option value="nomadix" data-i18n="vendor_nomadix">Nomadix</option>
                                        <option value="nomadix_xml" data-i18n="vendor_nomadix_xml">Nomadix - XML</option>
                                        <option value="meraki" data-i18n="vendor_meraki">Meraki</option>
                                        <option value="fortigate" data-i18n="vendor_fortigate">FortiOS</option>
                                        <option value="fortiap" data-i18n="vendor_fortiap">FortiAP</option>
                                        <option value="ruckus_ap" data-i18n="vendor_ruckus_ap">Ruckus AP</option>
                                        <option value="ruckus_scg" data-i18n="vendor_ruckus_scg">Ruckus SCG</option>
                                        <option value="ruckus_vsz" data-i18n="vendor_ruckus_vsz">Ruckus vSZ</option>
                                        <option value="motorola" data-i18n="vendor_motorola">Zebra Wifi</option>
                                        <option value="xirrus" data-i18n="vendor_xirrus">Xirrus</option>
                                        <option value="chillispot" data-i18n="vendor_chillispot">Chillispot</option>
                                        <option value="aruba" data-i18n="vendor_aruba">Aruba</option>
                                        <option value="aruba_os" data-i18n="vendor_aruba_os">Aruba OS</option>
                                        <option value="cambium" data-i18n="vendor_cambium">Cambium</option>
                                        <option value="huawei" data-i18n="vendor_huawei">Huawei</option>
                                        <option value="huawei-nce" data-i18n="">Huawei NCE</option>
                                        <option value="huawei-cloud-ugw" data-i18n="">Huawei Cloud UGW</option>
                                        <option value="pfsense" data-i18n="vendor_pfsense">Pfsense</option>
                                        <option value="sundray" data-i18n="vendor_sundray">Sundray</option>
                                        <option value="engenius" data-i18n="vendor_engenius">EnGenius</option>
                                        <option value="ubnt" data-i18n="vendor_ubnt">Ubiquiti</option>
                                        <option value="ubnt_controller" data-i18n="vendor_ubnt_controller">Ubiquiti Controller</option>
                                        <option value="virtual-nas" data-i18n="virtual_nas">virtual NAS</option>
                                        <option value="other" data-i18n="vendor_other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_btn_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-import" data-i18n="modal_1_footer_btn_create">Create</button>
                </div>

            </form>
        </div>
    </div>
</div>


<?php if ($_SESSION['access_level'] == "superuser"){ ?>

<div class="modal fade text-left" id="change-tenant" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel17">Change Tenant</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">

                <select name="new-tenant" id="new-tenant" class="form-control">

                    <?php

                    if (!empty($_SESSION['tenant_allowed'])){


                        $kiw_tenants = explode(",", $_SESSION['tenant_allowed']);

                        foreach ($kiw_tenants as $kiw_tenant){

                            echo "<option value='{$kiw_tenant}'>{$kiw_tenant}</option>";

                        }


                    } else {

                        $kiw_tenants = $kiw_db->fetch_array("SELECT tenant_id FROM kiwire_clouds");

                        foreach ($kiw_tenants as $kiw_tenant){

                            echo "<option value='{$kiw_tenant['tenant_id']}'>{$kiw_tenant['tenant_id']}</option>";

                        }

                    }

                    ?>

                </select>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary waves-effect waves-light btn-change-tenant">Confirm</button>
            </div>
        </div>
    </div>
</div>

<?php } ?>

<script>

    <?php if ($_SESSION['access_level'] == "superuser"){ ?>
    var max_column = true
    <?php } else { ?>
    var max_column = false
    <?php } ?>

</script>

<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>