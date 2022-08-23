<?php

$kiw['module'] = "Help -> System Quick Fix";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_nav.php';
require_once 'includes/include_access.php';

?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="help_system_quick_fix_title">System Quick Fix</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="help_system_quick_fix_subtitle">
                                Quickly fix simple system issues
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
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="thead-dark">
                                <tr>
                                    <th scope="col" data-i18n="help_system_quick_fix_issues">KNOWN ISSUES / PROBLEMS</th>
                                    <th scope="col" data-i18n="help_system_quick_fix_action">ACTION</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td data-i18n="help_system_quick_fix_reset_operator">Reset operator role</td>
                                    <td>
                                        <button type="button" name="reset" value="" class="btn btn-icon btn-primary btn-sm btn-take-action"><i class="feather icon-check-circle"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-i18n="help_system_quick_fix_clear_chached">Clear cached</td>
                                    <td>
                                        <button type="button" name="clear_cache" value="true" class="btn btn-icon btn-primary btn-sm btn-take-action"><i class="feather icon-check-circle"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-i18n="help_system_quick_fix_system_db">System / Database / PHP Timezone</td>
                                    <td>
                                        <button type="button" name="set_timezone" value="true" class="btn btn-icon btn-primary btn-sm btn-take-action"><i class="feather icon-check-circle"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-i18n="help_system_quick_fix_no_file">No file display / unable to upload in Login Engine -> Media</td>
                                    <td>
                                        <button type="button" name="set_custom_permission" value="true" class="btn btn-icon btn-primary btn-sm btn-take-action"><i class="feather icon-check-circle"></i></button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require_once "includes/include_footer.php"; ?>
