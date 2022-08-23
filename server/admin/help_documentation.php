<?php

$kiw['module'] = "Help -> Online Knowledgebase";
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
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Online Knowledge Base</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Access our online knowledge base and support
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
                        <p>
                            <span data-i18n="span_access">
                                To access our Online Knowledge Base, please click on the button bellow. Alternatively
                                you can access it via the web at <a href="https://docv3.synchroweb.com/" target="_new" data-i18n="link">docv3.synchroweb.com</a>
                            </span> 
                            
                            <br><br>
                                
                            <button class="btn btn-primary" onclick="document.location.href='https://docv3.synchroweb.com/'; return false;" target="_new" data-i18n="btn_access_db">
                                Access Online Knowledge Base
                            </button>
                        </p>              
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require_once "includes/include_footer.php"; ?>
