<?php

$kiw['module'] = "Help -> User Account Diagnostic";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Diagnostic User Account</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Perform user account diagnostic
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="card overflow-hidden">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="tab-content">

                                <form id="form_check" class="form-horizontal" method="post">

                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                    <div class="form-body">

                                        <div class="row">

                                            <div class="col-4">
                                                <label for="first-name-column" data-i18n="form_username">Username / Voucher</label>
                                                <div class="form-label-group">
                                                    <input type="text" class="form-control" name="username" id="username" value="">
                                                </div>
                                            </div>

                                            <div class="col-4">
                                                <label for="first-name-column" data-i18n="form_pass">Password</label>
                                                <div class="form-label-group">
                                                    <input type="password" class="form-control" name="password" id="password" value="">
                                                </div>
                                            </div>
                                            
                                            <div class="col-4">
                                                <label for="first-name-column"></label>
                                                <div class="form-label-group">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 mb-1" data-i18n="form_check">Check Account</button>
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="card overflow-hidden">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="tab-content">

                            
                                <h5><b data-i18n="result">Diagnostic Result</b></h5>

                                <div class="diagnose_result" style="padding: 20px; line-height: 2rem;" data-i18n="provide_username">

                                    Please provide a username and click [Check Account] to start diagnose

                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

require_once "includes/include_footer.php";

?>
