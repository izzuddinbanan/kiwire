</div>

<?php $kiw_temp = @file_get_contents("/var/www/kiwire/version.json");

$kiw_version = json_decode($kiw_temp, true); ?>

<footer class="footer footer-static footer-light">
    <p class="clearfix blue-grey lighten-2 mb-0"><span class="float-md-left d-block d-md-inline-block mt-25"><?=sync_brand_decrypt(SYNC_COPYRIGHT)?> - <small>Version <?php echo $kiw_version['version_number']?><small></span>
        <button class="btn btn-primary btn-icon scroll-top" type="button"><i class="feather icon-arrow-up"></i></button>
    </p>
</footer>



<!-- modal for change theme -->

<div class="modal fade text-left" id="theme_modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="mythememodal">Change Theme</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <select name="change-theme-btn" id="change-theme-btn" class="select2 form-control change-theme-btn">
                    <option value="default" <?= ($_SESSION['theme'] == "default" ? "selected" : "")?>>Light (Default)</option>
                    <option value="dark" <?= ($_SESSION['theme'] == "dark" ? "selected" : "")?>>Dark</option>
                </select>

            </div>

        </div>
    </div>
</div>


<!-- modal for read message -->

<div class="modal fade text-left" id="message_modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel17">Message</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body notification-content">
                [ Notification Space ]
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<script>

var metric = '<?= (empty($_SESSION['metrics']) || $_SESSION['metrics'] == "Gb" ? pow(1024, 3) : pow(1024, 2)) ?>';

</script>

<div class="sidenav-overlay"></div>
<div class="drag-target"></div>

<script src="/app-assets/vendors/js/vendors.min.js"></script>
<script src="/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="/app-assets/vendors/js/extensions/toastr.min.js"></script>
<script src="/app-assets/vendors/js/forms/validation/jquery.validate.min.js"></script>

<script src="/app-assets/vendors/js/forms/select/select2.min.js"></script>

<script src="/assets/js/parsley.js"></script>
<script src="/app-assets/js/core/app-menu.js"></script>
<script src="/app-assets/js/core/app.js"></script>
<script src="/app-assets/js/scripts/components.js"></script>

<script src="/app-assets/vendors/js/pickers/pickadate/picker.js"></script>
<script src="/app-assets/vendors/js/pickers/pickadate/picker.date.js"></script>
<script src="/app-assets/vendors/js/pickers/pickadate/picker.time.js"></script>
<script src="/app-assets/vendors/js/pickers/pickadate/legacy.js"></script>

<script src="/app-assets/js/scripts/pickers/dateTime/pick-a-datetime.js"></script>
<script src="/app-assets/vendors/js/forms/spinner/jquery.bootstrap-touchspin.js"></script>

<script src="/app-assets/dropify/dist/js/dropify.min.js"></script>

<script src="/libs/tinymce/tinymce.min.js"></script>


<script src="/app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script src="/admin/javascripts/include_footer.js"></script>

<?php if (file_exists(dirname(__FILE__, 2) . "/javascripts/{$kiw['name']}.js")){ ?>
<script src="/admin/javascripts/<?= basename($_SERVER['SCRIPT_NAME'], ".php") ?>.js"></script>
<?php } ?>


<script>

$(document).ready(function () {

    $(".nav-clear-cache").on("click", function () {

        $.ajax({
            url: "/admin/ajax/ajax_help_system_quick_fix.php",
            method: "post",
            data: {
                action: 'clear_cache'
            },
            success: function (response) {

                if (response['status'] === "success"){

                    swal("Success", response['message'], "success");

                } else {

                    swal("Error", response['status'], "error");

                }


            },
            error: function (response) {

                swal("Error", "There is an error. Please retry.", "error");

            }
        });

    });

});



</script>
</body>

</html>
