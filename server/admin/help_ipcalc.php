<?php

$kiw['module'] = "Help -> IP Network Calculator";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_nav.php';
require_once 'includes/include_access.php'; 

?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="help_ipcalc_title">IP Network Calculator</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="help_ipcalc_subtitle">
                                Network IP calculator
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
                        <form action="" method="post" class="form form-horizontal">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="text-right col-md-3">
                                                <span data-i18n="help_ipcalc_ipsubnet">IP & Subnet</span>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" name="input" id="input" value="<? echo $_POST['input']; ?>" class="form-control col-11"/>
                                                <span style="font-size: smaller;" class="flang-c-field_3_note">Eg : 192.168.0.1 255.255.255.0 or 192.168.0.1/24</span>
                                            </div>

                                            <button type="submit" class="btn btn-primary mr-1 mb-2 waves-effect waves-light" data-i18n="help_ipcalc_calcip">Calculate IP</button>
                                        
                                        </div>
                                    </div>    
                                
                                
                                
                                </div>
                            </div>
                        </form>         
                    </div>
                    
                    <div>
                        <?php
                            $input = $_POST['input'];
                            
                            if(substr_count($input, '.') == 3) $input = (strpos($input, "/") > 0 ? $input : $input . "/32");
                                if ($input) {
                                    $x = `ipcalc  -b -n -m -p $input`;
                                    echo "<pre>" . $x . "</pre>";
                                }
                        ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require_once "includes/include_footer.php"; ?>