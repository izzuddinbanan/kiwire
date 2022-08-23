<?php

global $kiw_config;

$kiw_error = $_SESSION['response']['error'];
$_SESSION['response']['error'] = "";

$kiw_success = $_SESSION['response']['success'];
$_SESSION['response']['success'] = "";


?>
</div>

<script>

    var error_msg   = "<?= $kiw_error ?>";
    var success_msg = "<?= $kiw_success ?>";
    var session_id  = "<?= $_REQUEST['session'] ?>";
    var user_mac_address = "<?= $_SESSION['user']['mac'] ?>";

</script>


<?php if ($kiw_config['ask_web_push'] == "y"){ ?>
<script type="application/javascript" src="/user/wifi-app.js"></script>
<?php } ?>


<?php if ($kiw_temp['purpose'] == "campaign"){ ?>

<script type="application/javascript" src="/libs/mightyslider/assets/js/jquery.js"></script>
<script type="application/javascript" src="/libs/mightyslider/assets/js/jquery.migrate.min.js"></script>
<script type="application/javascript" src="/libs/mightyslider/assets/js/jquery.mobile.just-touch.js"></script>
<script type="application/javascript" src="/libs/mightyslider/assets/js/jquery.requestAnimationFrame.js"></script>
<script type="application/javascript" src="/libs/mightyslider/assets/js/bootstrap.min.js"></script>
<script type="application/javascript" src="/libs/mightyslider/assets/js/retina.js"></script>
<script type="application/javascript" src="/libs/mightyslider/assets/js/jquery.easing-1.3.pack.js"></script>
<script type="application/javascript" src="/libs/mightyslider/assets/js/jquery.mousewheel.js"></script>
<script type="application/javascript" src="/libs/mightyslider/assets/js/jquery.simplr.smoothscroll.js"></script>
<script type="application/javascript" src="/libs/mightyslider/src/js/tweenlite.js"></script>
<script type="application/javascript" src="/libs/mightyslider/src/js/mightyslider.min.js"></script>
<script type="application/javascript" src="/user/campaign.js"></script>

<?php } elseif ($kiw_temp['purpose'] == "qr"){ ?>

<script type="application/javascript" src="/user/qrcode.js"></script>

<?php } elseif ($kiw_temp['purpose'] == "survey"){ ?>

<script type="application/javascript" src="/assets/js/js.cookie.min.js"></script>
<script type="application/javascript" src="/user/survey.js"></script>

<?php } ?>

<script type="application/javascript" src="/assets/js/parsley.js"></script>
<script type="application/javascript" src="/assets/js/datejs/build/date.js"></script>
<script type="application/javascript" src="/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script type="application/javascript" src="/admin/designer/assets/scripts/slick/slick.min.js"></script>
<script type="application/javascript" src='/user/app.js'></script>
<script type="application/javascript" src="/app-assets/vendors/js/pickers/pickadate/picker.js"></script>
<script type="application/javascript" src="/app-assets/vendors/js/pickers/pickadate/picker.date.js"></script>
<script type="application/javascript" src="/app-assets/vendors/js/pickers/pickadate/legacy.js"></script>
<script type="application/javascript" src="/app-assets/js/scripts/pickers/dateTime/pick-a-datetime.js"></script>


</body>
</html>