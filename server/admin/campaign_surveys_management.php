<?php

$kiw['module'] = "Campaign -> Survey Management";
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

<style>
    @media only screen and (max-width: 480px){
        .th-question{
            min-width: 400px;
        }

        .th-type{
            min-width: 200px;
        }

        .th-choice{
            min-width: 400px;
        }

        .th-action{
            min-width: 100px;
        }
    }
</style>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Survey</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Survey engine
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-survey pull-right mb-25" data-toggle="modal" data-target="#inlineForm" data-i18n="button_add_surver">Add Survey</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="thead_no">No</th>
                                        <th data-i18n="thead_name">Name</th>
                                        <th data-i18n="thead_desc">Description</th>
                                        <th data-i18n="thead_status">Status</th>
                                        <th data-i18n="thead_action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  <th data-i18n="tbody_loading">
                                    Loading...
                                  </th>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>

</div>


<!-- Modal -->
<div class="modal fade text-left" id="inlineForm" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33" data-i18n="modal_1_title">Add or Edit Survey</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">
                <div class="modal-body">

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <label data-i18n="modal_1_label_enable">Enable: </label>
                            <input type="checkbox" class="custom-control-input" name="status" id="status" value="y" class="toggle" />
                            <label class="custom-control-label" for="status"></label>
                        </div>
                    </div>

                    <label data-i18n="modal_1_label_title">Title: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="name" id="name" value="" class="form-control" placeholder="eg: Merdeka Survey" required>
                    </div>

                    <label data-i18n="modal_1_label_desc">Description: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="description" id="description" placeholder="remark" value="" class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal" data-i18n="modal_1_footer_button_cancel">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create" data-i18n="modal_1_footer_button_create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update" data-i18n="modal_1_footer_button_update">Update</button>

                </div>
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
            </form>
        </div>
    </div>
</div>



<!-- Modal -->
<div class="modal fade text-left" id="QuestionTable" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33">Create Question</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="table-question" action="#">
                <div class="modal-body" style="overflow: auto;">

                    <div class="table-responsive">
                        <table id="questlist" class="table table-hover table-data-1">
                            <thead>
                                <tr class="text-uppercase">
                                    <th>No</th>
                                    <th>Question</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>


<div class="modal fade text-left" id="question-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <form class="modal-content" id="question-form" style="height: 600px;">

            <div class="modal-header">
                <h4 class="modal-title" id="question-modal-title" data-i18n="modal_2_title">Survey Questions</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="overflow: auto;">

                <table class="table table-bordered question-list">

                    <thead class="thead-dark">
                    <tr>
                        <th data-i18n="modal_2_thead_question">Question</th>
                        <th data-i18n="modal_2_thead_type">Type</th>
                        <th data-i18n="modal_2_thead_required">Required</th>
                        <th data-i18n="modal_2_thead_choice">Choice</th>
                        <th data-i18n="modal_2_thead_action">Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>

                </table>

            </div>

            <div class="modal-footer">

                <div class="col-12">

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <span style="font-weight: bolder; color: rgba(0, 0, 0, 0.5);">              
                                Please take note that changes only be saved once you click on the [ Save ] button.
                            </span>
                        </div>
                        
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <!-- <button type="button" class="pull-right btn btn-danger waves-effect waves-light btn-question-close" data-dismiss="modal" data-i18n="modal_2_footer_button_close">Close</button> -->
                            <button type="button" class="pull-right btn btn-primary waves-effect waves-light btn-question-save mr-1" data-i18n="modal_2_footer_button_save">Save</button>
                            <button type="button" class="pull-right btn btn-info waves-effect waves-light btn-question-add mr-1" data-i18n="modal_2_footer_button_add_q">Add Question</button>
                        </div>
                    </div>
                    
                </div>

            </div>

        </form>
    </div>
</div>


<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>
