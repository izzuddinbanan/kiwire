<?php

$kiw['module'] = "Login Engine -> Term and Condition";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_tnc WHERE tenant_id = '{$tenant_id}' ");

?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="login_engine_term_condition_title">Terms & Condition</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="login_engine_term_condition_subtitle">
                                Manage Terms and Condition
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section id="basic-tabs-components">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card overflow-hidden">
                        <div class="card-content">
                            <div class="card-body">

                                <form class="update-form">
                                    <button type="button" class="btn btn-primary pull-right save-button round waves-effect waves-light" data-i18n="login_engine_term_condition_save">Save</button>


                                    <ul class="nav nav-tabs" role="tablist">

                                        <li class="nav-item">
                                            <a class="nav-link active" id="terms_cond-tab" data-toggle="tab" href="#terms_cond" aria-controls="terms_cond" role="tab" aria-selected="true" data-i18n="login_engine_term_condition_tc">Terms & Condition</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="advance-tab" data-toggle="tab" href="#advance" aria-controls="advance" role="tab" aria-selected="false" data-i18n="login_engine_term_condition_advance">Advance</a>
                                        </li>

                                    </ul>

                                    <br><br>

                                    <div class="tab-content">

                                        <div class="tab-pane active" id="terms_cond" aria-labelledby="terms_cond-tab" role="tabpanel">

                                            <div class="form-group">
                                                <div class="col-md-12">
                                                    <textarea id="tnc" rows="5" name="tnc" class="form-control">
                                                <? echo $kiw_row['tnc']; ?>
                                            </textarea>
                                                </div>
                                            </div>

                                        </div>


                                        <div class="tab-pane" id="advance" aria-labelledby="advance-tab" role="tabpanel">

                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <div class="text-right col-md-3">
                                                        <span data-i18n="login_engine_term_condition_always_on">T&C always On</span>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <div class="custom-control custom-switch custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input" name=tnc_alwayson  id=tnc_alwayson  <?= ($kiw_row['tnc_alwayson'] == "y") ? 'checked="yes"' : '' ?> value="y" class="toggle" />
                                                            <label class="custom-control-label" for="tnc_alwayson"></label>
                                                        </div>
                                                    </div>
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
        </section>
    </div>
</div>

<?php
require_once "includes/include_footer.php";
?>