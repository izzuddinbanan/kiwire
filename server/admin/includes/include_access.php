
<?php if (empty($kiw['module']) || !in_array($kiw['module'], $_SESSION['access_list'])) { ?>

    <div class="content-wrapper">

        <div class="content-body">

            <section id="css-classes" class="card">
                <div class="card-header">
                    <h4 class="card-title">Permission denied!</h4>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="card-text">
                            <p>You are not allow to access this module.</p>
                            <p>Please contact your administrator for more details.</p>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>

<?php

require_once "includes/include_footer.php";

exit();

}



?>
