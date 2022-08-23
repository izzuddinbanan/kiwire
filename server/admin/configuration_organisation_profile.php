<?php

$kiw['module'] = "Configuration -> Site Branding";
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

$kiw_row = $kiw_db->query_first("SELECT * FROM kiwire_clouds WHERE tenant_id = '{$tenant_id}' LIMIT 1");

if (empty($kiw_row)) $kiw_db->query("INSERT INTO kiwire_clouds(tenant_id) VALUE('{$tenant_id}')");


?>

    
<style>
    .select2-container {
        width: 92% !important;
    }
</style>

    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Organisation Profile</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active" data-i18n="subtitle">
                                    Configure organisation profile
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
                                <div class="card-header pull-right">
                                   
                                </div>
                                <div class="card-body">

                                    <form id="update-form" class="form-horizontal" method="post" enctype="multipart/form-data">

                                        <br><br><br>
                                        
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                        <div class="col-12">
                                            <div class="form-group row">

                                                <div class="col-md-2">
                                                    <span data-i18n="form_upload_logo">Upload Logo</span>
                                                </div>

                                                <div class="col-md-10">
                                                    <div class="custom-file">
                                                        <input type="file" name="logo" class="custom-file-input" id="logo" onchange="showImage(this);" data-maxfilesize="1000000">
                                                        <label class="custom-file-label" for="logo" data-i18n="form_choose_file">Choose file</label>
                                                        <span style="font-size: smaller; padding: 10px;" class="flang-c-field_3_note" data-i18n="form_logo_max">Logo maximum size is 1MB</span>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="form_current_logo">Current Logo</span>
                                                </div>

                                                <div class="col-md-10" id="current_logo_label">

                                                    <?php

                                                    $image_avail = false;

                                                    foreach (array("png", "jpeg", "jpg", "gif") as $ext) {

                                                        if (file_exists( dirname(__FILE__, 2) . "/custom/{$_SESSION['tenant_id']}/logo-{$_SESSION['tenant_id']}.{$ext}") == true) {

                                                            $image_avail = true;

                                                            echo "<img class='p-50 border' id='profile_logo' src='/custom/{$tenant_id}/logo-{$_SESSION['tenant_id']}.{$ext}' style='max-height: 200px;' />";

                                                            break;

                                                        }

                                                    }

                                                    if ($image_avail == false) {
                                                        echo "<img class='p-50 border' id='profile_logo' src='' style='max-height: 200px;' />";
                                                        echo "<span class='badge badge-warning badge-md badge-not-upload' style='font-size: 11px;'>No logo has been uploaded</span>";

                                                    }


                                                    ?>

                                                </div>

                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="form_customer">Customer</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="text" name="name" id="name" value="<? echo $kiw_row['name']; ?>" class="form-control col-11"/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">

                                            <div class="form-group row">

                                                <div class="col-md-2">
                                                    <span data-i18n="form_industry_type">Industry Type</span>
                                                </div>

                                                <div class="col-md-10">

                                                    <?php
                                                    $array = array(
                                                        '1' => 'Education',
                                                        '2' => 'Hospitality',
                                                        '3' => 'Healthcare',
                                                        '4' => 'Retail',
                                                        '5' => 'Marketing & Advertising',
                                                        '6' => 'Entertainment',
                                                        '7' => 'Real Estate',
                                                        '8' => 'Financial & Insurance Services',
                                                        '9' => 'Media & Telecommunications',
                                                        '10' => 'Government & Non-profit',
                                                        '11' => 'Manufacturing',
                                                        '12' => 'Professional & Business Support Services',
                                                        '13' => 'Transportation: Airport',
                                                        '14' => 'Transportation: Train',
                                                        '15' => 'Transportation: Bus',
                                                        '16' => 'Transportation: Seaport',
                                                        '17' => 'Municipal',
                                                        '18' => 'Agriculture',
                                                        '19' => 'Tourism',
                                                        '20' => 'Residential',
                                                        '25' => 'Other',
                                                        '26' => 'Assembly',
                                                        '27' => 'Business',
                                                        '28' => 'Factory And Industrial',
                                                        '29' => 'Institutional',
                                                        '30' => 'Mercantile',
                                                        '31' => 'Storage',
                                                        '32' => 'Utility And Miscellaneous',
                                                        '33' => 'Vehicular',
                                                        '34' => 'Outdoor',
                                                        '35' => 'Unspecified Business',
                                                        '36' => 'Doctor Or Dentist Office',
                                                        '37' => 'Bank',
                                                        '38' => 'Fire Station',
                                                        '39' => 'Police Station',
                                                        '40' => 'Post Office',
                                                        '41' => 'Professional Office',
                                                        '42' => 'Research And Development Facility',
                                                        '43' => 'Attorney Office',
                                                        '44' => 'Grocery Market',
                                                        '45' => 'Automotive Service Station',
                                                        '46' => 'Shopping Mall',
                                                        '47' => 'Gas Station'
                                                    );
                                                    ?>

                                                    <select name="industry" id="industry" class="select2 form-control"  data-style="btn-default" tabindex="-98">

                                                        <?php

                                                        foreach ($array as $key => $value) {

                                                            echo '<option value="' . $key . '" ' . ($kiw_row['industry'] == $key ? "selected" : "") . '>' . $value . '</option>';
                                                        }

                                                        ?>

                                                    </select>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="form_website">Website</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="text" name='website' id='website' value="<? echo $kiw_row['website']; ?>" class="form-control col-11"/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="form_phone_no">Phone No</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="text" name='phone' id='phone' value="<? echo $kiw_row['phone']; ?>" class="form-control col-11"/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-2">
                                                    <span data-i18n="form_address">Address</span>
                                                </div>
                                                <div class="col-md-10">
                                                    <textarea rows="3" cols="5" name='address' id='address' value="" class="form-control col-11"><? echo $kiw_row['address']; ?></textarea>
                                                </div>
                                            </div>
                                        </div>

                                    </form>

                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary save-button waves-effect waves-light" data-i18n="btn_save">Save </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

<?php require_once "includes/include_footer.php"; ?>
