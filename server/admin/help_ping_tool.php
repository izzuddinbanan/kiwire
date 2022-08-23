<?php

$kiw['module'] = "Help -> Ping Tool";
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
require_once "includes/include_report.php";

$kiw_db = Database::obtain();

?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="help_ping_title">IP Ping</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="help_ping_subtitle">
                                Network ping tools
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
                        <div class="col-12">
                            <div class="form-group row">
                                <h6 class="text-bold-500" data-i18n="help_ping_ip_addr">IP Address / Domain:</h6>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group row">

                                <div class="col-md-11">
                                    <input type="text" class="form-control" name="ip_address" id="ip_address" placeholder="Example: 192.168.0.1">
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" name='search' id='search' class="btn btn-primary pull-right waves-effect waves-light btn-search" data-i18n="help_ping_ping">Ping</button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <div class="ping_result p-2">

                            <pre class="p-3 progress-space" data-i18n="help_ping_provide_domain">Please provide IP address or domain name and click [ Ping ]</pre>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

</div>

<?php

require_once "includes/include_footer.php";

?>
