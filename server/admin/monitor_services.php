<?php


$kiw['module'] = "Report -> Monitor -> Service";
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
        <div class="content-header-left col-md-9 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-11">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_logins_record_title">Monitor : Service</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_logins_record_subtitle">
                            Critical system services health report
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>


   
    <div class="row append">

           

        
    </div>
      


</div>


<script>
    var access_user = '<?= $_SESSION['access_level'] ?>';
</script>


<?php

require_once "includes/include_footer.php";


?>
