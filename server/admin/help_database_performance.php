<?php

$kiw['module'] = "Help -> Database Performance";
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


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Database Performance</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Database performance information
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section id="css-classes" class="card">

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">

                                <div class="col-md-12 database-result">

                                    <button class="btn btn-primary mb-1 waves-effect waves-light" type="button" disabled="">
                                        <span class="spinner-border spinner-border-lg" role="status" aria-hidden="true"></span>
                                        &nbsp;
                                        <span data-i18n="pls_wait">Please wait..</span>
                                    </button>

                                </div>

                            </div>
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