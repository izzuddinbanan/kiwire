</div>

<script src="assets/lib/jquery/jquery.min.js" type="text/javascript"></script>
<script src="assets/lib/jquery-flot/jquery.flot.js" type="text/javascript"></script>
<script src="assets/lib/jquery-flot/jquery.flot.pie.js" type="text/javascript"></script>
<script src="assets/lib/jquery-flot/jquery.flot.resize.js" type="text/javascript"></script>
<script src="assets/lib/jquery-flot/plugins/jquery.flot.orderBars.js" type="text/javascript"></script>
<script src="assets/lib/jquery-flot/plugins/curvedLines.js" type="text/javascript"></script>
<script src="assets/lib/jquery.sparkline/jquery.sparkline.min.js" type="text/javascript"></script>
<script src="assets/lib/countup/countUp.min.js" type="text/javascript"></script>
<script src="assets/lib/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="assets/lib/jquery-flot/jquery.flot.time.js" type="text/javascript"></script>
<script src="assets/lib/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js" type="text/javascript"></script>
<script src="assets/lib/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>

<script src="assets/lib/datatables/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="assets/lib/datatables/js/dataTables.responsive.min.js" type="text/javascript"></script>
<script src="assets/lib/datatables/js/dataTables.bootstrap.min.js" type="text/javascript"></script>
<script src="assets/lib/datatables/plugins/buttons/js/dataTables.buttons.js" type="text/javascript"></script>
<script src="assets/lib/datatables/plugins/buttons/js/buttons.html5.js" type="text/javascript"></script>
<script src="assets/lib/datatables/plugins/buttons/js/buttons.flash.js" type="text/javascript"></script>
<script src="assets/lib/datatables/plugins/buttons/js/buttons.print.js" type="text/javascript"></script>
<script src="assets/lib/datatables/plugins/buttons/js/buttons.colVis.js" type="text/javascript"></script>
<script src="assets/lib/datatables/plugins/buttons/js/buttons.bootstrap.js" type="text/javascript"></script>
<script src="assets/lib/toastr/toastr.min.js"></script>
<script src="../assets/js/parsley.js"></script>


<!-- BEGIN ADDED js -->


<!-- BEGIN: Vendor JS-->
<script src="../../../../app-assets/vendors/js/vendors.min.js"></script>
<!-- BEGIN Vendor JS-->

<!-- BEGIN: Page Vendor JS-->
<script src="../../../../app-assets/vendors/js/charts/apexcharts.min.js"></script>
<!-- <script src="../../../../app-assets/vendors/js/extensions/tether.min.js"></script>
<script src="../../../../app-assets/vendors/js/extensions/shepherd.min.js"></script> -->
<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
<script src="../../../../app-assets/js/core/app-menu.js"></script>
<script src="../../../../app-assets/js/core/app.js"></script>
<script src="../../../../app-assets/js/scripts/components.js"></script>
<!-- END: Theme JS-->

<!-- BEGIN: Page JS-->
<!-- <script src="../../../../app-assets/js/scripts/pages/dashboard-ecommerce.js"></script> -->
<!-- <script src="../../../../app-assets/js/scripts/pages/dashboard-analytics.js"></script> -->
<!-- END: Page JS-->

<!-- <script src="../../../javascript/dashboard.js"></script> -->

<!-- END HERE -->




<!-- <script src="assets/lib/daterangepicker/js/daterangepicker.js"></script>
<script src="assets/lib/datetimepicker/js/bootstrap-datetimepicker.js"></script>
<script src="assets/lib/datetimepicker/js/bootstrap-datetimepicker.min.js"></script> -->

<script src="assets/lib/moment.js/min/moment.min.js"></script>


<!-- <script src="../app-assets/js/moment.min.js"></script> -->
<!-- <script src="../app-assets/js/daterangepicker.js"></script> -->

<script src="../app-assets/vendors/js/pickers/pickadate/picker.js"></script>
<script src="../app-assets/vendors/js/pickers/pickadate/picker.date.js"></script>
<script src="../app-assets/vendors/js/pickers/pickadate/picker.time.js"></script>
<script src="../app-assets/vendors/js/pickers/pickadate/legacy.js"></script>

<script src="../app-assets/js/scripts/pickers/dateTime/pick-a-datetime.js"></script>




<script src="assets/js/main.js" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function() {
        App.init();
    });
</script>

<?php if (file_exists("javascript/" . basename($_SERVER['SCRIPT_FILENAME'], ".php") . ".js")) { ?>
    <script src="<?= "javascript/" . basename($_SERVER['SCRIPT_FILENAME'], ".php") . ".js" ?>" type="text/javascript"></script>
<?php } ?>

</body>

</html>