<?php

$kiw['module'] = "Help -> Version";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;


require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_nav.php';
require_once 'includes/include_access.php';
require_once "includes/include_report.php";


// $kiw_temp = @file_get_contents(dirname(__FILE__, 2) . "version.json");

$kiw_temp = @file_get_contents("/var/www/kiwire/version.json");

$kiw_version = json_decode($kiw_temp, true);


?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="help_sources_credits_title">Version</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="help_sources_credits_subtitle">
                                Description of system version and updates
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section id="nested-media-list">
        <div class="row match-height">
            <div class="col-sm-12">
                <div class="card border-success bg-transparent">
                    <div class="card-body">
                        <div class="card-text">
                            <br>
                            <table>

                                <?php

                                foreach ($kiw_version as $row => $innerArray) { ?>

                                    <tr>
                                        <td>
                                            
                                            <?php

                                            if ($row == "details") {

                                                echo "<p><h5></h5></p>";
                                            } else {

                                                echo "<p><b style='text-transform: uppercase;'>$row</b> : $innerArray</p>";
                                            }

                                            ?>
                                        </td>
                                    </tr>

                                    <?php

                                    foreach ($innerArray as $innerRow => $value) { ?>

                                        <tr>
                                            <td>
                                                <div class="alert bg-gradient-success" role="alert">
                                                    <div class="alert-body">
                                                        <?php echo "<h5 class='text-white' style='text-transform: uppercase;'>$innerRow</h5>"; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <?php

                                        foreach ($value as $inner => $val) { ?>

                                            <tr>
                                                <td>
                                                    <ul>
                                                        <li><?php echo "<p>$val</p>"; ?></li>
                                                    </ul>
                                                </td>
                                            </tr>

                                <?php

                                        }
                                    }
                                }

                                ?>
                            </table>
                            </br>
                        </div>
                    </div>
                </div>
            </div>
    </section>

</div>




<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>