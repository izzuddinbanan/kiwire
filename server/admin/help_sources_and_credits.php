<?php

$kiw['module'] = "Help -> Sources & Credits";
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

?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="help_sources_credits_title">Sources & Credits</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="help_sources_credits_subtitle">
                                External code and their licenses
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">

    <div class="card-content collapse show" aria-expanded="true">
        <div class="card-body">
            <div class="card-text">
                <p data-i18n="help_sources_credits_used">We've used the following images, icons or other plugins URL, License as listed.</p>
                <p data-i18n="help_sources_credits_licensed_menu"><strong><?= sync_brand_decrypt(SYNC_PRODUCT) ?> contains licensed <em>mmenu</em> within the package.</strong></p>
            </div>

            <h4 data-i18n="help_sources_credits_core">Core System</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th data-i18n="help_sources_credits_name">Name</th>
                        <th data-i18n="help_sources_credits_url">URL</th>
                        <th data-i18n="help_sources_credits_license">License</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                       <td><strong>FreeRADIUS</strong></td>
                       <td><a href="https://freeradius.org/" target="_blank">https://freeradius.org/</a></td>
                       <td>GPLv2</td>
                   </tr>
                   <tr>
                       <td><strong>Redis</strong></td>
                       <td><a href="https://redis.io/" target="_blank">https://redis.io/</a></td>
                       <td>BSD 3-clause</td>
                   </tr>
                   <tr>
                       <td><strong>PHP</strong></td>
                       <td><a href="https://www.php.net/" target="_blank">https://www.php.net/</a></td>
                       <td>PHP License v3.01</td>
                   </tr>
                   <tr>
                       <td><strong>NGINX</strong></td>
                       <td><a href="https://www.nginx.com/" target="_blank">https://www.nginx.com/</a></td>
                       <td>2-clause BSD</td>
                   </tr>
                   <tr>
                       <td><strong>CentOS 7</strong></td>
                       <td><a href="https://www.centos.org/" target="_blank">https://www.centos.org/</a></td>
                       <td>GNU GPL and other licenses</td>
                   </tr>
                   <tr>
                       <td><strong>Swoole</strong></td>
                       <td><a href="https://www.swoole.co.uk/" target="_blank">https://www.swoole.co.uk/</a></td>
                       <td>Apache 2.0</td>
                   </tr>
                   <tr>
                       <td><strong>ContentBuilder</strong></td>
                       <td><a href="" target="_blank"></a></td>
                       <td>STANDARD DEVELOPER LICENSE AGREEMENT</td>
                   </tr>
                   <tr>
                       <td><strong>adLDAP</strong></td>
                       <td><a href="https://github.com/adldap/adLDAP" target="_blank">https://github.com/adldap/adLDAP</a></td>
                       <td>GNU LGPL v2.1</td>
                   </tr>
                   <tr>
                       <td><strong>Device Detector</strong></td>
                       <td><a href="http://devicedetector.net/" target="_blank">http://devicedetector.net/</a></td>
                       <td>GNU LGPL v3.0</td>
                   </tr>
                   <tr>
                       <td><strong>Mad Mimi</strong></td>
                       <td><a href="http://madmimi.com/" target="_blank">http://madmimi.com/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>MailChimp</strong></td>
                       <td><a href="https://github.com/drewm/mailchimp-api" target="_blank">https://github.com/drewm/mailchimp-api</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>PayPal</strong></td>
                       <td><a href="http://paypal.github.io/PayPal-PHP-SDK/" target="_blank">http://paypal.github.io/PayPal-PHP-SDK/</a></td>
                       <td>PAYPAL, INC</td>
                   </tr>
                   <tr>
                       <td><strong>PHPMailer</strong></td>
                       <td><a href="https://github.com/PHPMailer/PHPMailer/" target="_blank">https://github.com/PHPMailer/PHPMailer/</a></td>
                       <td>GNU LGPL v2.1</td>
                   </tr>
                   <tr>
                       <td><strong>Stripe</strong></td>
                       <td><a href="https://stripe.com/en-my" target="_blank">https://stripe.com/en-my</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>SmartBanner JS</strong></td>
                       <td><a href="https://www.npmjs.com/package/smartbanner.js" target="_blank">https://www.npmjs.com/package/smartbanner.js</a></td>
                       <td>GNU LGPL v3.0</td>
                   </tr>
                   <tr>
                       <td><strong>Twilio</strong></td>
                       <td><a href="https://www.twilio.com/docs/libraries/php" target="_blank">https://www.twilio.com/docs/libraries/php</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Spyc</strong></td>
                       <td><a href="https://github.com/mustangostang/spyc/" target="_blank">https://github.com/mustangostang/spyc/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>PHP Uploader</strong></td>
                       <td><a href="https://github.com/CreativeDream/php-uploader" target="_blank">https://github.com/CreativeDream/php-uploader</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Simple PHP Captcha</strong></td>
                       <td><a href="https://labs.abeautifulsite.net/simple-php-captcha/" target="_blank">https://labs.abeautifulsite.net/simple-php-captcha/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>PHP QR Code</strong></td>
                       <td><a href="https://sourceforge.net/projects/phpqrcode/" target="_blank">https://sourceforge.net/projects/phpqrcode/</a></td>
                       <td>GNU LGPL v3.0</td>
                   </tr>

                </tbody>
            </table>

            <h4>Plugins</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>URL</th>
                        <th>License</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                       <td><strong>Animate.css</strong></td>
                       <td><a href="https://daneden.github.io/animate.css/" target="_blank">https://daneden.github.io/animate.css/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Bootstrap TouchSpin</strong></td>
                       <td><a href="http://www.virtuosoft.eu/code/bootstrap-touchspin/" target="_blank">http://www.virtuosoft.eu/code/bootstrap-touchspin/</a></td>
                       <td>Apache 2.0</td>
                   </tr>
                   <tr>
                       <td><strong>Select2</strong></td>
                       <td><a href="https://select2.github.io/" target="_blank">https://select2.github.io/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>jqBootstrapValidation</strong></td>
                       <td><a href="https://reactiveraven.github.io/jqBootstrapValidation/" target="_blank">https://reactiveraven.github.io/jqBootstrapValidation/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Quill</strong></td>
                       <td><a href="http://quilljs.com/" target="_blank">http://quilljs.com/</a></td>
                       <td>BSD-3-Clause</td>
                   </tr>
                   <tr>
                       <td><strong>pickadate.js</strong></td>
                       <td><a href="http://amsul.ca/pickadate.js/" target="_blank">http://amsul.ca/pickadate.js/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>DataTables</strong></td>
                       <td><a href="https://datatables.net/" target="_blank">https://datatables.net/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>SweetAlert2</strong></td>
                       <td><a href="https://sweetalert2.github.io/" target="_blank">https://sweetalert2.github.io/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Prism</strong></td>
                       <td><a href="http://prismjs.com/" target="_blank">http://prismjs.com/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>noUiSlider</strong></td>
                       <td><a href="https://refreshless.com/nouislider/" target="_blank">https://refreshless.com/nouislider/</a></td>
                       <td>WTFPL</td>
                   </tr>
                   <tr>
                       <td><strong>Toastr</strong></td>
                       <td><a href="http://codeseven.github.io/toastr/demo.html" target="_blank">http://codeseven.github.io/toastr/demo.html</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Coming Soon</strong></td>
                       <td><a href="http://hilios.github.io/jQuery.countdown/" target="_blank">http://hilios.github.io/jQuery.countdown/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Bootstrap Tree View</strong></td>
                       <td><a href="http://jonmiles.github.io/bootstrap-treeview/" target="_blank">http://jonmiles.github.io/bootstrap-treeview/</a></td>
                       <td>Apache 2.0</td>
                   </tr>
                   <tr>
                       <td><strong>jQuery Steps</strong></td>
                       <td><a href="http://www.jquery-steps.com/" target="_blank">http://www.jquery-steps.com/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>jQuery Validation</strong></td>
                       <td><a href="https://jqueryvalidation.org/" target="_blank">https://jqueryvalidation.org/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>jQuery UI</strong></td>
                       <td><a href="http://jqueryui.com" target="_blank">http://jqueryui.com</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Perfect Scrollbar</strong></td>
                       <td><a href="https://www.npmjs.com/package/perfect-scrollbar" target="_blank">https://www.npmjs.com/package/perfect-scrollbar</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Block UI</strong></td>
                       <td><a href="http://malsup.com/jquery/block/" target="_blank">http://malsup.com/jquery/block/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Screenfull</strong></td>
                       <td><a href="https://github.com/sindresorhus/screenfull.js/" target="_blank">https://github.com/sindresorhus/screenfull.js/</a></td>
                       <td>WTFPL</td>
                   </tr>
                   <tr>
                       <td><strong>Pace</strong></td>
                       <td><a href="https://github.hubspot.com/pace/" target="_blank">https://github.hubspot.com/pace/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Popper</strong></td>
                       <td><a href="https://popper.js.org/" target="_blank">https://popper.js.org/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Waves</strong></td>
                       <td><a href="https://github.com/fians/Waves" target="_blank">https://github.com/fians/Waves</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Dragula</strong></td>
                       <td><a href="https://bevacqua.github.io/dragula/" target="_blank">https://bevacqua.github.io/dragula/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Dropzone.js</strong></td>
                       <td><a href="http://www.dropzonejs.com/" target="_blank">http://www.dropzonejs.com/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>jQuery File Upload</strong></td>
                       <td><a href="https://blueimp.github.io/jQuery-File-Upload/" target="_blank">https://blueimp.github.io/jQuery-File-Upload/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>AgGrid</strong></td>
                       <td><a href="https://www.ag-grid.com/" target="_blank">https://www.ag-grid.com/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Media Player</strong></td>
                       <td><a href="https://plyr.io" target="_blank">https://plyr.io</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>FullCalendar</strong></td>
                       <td><a href="https://fullcalendar.io/" target="_blank">https://fullcalendar.io/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>i18next</strong></td>
                       <td><a href="http://i18next.com/" target="_blank">http://i18next.com/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Tour</strong></td>
                       <td><a href="https://github.com/shipshapecode/shepherd/" target="_blank">https://github.com/shipshapecode/shepherd/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Context Menu</strong></td>
                       <td><a href="https://github.com/swisnl/jQuery-contextMenu/" target="_blank">https://github.com/swisnl/jQuery-contextMenu/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Apex Chart</strong></td>
                       <td><a href="https://apexcharts.com/docs/installation/" target="_blank">https://apexcharts.com/docs/installation/</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>EChart</strong></td>
                       <td><a href="https://ecomfe.github.io/echarts/index-en.html" target="_blank">https://ecomfe.github.io/echarts/index-en.html</a></td>
                       <td>BSD-2-Clause</td>
                   </tr>
                   <tr>
                       <td><strong>Chart.js</strong></td>
                       <td><a href="http://www.chartjs.org" target="_blank">http://www.chartjs.org</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Google Maps</strong></td>
                       <td><a href="https://github.com/hpneo/gmaps" target="_blank">https://github.com/hpneo/gmaps</a></td>
                       <td>MIT</td>
                   </tr>
                   <tr>
                       <td><strong>Match Height</strong></td>
                       <td><a href="http://brm.io/jquery-match-height/" target="_blank">http://brm.io/jquery-match-height/</a></td>
                       <td>MIT</td>
                   </tr>

                </tbody>
            </table>

            <h4>Icons</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>URL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Feather</strong></td>
                        <td><a href="https://github.com/colebemis/feather" target="_blank">https://github.com/colebemis/feather</a></td>
                    </tr>
                    <tr>
                        <td><strong>Font Awesome</strong></td>
                        <td><a href="http://fontawesome.io/icons/" target="_blank">http://fontawesome.io/icons/</a></td>
                    </tr>
                </tbody>
            </table>

            <h4>Images</h4>
            <p>Images we used from below sites. Get it's URL also.</p>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>URL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a href="https://unsplash.com/" target="_blank">https://unsplash.com/</a></td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
</div>
</div>



<?php
require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";
?>
