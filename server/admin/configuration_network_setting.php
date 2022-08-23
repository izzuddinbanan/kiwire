<?php

$kiw['module'] = "Configuration -> Network Setting";
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

$kiw_network = `sudo /usr/bin/nmcli -m multiline con show`;
$kiw_network = explode(PHP_EOL, $kiw_network);


$kiw_nminfo = array();


foreach ($kiw_network as $kiw_item) {

    if (!empty(trim($kiw_item))) {


        $kiw_temp = explode(":", $kiw_item);

        $kiw_nminfo[preg_replace('/\s*/m', '', $kiw_temp[0])] = preg_replace('/\s*/m', '', $kiw_temp[1]);


    }

}


unset($kiw_network);

$kiw_network = `sudo /usr/bin/nmcli device show {$kiw_nminfo['DEVICE']}`;
$kiw_network = explode(PHP_EOL, $kiw_network);

foreach ($kiw_network as $item) {

    if (!empty(trim($item))) {


        $kiw_temp = explode(":", $item);

        $kiw_nminfo[preg_replace('/\s*/m', '', $kiw_temp[0])] = preg_replace('/\s*/m', '', $kiw_temp[1]);


    }

}


unset($kiw_network);

$kiw_nminfo['HOSTNAME'] = `sudo /usr/bin/hostname`;


?>

<form id="update-form" class="form-horizontal" method="post">

    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Network Setting</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active" data-i18n="subtitle">
                                    Manage your server connection setting
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
                                <div class="card-header pull-right">
                                    <button type="button" class="btn btn-primary save-button waves-effect waves-light" data-i18n="btn_save">Save</button>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="general" aria-labelledby="general-tab" role="tabpanel">
                                            <br><br><br>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="text-right col-md-3">
                                                        <span data-i18n="span_network_name">Network Name</span>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <label for="" class="badge badge-primary"><?= $kiw_nminfo['DEVICE']; ?></label>
                                                        <input type="hidden" name="connection_name" id="connection_name" value="<?= $kiw_nminfo['DEVICE'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="text-right col-md-3">
                                                        <span data-i18n="span_hostname">Hostname</span>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <input type="text" name="hostname" id="hostname" value="<?= $kiw_nminfo['HOSTNAME'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="text-right col-md-3">
                                                        <span data-i18n="span_ip_addres">IP Address</span>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <input type="text" name="ip_address" id="ip_address" value="<?= $kiw_nminfo['IP4.ADDRESS[1]'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="text-right col-md-3">
                                                        <span data-i18n="span_gateway_ip">Gateway IP Address</span>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <input type="text" name="gateway_ip" id="gateway_ip" value="<?= $kiw_nminfo['IP4.GATEWAY'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="text-right col-md-3">
                                                        <span data-i18n="span_dns_1">DNS Server 1</span>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <input type="text" name="dns_one" id="dns_one" value="<?= $kiw_nminfo['IP4.DNS[1]'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="text-right col-md-3">
                                                        <span data-i18n="span_dns_2">DNS Server 2</span>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <input type="text" name="dns_two" id="dns_two" value="<?= $kiw_nminfo['IP4.DNS[2]'] ?>" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>

                                            <input type="hidden" name="update" value="true" />

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="text-right col-md-3"></div>

                                                    <div class="col-md-7" style="font-weight: bold;" data-i18n="span_process_note">
                                                        * This process might take around a minute to complete.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

    </div>
</form>

<?php require_once "includes/include_footer.php"; ?>