<?php

$kiw['module'] = "Policy -> Firewall";
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

?>


<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="policy_firewall_title">Firewall</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="policy_firewall_subtitle">
                                Firewall configuration
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

                        <button type="button" class="btn btn-primary waves-effect waves-light create-btn-firewall pull-right" data-toggle="modal" data-target="#inlineForm" data-i18n="policy_firewall_add">Add Firewall Policy</button>

                        <div class="table-responsive">
                            <table id="itemlist" class="table table-hover table-data">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th data-i18n="policy_firewall_tno">No</th>
                                        <th data-i18n="policy_firewall_tzone">Zone</th>
                                        <th data-i18n="policy_firewall_thost">Host</th>
                                        <th data-i18n="policy_firewall_trule">Rule</th>
                                        <th data-i18n="policy_firewall_tremark">Remark</th>
                                        <th data-i18n="policy_firewall_taction">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  <th data-i18n="policy_firewall_loading">
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
                <h4 class="modal-title" id="myModalLabel33" data-i18n="policy_firewall_add_edit">Add or Edit Firewall Policy</h4>
                <button type="button" class="close cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="create-form" action="#">
                <div class="modal-body">

                    <label data-i18n="policy_firewall_nas">NAS: </label>
                    <div class="form-group">

                        <select name=nasid id=nasid class="select2 form-control" data-style="btn-default" tabindex="-98">
                            <option value="All" data-i18n="policy_firewall_nas_all">All</option>

                            <?
                            $sql = "select unique_id from kiwire_controller where tenant_id = '{$tenant_id}'";
                            $rows = $kiw_db->fetch_array($sql);
                            foreach ($rows as $record) {
                                ?>
                                <option value="<?= $record['unique_id']; ?>"><?= $record['unique_id']; ?></option>
                            <? } ?>

                        </select>

                    </div>

                    <label data-i18n="policy_firewall_host">Host/Mac: </label> <span class="text-danger">*</span>
                    <div class="form-group">
                        <input type="text" name="dest" id="dest" class="form-control" placeholder="Host/Mac Address" required>
                    </div>

                    <label data-i18n="policy_firewall_policy">Policy: </label>
                    <div class="form-group">

                        <select name=type id=type class="select2 form-control" data-style="btn-default" tabindex="-98">
                            <option value="fwip" data-i18n="policy_firewall_block_ip">Block this IP Address</option>
                            <option value="fwmac" data-i18n="policy_firewall_block_mac">Block this MAC Address</option>
                        </select>

                    </div>

                    <label data-i18n="policy_firewall_remark">Remark: </label>
                    <div class="form-group">
                        <input type="text" name="remark" id="remark" placeholder="description" class="form-control">
                    </div>

                </div>


                <div class="modal-footer">

                    <input type="hidden" id="reference" name="reference" value="">
                    <button type="button" class="btn btn-danger round waves-effect waves-light cancel-button" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-create">Create</button>
                    <button type="button" class="btn btn-primary round waves-effect waves-light btn-update">Update</button>

                </div>
                <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
            </form>
        </div>
    </div>
</div>

<?php

require_once "includes/include_footer.php";
require_once "includes/include_datatable.php";

?>
