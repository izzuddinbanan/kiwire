<?php

// $kiw['module'] = "General -> Profile";
// $kiw['page'] = "Dashboard";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['custom'] = false;

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_nav.php';
// require_once 'includes/include_access.php';
require_once "includes/include_connection.php";
require_once "includes/include_report.php";

$kiw_db = Database::obtain();

$kiw_admin = $kiw_db->query_first("SELECT fullname, email, photo FROM kiwire_admin WHERE id = '{$_SESSION['id']}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

?>

<style>
    .file-icon p {
        font-size: 20px !important;
    }
</style>

<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Administrator Profile</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Change administrator profile
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

                    <form id="profile-form" action="#" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                    <div class="row">
                        <div class="clearfix"></div>


                        <div class="col-md-6 pb-1">
                            <div class="form-group">
                                <label for="name">Fullname </label>
                                <input class="form-control" type="text" placeholder="e.g admin" autocomplete="off" name="fullname" value="<?= $kiw_admin['fullname'] ?>" autofocus="" required="" tabindex="1" />
                            </div>
                        </div>

                        
                        <div class="col-md-6 pb-1">
                            <div class="form-group">
                                <label for="name">Email </label>
                                <input class="form-control" type="text" placeholder="e.g admin@synchroweb.com" autocomplete="off" name="email" value="<?= $kiw_admin['email'] ?>" autofocus="" required="" tabindex="1" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Photo <code>Type: jpg, jpeg, png</code></label>
                                <input class="form-control dropify" type="file" autocomplete="off" name="photo" value="" accept="image/*" data-default-file='<?= $kiw_admin['photo'] &&  file_exists(dirname(__FILE__,2) . "/custom/{$_SESSION['tenant_id']}/profile/{$kiw_admin['photo']}") ? "/custom/{$_SESSION['tenant_id']}/profile/{$kiw_admin['photo']}" : "" ?>' />
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <button type="button" class="btn-save-profile btn btn-primary mr-1 waves-effect waves-float waves-light">Submit</button>
                        </div>

                    </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

</div>


<?php

require_once "includes/include_footer.php";

?>