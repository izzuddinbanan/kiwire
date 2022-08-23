<?php

$kiw['module'] = "General -> Password";
$kiw['page'] = "Dashboard";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['custom'] = false;

require_once 'includes/include_config.php';
require_once 'includes/include_session.php';
require_once 'includes/include_general.php';
require_once 'includes/include_header.php';
require_once 'includes/include_nav.php';
require_once 'includes/include_access.php';
require_once "includes/include_connection.php";
require_once "includes/include_report.php";

$kiw_db = Database::obtain();

?>

<div class="content-wrapper">

    <div class="content-header row">
        <div class="content-header-left col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0 text-uppercase" data-i18n="title">Change Password</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" data-i18n="subtitle">
                                Change administrator password
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

                        <div class="row mb-25">
                            <div class="col-12">

                                <h3 data-i18n="h3_title">End User License Agreement</h3>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 round">
                                <div>
                                    <pre style="max-height: 300px; padding: 20px;" data-i18n="EULA">
This Software License is made by Synchroweb Technology(M) Sdn Bhd, to the Customer as an essential element of the services to be rendered by Synchroweb as defined in the system specification and any associated documents and agreement. System shall mean the deliverable product as defined in these documents.

Customer and Synchroweb agree that this Software License is deemed to be part of, and subject to, the terms of the Agreement applicable to both parties.

SECTION 1 LICENSE GRANT AND OWNERSHIP

1.1 Synchroweb hereby grants to Customer a worldwide, perpetual, non-exclusive, non-transferable license to all software for Customer’s use in connection with the establishment, use, maintenance and modification of the system implemented by Synchroweb. Software shall mean executable object code of software programs and the patches, scripts, modifications, enhancements, designs, concepts or other materials that constitute the software programs necessary for the proper function and operation of the system as delivered by the (AV COMPANY NAME) and accepted by the Customer.
1.2 Except as expressly set forth in this paragraph, Synchroweb shall at all times own all intellectual property rights in the software. Any and all licenses, product warranties or service contracts provided by third parties in connection with any software, hardware or other software or services provided in the system shall be delivered to Customer for the sole benefit of Customer.
1.3 Customer may supply to Synchroweb or allow the Synchroweb use certain proprietary information, including service marks, logos, graphics, software, documents and business information and plans that have been authored or pre-owned by Customer. All such intellectual property shall remain the exclusive property of Customer and shall not be used by for any purposes other than those associated with delivery of the system.

SECTION 2 COPIES, MODIFICATION, AND USE

2.1 Customer may make copies of the software for archival purposes and as required for modifications to the system. All copies and distribution of the software shall remain within the direct control of Customer and its representatives.
2.2 Customer may make modifications to the source code version of the software, if and only if the results of all such modifications are applied solely to the system. In no way does this Software License confer any right in Customer to license, sublicense, sell, or otherwise authorize the use of the software, whether in executable form, source code or otherwise, by any third parties, except in connection with the use of the system as part of Customer’s business.
2.3 All express or implied warranties relating to the software shall be deemed null and void in case of any modification to the software made by any party other than Synchroweb.

SECTION 3 WARRANTIES AND REPRESENTATIONS

Synchroweb represents and warrants to Customer that:

3.1 it has all necessary rights and authority to execute and deliver this Software License and perform its obligations hereunder and to grant the rights granted under this Software License to Customer;
3.2 the goods and services provided by contractor under this Software License, including the software and all intellectual property provided hereunder, are original to Synchroweb or its subcontractors or partners; and
3.3 the software, as delivered as part of the system, will not infringe or otherwise violate the rights of any third party, or violate any applicable law, rule or regulation.
3.4 Synchroweb further represents and warrants that, throughout the System Warranty Period, the executable object code of software and the system will perform substantially in accordance with the System Specifications and Agreement. If the software fails to perform as specified and accepted all remedies are pursuant to the policies set forth in the Specification and in the Agreement. No warranty of any type or nature is provided for the source code version of the software which is delivered as is.
3.5 Except as expressly stated in this Agreement, there are no warranties, express or implied, including, but not limited to, the implied warranties of fitness for a particular purpose, of merchantability, or warranty of no infringement of third party intellectual property rights.

SECTION 4 TRANSFER AND TERMINATION

This license will automatically terminate upon the disassembly of the system cited above, unless the system is reassembled in its original configuration in another location.

Synchroweb may terminate this license upon notice for failure to comply with any of terms set forth in this Software License. Upon termination, Customer is obligated to immediately destroy the software, including all copies and modifications.

By clicking save you agree to the above license agreement
                                    </pre>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-12">

                                <form id="password-change" action="#" method="post" class="form form-horizontal">
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                                    <div class="col-6 offset-2 d-inline-flex">
                                        <input type="password" class="form-control" name="password">
                                    </div>
                                    <div class="col-2 d-inline-flex">
                                        <input type="button" class="btn btn-primary btn-save-password" value="Save">
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