<?php


require_once dirname(__FILE__, 4) . "/includes/include_config.php";
require_once dirname(__FILE__, 4) . "/includes/include_session.php";
require_once dirname(__FILE__, 4) . "/includes/include_connection.php";


header("Content-Type: application/javascript");


// get extra fields

$kiw_fields_signup_public = $kiw_db->query_first("SELECT data FROM kiwire_signup_public WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1")['data'];
$kiw_fields_signup_sponsor = $kiw_db->query_first("SELECT data FROM kiwire_signup_visitor WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1")['data'];
$kiw_fields_signup_oneclick = $kiw_db->query_first("SELECT data FROM kiwire_one_click_login WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1")['data'];
$kiw_fields_email_verification = $kiw_db->query_first("SELECT data FROM kiwire_int_email WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1")['data'];
$kiw_fields_sms_verification = $kiw_db->query_first("SELECT data FROM kiwire_int_sms WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1")['data'];


// get the survey data

$kiw_surveys = $kiw_db->fetch_array("SELECT SQL_CACHE * FROM kiwire_survey_list WHERE tenant_id = '{$_SESSION['tenant_id']}'");


// get the social data

$kiw_social = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_social WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


$kiw_field_data = @file_get_contents(dirname(__FILE__, 5) . "/custom/{$_SESSION['tenant_id']}/data-mapping.json");

$kiw_field_data = json_decode($kiw_field_data, true);


if (empty($kiw_field_data)){


    $kiw_field_data = @file_get_contents(dirname(__FILE__, 5) . "/user/templates/kiwire-data-mapping.json");

    if (!empty($kiw_field_data)) {

        $kiw_field_data = json_decode($kiw_field_data, true);

        @file_put_contents(dirname(__FILE__, 5) . "/custom/{$_SESSION['tenant_id']}/data-mapping.json", json_encode($kiw_field_data));

    }


}



$kiw_field_data_field = [];

$kiw_field_data_required = [];



if ($kiw_field_data){

	foreach($kiw_field_data as $kiw_data){

	    if ($kiw_data['variable'] != "[empty]") {

            $kiw_field_data_field[trim($kiw_data['variable'])] = $kiw_data['display'];
            $kiw_field_data_required[$kiw_data['variable']] = $kiw_data['required'];

        }

	}

}

unset($kiw_data);
unset($kiw_field_data);


?>

/* v1 */
function _tabs(n) {
	var html = '';
	for (var i = 1; i <= n; i++) {
		html += '\t';
	}
	return '\n' + html;
}

// source: https: //stackoverflow.com/questions/2255689/how-to-get-the-file-path-of-the-currently-executing-javascript-code
function _path() {
	var scripts = document.querySelectorAll('script[src]');
	var currentScript = scripts[scripts.length - 1].src;
	var currentScriptChunks = currentScript.split('/');
	var currentScriptFile = currentScriptChunks[currentScriptChunks.length - 1];
	return currentScript.replace(currentScriptFile, '');
}
var _snippets_path = _path();

var data_basic = {
	'snippets': [



		{
			'thumbnail': 'preview/bpanel.png',
			'category': '100',
			'html':
                '<div class="row"> <div class="col-md-12"> <div class="row"> <div class="col-md-12 col-sm-12"><center><h4 class="mb-0"><b>Choose Your Profile</b></h4><p><b><br></b></p><p><b><br></b></p></center></div></div><div class="row">{{profile_list}}</div></div></div>'
		},

        {
			'thumbnail': 'preview/next_page.png',
			'category': '100',
			'html':
				'<div class="row">' +
				'<div class="col-md-6 offset-md-3 col-sm-12">' +
				'<div class="card">' +
				'<div class="card-content">' +
				'<div class="card-body">' +
				'<div class="row text-center">' +
				'<a href="javascript:void(0);" class="btn btn-primary text-center next-page-btn mx-auto" data-next-page="">Next</a>' +
				'</div>' +
				'</div>' +
				'</div>' +
				'</div>' +
				'</div>' +
				'</div>'
		},

        {
            'thumbnail': 'preview/date.png',
            'category': '100',
            'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="text-center col-12 mx-auto">' +
                'Date: <div class="current-date" data-format="d-MMM-yyyy"></div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },


		{
			'thumbnail': 'preview/using_registered_acc.png',
			'category': '100',
			'html':
				'<div class="row">' +
        		'<div class="col-md-6 offset-md-3 col-sm-12">' +
            	'<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Login using Registered Account</b></h4>' +
                '</div>' +
                '<form method="post" action="/user/login/?session={{session_id}}">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Username">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="password" class="form-control" id="password" placeholder="Password">' +
                '</div>' +
                '<button type="submit" class="btn btn-primary pull-right mb-2">Login</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},


		{
			'thumbnail': 'preview/using_voucher.png',
			'category': '100',
			'html':
				'<div class="row">' +
        		'<div class="col-md-6 offset-md-3 col-sm-12">' +
            	'<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Login using Voucher</b></h4>' +
                '</div>' +
                '<form method="post" action="/user/login/?session={{session_id}}">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Voucher Code">' +
                '</div>' +
                '<button type="submit" class="btn btn-primary pull-right mb-2">Login</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},


        {
			'thumbnail': 'preview/using_ext_radius.png',
			'category': '100',
			'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Login using External Radius</b></h4>' +
                '</div>' +
                '<form method="post" action="/user/integration/radius/?session={{session_id}}">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<input type="text" name="domain" class="form-control" id="domain" placeholder="Domain">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Username">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="password" class="form-control" id="password" placeholder="Password">' +
                '</div>' +
                '<button type="submit" class="btn btn-primary pull-right mb-2">Login</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
		},


        {
			'thumbnail': 'preview/using_microsoft_ad.png',
			'category': '100',
			'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Login using Microsoft Active Directory</b></h4>' +
                '</div>' +
                '<form method="post" action="/user/integration/msad/?session={{session_id}}">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Username">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="password" class="form-control" id="password" placeholder="Password">' +
                '</div>' +
                '<button type="submit" class="btn btn-primary pull-right mb-2">Login</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
		},


        {
			'thumbnail': 'preview/using_ldap.png',
			'category': '100',
			'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Login using LDAP</b></h4>' +
                '</div>' +
                '<form method="post" action="/user/integration/ldap/?session={{session_id}}">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Username">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="password" class="form-control" id="password" placeholder="Password">' +
                '</div>' +
                '<button type="submit" class="btn btn-primary pull-right mb-2">Login</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
		},


        {
			'thumbnail': 'preview/using_ext_db.png',
			'category': '100',
			'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Login using External Database</b></h4>' +
                '</div>' +
                '<form method="post" action="/user/integration/database/?session={{session_id}}">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Username">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="password" class="form-control" id="password" placeholder="Password">' +
                '</div>' +
                '<button type="submit" class="btn btn-primary pull-right mb-2">Login</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },


		{
			'thumbnail': 'preview/campaign_1.png',
			'category': '100',
			'html':
				'<div class="row mb-3">' +
        		'<div class="col-md-6 offset-md-3 col-sm-12">' +
            	'<div class="card">' +
                '<div class="row no-gutters">' +
                '<div class="col-4" style="background-color:#d3d3d3;">' +
                '<div id="campaign-1" style="height: 100%; width: 100%;"></div>' +
                '<div class="campaign-1-name"></div>' +
                '</div>' +
                '<div class="col-8">' +
                '<div class="card-body">' +
                '<form method="post" action="/user/login/?session={{session_id}}">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<label for="email">Username:</label>' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Please enter your Username">' +
                '</div>' +
                '<div class="form-group">' +
                '<label for="password">Password:</label>' +
                '<input type="password" name="password" class="form-control" id="password" placeholder="Please enter your Password">' +
                '</div>' +
                '<button type="submit" class="btn btn-primary">Submit</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},


		{
			'thumbnail': 'preview/one_click.png',
			'category': '100',
			'html':
				'<div class="row mb-3">' +
        		'<div class="col-md-6 offset-md-3 col-sm-12">' +
            	'<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<form action="/user/one/?session={{session_id}}" method="post">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="col-sm-12 center">' +
                '<p class="mb-2"><b>Welcome, please click continue to access wifi:</b></p>' +
                '</div>' +
                '<div class="custom-control custom-checkbox center">' +
                '<input type="checkbox" class="custom-control-input" id="tnc" name="tnc" required value="">' +
                '<label for="tnc" class="custom-control-label">Terms and Condition and Privacy Policy</label>' +
                '</div>' +
                '<div class="col-sm-12 center mt-75">' +
                '<button type="submit" class="btn btn-primary center">Continue</button>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},

        {
            'thumbnail': 'preview/continue.png',
            'category': '100',
            'html':
                '<div class="row mb-3">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<form name="register" action="/user/one/?session={{session_id}}" method="post">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="col-sm-12 center">' +
                '<p class="mb-2"><b>Please fill in the below information, and click on Continue to enjoy your internet.</b></p>' +
                '</div>' +
                <? foreach(array_filter(explode(",", $kiw_fields_signup_oneclick)) as $kiw_field){ ?>
                    '<div class="form-group">' +
                        '<input type="text" class="form-control" id="<?= $kiw_field ?>" name="<?= $kiw_field ?>" placeholder="<?= $kiw_field_data_field[$kiw_field] ?>" <?= ($kiw_field_data_required[$kiw_field] == "y" ? "required" : "") ?> value="">' +
                    '</div>' +
                <? } ?>
                '<div class="custom-control custom-checkbox">' +
                '<input type="checkbox" class="custom-control-input" id="tnc" name="tnc" required value="">' +
                '<label for="tnc" class="custom-control-label">Terms and Condition and Privacy Policy</label>' +
                '</div>' +
                '<div class="col-sm-12 center mt-75">' +
                '<button type="submit" class="btn btn-primary center">Continue</button>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },


		{
			'thumbnail': 'preview/social_login.png',
			'category': '100',
			'html':
				'<div class="row mb-3">' +
        		'<div class="col-md-6 offset-md-3 col-sm-12">' +
            	'<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="col-sm-12 card-title">' +
                '<h5 class="mb-2 center"><b>Social Login</b></h5>' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-lg-12">' +
                '<div class="social mx-auto d-block" align="center">' +
                '<a href="/user/social/?type=facebook&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_facebook.png"></a>' +
                '<a href="/user/social/?type=twitter&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_twitter.png"></a>' +
                '<a href="/user/social/?type=instagram&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_instagram.png"></a>' +
                '<a href="/user/social/?type=linkedin&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_linkedin.png"></a>' +
                '<a href="/user/social/?type=microsoft&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_windows365.png"></a>' +
                '<br>' +
                '<a href="/user/social/?type=zalo&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_zalo.png"></a>' +
                '<a href="/user/social/?type=vk&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_vk.png"></a>' +
                '<a href="/user/social/?type=line&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_line.png"></a>' +
                '<a href="/user/social/?type=kakao&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_kakao.png"></a>' +
                '<a href="/user/social/?type=wechat&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_wechat.png"></a>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},


		{
			'thumbnail': 'preview/registered_or_social_media.png',
			'category': '100',
			'html':
				'<div class="row mb-3">' +
        		'<div class="col-md-6 offset-md-3 col-sm-12">' +
            	'<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="col-sm-12 card-title">' +
                '<h5 class="mb-2 center"><b>Login using Registered Account</b></h5>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-12">' +
                '<form method="post" action="/user/login/?session={{session_id}}">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Username">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="password" class="form-control" id="password" placeholder="Password">' +
                '</div>' +
                '<button type="submit" class="btn btn-primary mb-2 col-12">Login</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '<div class="col-sm-12 card-title">' +
                '<h5 class="mb-2 center"><b>Or using Social Media Account</b></h5>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-12">' +
                '<div class="social mx-auto d-block" align="center">' +
                '<a href="/user/social/?type=facebook&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_facebook.png"></a>' +
                '<a href="/user/social/?type=twitter&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_twitter.png"></a>' +
                '<a href="/user/social/?type=instagram&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_instagram.png"></a>' +
                '<a href="/user/social/?type=linkedin&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_linkedin.png"></a>' +
                '<a href="/user/social/?type=microsoft&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_windows365.png"></a>' +
                '<br>' +
                '<a href="/user/social/?type=zalo&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_zalo.png"></a>' +
                '<a href="/user/social/?type=vk&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_vk.png"></a>' +
                '<a href="/user/social/?type=line&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_line.png"></a>' +
                '<a href="/user/social/?type=kakao&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_kakao.png"></a>' +
                '<a href="/user/social/?type=wechat&session={{session_id}}" class="mr-1"><img src="/admin/designer/uploads/social/socialbox_wechat.png"></a>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},


		{
			'thumbnail': 'preview/using_qrcode.png',
			'category': '100',
			'html':
				'<div class="row">' +
        		'<div class="col-md-6 offset-md-3 col-sm-12">' +
            	'<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="col-sm-12 card-title">' +
                '<h5 class="mb-2 center"><b>Login Using QR Code</b></h5>' +
                '</div>' +
                '<div class="col-sm-12 center">' +
                '<p class="mb-2">Please flash this QR Code to a network administrator to allow internet access:</p>' +
                '</div>' +
                '<div class="col-sm-4 offset-sm-4">' +
                '<img src="/admin/designer/uploads/dummy.jpg" class="card-img qrcode-img">' +
                '</div>' +
                '<div class="col-sm-4 offset-sm-4 center qr-pls-wait">' +
                '[ Please Wait ]' +
                '</div>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},

        {
        'thumbnail': 'preview/terms_conditions.png',
        'category': '100',
        'html':
            '<div class="row">' +
            '<div class="col-md-6 offset-md-3 col-sm-12">' +
            '<div class="card">' +
            '<div class="card-content">' +
            '<div class="card-body">' +
            '<div class="col-12 card-title">' +
            '<h5 class="mb-2 center"><b>Terms and Conditions</b></h5>' +
            '</div>' +
            '<div class="col-sm-12 text-center">' +
            '<p class="mb-2">TERMS AND CONDITIONS FOR USE OF WIRED/WIRELESS INTERNET SERVICE</p>' +
            '</div>' +
            '<div class="row text-center">' +
            '<div class="col-sm-12 text-center">' +
            '<a href="javascript:void(0);" class="btn btn-primary text-center next-page-btn">Next</a>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>'
        },


		{
			'thumbnail': 'preview/public_signup.png',
			'category': '100',
			'html':
				'<div class="row">' +
        		'<div class="col-md-6 offset-md-3 col-sm-12">' +
            	'<div class="card mb-3">' +
                '<div class="row no-gutters">' +
                '<div class="col-12">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Public Sign-Up</b></h4>' +
                '</div>' +
                '<form name="register" action="/user/signup/?session={{session_id}}" method="post">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
				'<div class="form-group">' +
                '<input type="text" name="username" class="form-control" id="email" placeholder="Username">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="password" class="form-control" id="password" placeholder="Password">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="verification" class="form-control" id="verification" placeholder="Re-enter Password">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="text" name="fullname" class="form-control" id="fullname" placeholder="Full Name">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="text" name="email_address" class="form-control" id="email_address" placeholder="Email Address">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="text" name="phone_number" class="form-control" id="phone_number" placeholder="Phone Number">' +
                '</div>' +
                <? foreach(array_filter(explode(",", $kiw_fields_signup_public)) as $kiw_field){ ?>
                    <?php if (!in_array($kiw_field, array("fullname", "email_address", "phone_number", ""))){ ?>
                        '<div class="form-group">' +
                        '<input type="text" class="form-control" id="<?= $kiw_field ?>" name="<?= $kiw_field ?>" placeholder="<?= $kiw_field_data_field[$kiw_field] ?>" <?= ($kiw_field_data_required[$kiw_field] == "y" ? "required" : "") ?> value="">' +
                        '</div>' +
                    <?php } ?>
				<? } ?>
                '<div class="custom-control custom-checkbox">' +
                '<input type="checkbox" class="custom-control-input" id="tnc" name="tnc" required value="">' +
                '<label for="tnc" class="custom-control-label">Terms and Condition and Privacy Policy</label>' +
                '</div>' +
                '<button type="submit" class="btn btn-primary form-control mb-3 mt-75">Register</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},


        {
			'thumbnail': 'preview/sponsor_signup.png',
			'category': '100',
			'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card mb-3">' +
                '<div class="row no-gutters">' +
                '<div class="col-12">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Sponsored Sign-Up</b></h4>' +
                '</div>' +
                '<form name="register" action="/user/sponsored/?session={{session_id}}" method="post">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<input type="text" name="fullname" class="form-control" id="fullname" placeholder="Full Name">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Username">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="password" class="form-control" id="password" placeholder="Password">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="verification" class="form-control" id="verification" placeholder="Re-enter Password">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="email" name="sponsor" class="form-control" id="sponsor" placeholder="Sponsor Email Address">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="email" name="email_address" class="form-control" id="email_addres" placeholder="Your Email Address">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="text" name="phone_number" class="form-control" id="phone_number" placeholder="Phone Number">' +
                '</div>' +
                <? foreach(explode(",", $kiw_fields_signup_sponsor) as $kiw_field){ ?>
                    <?php if (!in_array($kiw_field, array("fullname", "email_address", "phone_number", ""))){ ?>
                        '<div class="form-group">' +
                        '<input type="text" class="form-control" id="<?= $kiw_field ?>" name="<?= $kiw_field ?>" placeholder="<?= $kiw_field_data_field[$kiw_field] ?>" <?= ($kiw_field_data_required[$kiw_field] == "y" ? "required" : "") ?> value="">' +
                        '</div>' +
                    <?php } ?>
				<? } ?>
                '<div class="custom-control custom-checkbox">' +
                '<input type="checkbox" class="custom-control-input" id="tnc" name="tnc" required value="">' +
                '<label for="tnc" class="custom-control-label">Terms and Condition and Privacy Policy</label>' +
                '</div>' +
                '<button type="submit" class="btn btn-primary form-control mb-3 mt-75">Register</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
		},


		{
			'thumbnail': 'preview/email_verification.png',
			'category': '100',
			'html':
				'<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
            	'<div class="card mb-3">' +
                '<div class="row no-gutters">' +
                '<div class="col-12">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Email Verification Sign-Up</b></h4>' +
                '</div>' +
                '<form name="register" action="/user/verification/email/?session={{session_id}}" method="post">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<input type="text" name="fullname" class="form-control" id="fullname" placeholder="Name">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="email" name="username" class="form-control" id="username" placeholder="Email Address">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="password" class="form-control" id="password" placeholder="Password">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="verification" class="form-control" id="verification" placeholder="Re-enter Password">' +
                '</div>' +
                <? foreach(explode(",", $kiw_fields_email_verification) as $kiw_field){ ?>
                    <?php if (!in_array($kiw_field, array("fullname", "email_address", "phone_number", ""))){ ?>
                        '<div class="form-group">' +
                        '<input type="text" class="form-control" id="<?= $kiw_field ?>" name="<?= $kiw_field ?>" placeholder="<?= $kiw_field_data_field[$kiw_field] ?>" <?= ($kiw_field_data_required[$kiw_field] == "y" ? "required" : "") ?> value="">' +
                        '</div>' +
                    <?php } ?>
				<? } ?>
                '<div class="custom-control custom-checkbox">' +
                '<input type="checkbox" class="custom-control-input" id="tnc" name="tnc" required value="">' +
                '<label for="tnc" class="custom-control-label">Terms and Condition and Privacy Policy</label>' +
                '</div>' +
                '<button type="submit" class="btn btn-primary form-control mb-3 mt-75">Register</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},


		{
			'thumbnail': 'preview/sms_signup.png',
			'category': '100',
			'html':
				'<div class="row">' +
        		'<div class="col-md-6 offset-md-3 col-sm-12">' +
            	'<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>SMS Sign-Up</b></h4>' +
                '</div>' +
                '<form name="register" method="post" action="/user/verification/sms/register/?session={{session_id}}">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<input type="text" name="fullname" class="form-control" id="fullname" placeholder="Full Name">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Phone Number">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="password" class="form-control" id="password" placeholder="Password">' +
                '</div>' +
                '<div class="form-group">' +
                '<input type="password" name="verification" class="form-control" id="verification" placeholder="Re-enter Password">' +
                '</div>' +
                <? foreach(explode(",", $kiw_fields_sms_verification) as $kiw_field){ ?>
                    <?php if (!in_array($kiw_field, array("fullname", "email_address", "phone_number", ""))){ ?>
                        '<div class="form-group">' +
                        '<input type="text" class="form-control" id="<?= $kiw_field ?>" name="<?= $kiw_field ?>" placeholder="<?= $kiw_field_data_field[$kiw_field] ?>" <?= ($kiw_field_data_required[$kiw_field] == "y" ? "required" : "") ?> value="">' +
                        '</div>' +
                    <?php } ?>
				<? } ?>
                '<div class="custom-control custom-checkbox">' +
                '<input type="checkbox" class="custom-control-input" id="tnc" name="tnc" required value="">' +
                '<label for="tnc" class="custom-control-label">Terms and Condition and Privacy Policy</label>' +
                '</div>' +
                '<button type="submit" class="btn pull-right btn-primary mb-2 mt-75">Register</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},


		{
			'thumbnail': 'preview/sms_request_otp.png',
			'category': '100',
			'html':
				'<div class="row">' +
        		'<div class="col-md-6 offset-md-3 col-sm-12">' +
            	'<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>SMS Request OTP</b></h4>' +
                '</div>' +
                '<form name="otp-request" method="post" action="/user/verification/sms/otp/?session={{session_id}}">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Phone Number">' +
                '</div>' +
                '<button type="submit" class="btn pull-right btn-primary mb-2">Request OTP</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},





        {
            'thumbnail': 'preview/temp_access.png',
            'category': '100',
            'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="row-12 center">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Account Created - Temporary Access!</b></h4>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-12">' +
                'You are required to verify your account by click on the link in the email within 5 minutes.' +
                '</div>' +
                '</div>' +
                '<div class="row mt-75">' +
                '<div class="col-12">' +
                '<form action="">' +
                '<input type="hidden" name="username" value="{{username}}">' +
                '<input type="hidden" name="password" value="{{password}}">' +
                '<input type="submit" class="btn btn-primary" value="Login to Wifi">' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },


        {
            'thumbnail': 'preview/pending_verify.png',
            'category': '100',
            'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="row-12 center">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Account Created - Pending Verification!</b></h4>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-12">' +
                'Your account has been created. You will be able to access internet using this account once your sponsor verify this.' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' 
        },


        {
            'thumbnail': 'preview/acc_verified.png',
            'category': '100',
            'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="row-12 center">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Account Has Been Verified!</b></h4>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-12">' +
                'This account has been verified. Please inform the account holder so that they can access the internet.' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },


        {
            'thumbnail': 'preview/sms_register.png',
            'category': '100',
            'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Verify OTP for SMS Registration</b></h4>' +
                '</div>' +
                '<form method="post" action="/user/verification/sms/register/?session={{session_id}}">' +
                '<div class="input-group mb-2">' +
                '<div class="input-group-prepend has-icon-left">' +
                '<span class="input-group-text fa fa-phone" id=""></span>' +
                '</div>' +
                '<input type="text" name="code" class="form-control" id="code" placeholder="OTP Code">' +
                '</div>' +
                '<button type="submit" class="btn pull-right btn-primary mb-2">Login</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },


        {
            'thumbnail': 'preview/otp_login.png',
            'category': '100',
            'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Verify OTP for OTP Login</b></h4>' +
                '</div>' +
                '<form method="post" action="/user/verification/sms/otp/?session={{session_id}}">' +
                '<div class="input-group mb-2">' +
                '<div class="input-group-prepend has-icon-left">' +
                '<span class="input-group-text fa fa-phone" id=""></span>' +
                '</div>' +
                '<input type="text" name="code" class="form-control" id="code" placeholder="OTP Code">' +
                '</div>' +
                '<button type="submit" class="btn pull-right btn-primary mb-2">Login</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },

        {
            'thumbnail': 'preview/forgot_pass.png',
            'category': '100',
            'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Forgot Password</b></h4>' +
                '</div>' +
                '<form method="post" action="/user/forgot/?session={{session_id}}">' +
                '<div class="mb-2">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Username">' +
                '</div>' +
                '<div class="mb-2">' +
                '<input type="text" name="email_address" class="form-control" id="email_address" placeholder="Email Address">' +
                '</div>' +
                '<div class="mb-2">' +
                '<input type="text" name="phone_number" class="form-control" id="phone_number" placeholder="Phone Number">' +
                '</div>' +
                '<button type="submit" class="btn pull-right btn-primary mb-2">Submit</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },


     
		{
			'thumbnail': 'preview/change_pass.png',
			'category': '100',
			'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Change Password</b></h4>' +
                '</div>' +
                '<form method="post" action="/user/change/?session={{session_id}}">' +
                '<div class="mb-2">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Username">' +
                '</div>' +
                '<div class="mb-2">' +
                '<input type="password" name="password" class="form-control" id="password" placeholder="Current Password">' +
                '</div>' +
                '<div class="mb-2">' +
                '<input type="password" name="new_password" class="form-control" id="new_password" placeholder="New Password">' +
                '</div>' +
                '<div class="mb-2">' +
                '<input type="password" name="ver_password" class="form-control" id="ver_password" placeholder="Re-type New Password">' +
                '</div>' +
                '<button type="submit" class="btn pull-right btn-primary mb-2">Submit</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },

        {
            'thumbnail': 'preview/topup_code.png',
            'category': '100',
            'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>Topup Code</b></h4>' +
                '</div>' +
                '<form method="post" action="user/topup/index.php">' +
                '<div class="mb-2">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Username">' +
                '</div>' +
                '<div class="mb-2">' +
                '<input type="text" name="code" class="form-control" id="code" placeholder="Code">' +
                '</div>' +
                '<button type="submit" class="btn pull-right btn-primary mb-2">Submit</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },
        
		{
			'thumbnail': 'preview/photo_pleasewait.png',
			'category': '100',
			'html':
				'<div class="row mb-3">' +
        		'<div class="col-md-10 offset-md-1 col-sm-12">' +
            	'<div class="card mb-1">' +
                '<div class="row no-gutters">' +
                '<div class="col-12" style="min-height: 500px;">' +
                '<div id="campaign-1" style="height: 100%; width: 100%; background-color: lightgrey;"></div>' +
                '<div class="campaign-1-name"></div>' +
                '<div class="campaign-1-description"></div>' +
                '<div class="campaign-1-startdate"></div>' +
                '<div class="campaign-1-enddate"></div>' +
                '</div>' +
                '</div>' +
            	'</div>' +
            	'<div class="row">' +
                '<div class="col-2 offset-5 text-center">' +
                '<button class="btn btn-primary campaign-btn waves-effect waves-light">Please wait..</button>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},


		{
			'thumbnail': 'preview/photo2_pleasewait.png',
			'category': '100',
			'html':
				'<div class="row mb-3">' +
        		'<div class="col-md-10 offset-md-1 col-sm-12">' +
            	'<div class="card mb-1">' +
                '<div class="row no-gutters">' +
                '<div class="col-12" style="min-height: 500px;">' +
                '<div id="campaign-1" style="height: 100%; width: 100%; background-color: lightgrey;"></div>' +
                '</div>' +
                '</div>' +
            	'</div>' +
            	'<div class="card mb-1">' +
                '<div class="row no-gutters">' +
                '<div class="col-12" style="min-height: 500px;">' +
                '<div id="campaign-2" style="height: 100%; width: 100%; background-color: lightgrey;"></div>' +
                '</div>' +
                '</div>' +
            	'</div>' +
                '<div class="row">' +
                '<div class="col-2 offset-5 text-center">' +
                '<button class="btn btn-primary campaign-btn waves-effect waves-light">Please wait..</button>' +
                '</div>' +
                '</div>' +
        		'</div>' +
    			'</div>'
		},


		{
			'thumbnail': 'preview/photo3_pleasewait.png',
			'category': '100',
			'html':
				'<div class="row mb-3">' +
        		'<div class="col-md-10 offset-md-1 col-sm-12">' +
            	'<div class="card mb-1">' +
                '<div class="row no-gutters">' +
                '<div class="col-12" style="min-height: 500px;">' +
                '<div id="campaign-1" style="height: 100%; width: 100%; background-color: lightgrey;"></div>' +
                '</div>' +
                '</div>' +
            	'</div>' +
            	'<div class="mb-1">' +
                '<div class="row no-gutters">' +
                '<div class="col-6" style="min-height: 250px;">' +
                '<div id="campaign-2" style="height: 100%; width: 100%; background-color: lightgrey;"></div>' +
                '</div>' +
                '<div class="col-6" style="min-height: 250px;">' +
                '<div id="campaign-3" style="height: 100%; width: 100%; background-color: grey;"></div>' +
                '</div>' +
                '</div>' +
            	'</div>' +
                '<div class="row">' +
                '<div class="col-2 offset-5 text-center">' +
                '<button class="btn btn-primary campaign-btn waves-effect waves-light">Please wait..</button>' +
                '</div>' +
                '</div>' +
        		'</div>' +
    			'</div>'
		},


		{
			'thumbnail': 'preview/survey.png',
			'category': '100',
			'html':
				'<div class="row mb-3">' +
        		'<div class="col-md-10 offset-md-1 col-sm-12">' +
            	'<div class="card mb-1">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="row no-gutters mb-2">' +
                '<div class="col-12" style="min-height: 100px;">' +
                '<div style="padding: 10px; height: 100%; width: 100%; background-color: lightgrey; border-radius: 5px;">' +
                '<div class="survey-title" style="margin: auto; width: 20%; text-align: center;"><h4>Survey Title</h4></div>' +
                '<div class="survey-description" style="text-align: center;">Survey description</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<form class="form form-horizontal survey" data-survey="test" action="/user/survey/?session={{session_id}}" method="post">' +
                '<div class="form-body">' +
                '<div class="row p-10">' +
                '<div class="col-12 mb-2">' +
                '<div class="mb-1"><h5>Question 1</h5></div>' +
                '<div class="mb-1">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean id metus vehicula, sagittis ex sed, elementum purus.</div>' +
                '<div class="mb-1"><input type="text" class="form-control"></div>' +
                '</div>' +
                '<div class="col-12 mb-2">' +
                '<div class="mb-1"><h5>Question 2</h5></div>' +
                '<div class="mb-1">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean id metus vehicula, sagittis ex sed, elementum purus?</div>' +
                '<div class="mb-1">' +
                '<select class="form-control" name="" id="">' +
                '<option value="test1">Test 1</option>' +
                '<option value="test2">Test 2</option>' +
                '<option value="test3">Test 3</option>' +
                '</select>' +
                '</div>' +
                '</div>' +
                '<div class="col-12 mb-2">' +
                '<div class="mb-1"><h5>Question 3</h5></div>' +
                '<div class="mb-1">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean id metus vehicula, sagittis ex sed, elementum purus.</div>' +
                '<div class="mb-1">' +
                '<div class="form-check">' +
                '<input type="checkbox" name="test4" value="test" id="test4" class="form-check-input">' +
                '<label for="test4" class="form-check-label">Test 4</label>' +
                '</div>' +
                ' <div class="form-check">' +
                '<input type="checkbox" name="test5" value="test" id="test5" class="form-check-input">' +
                '<label for="test5" class="form-check-label">Test 5</label>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-2">' +
                '<button type="submit" class="survey-button btn btn-primary">Submit</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},


		{
			'thumbnail': 'preview/rate.png',
			'category': '100',
			'html':
                '<div class="row">' +
                '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<form method="post" action="/user/nps/?session={{session_id}}">' +
                '<div class="col-12 center">' +
                '<h5>Please rate our network performance</h5>' +
                '</div>' +
                '<div class="col-12 center">' +
                '<div class="btn-group" role="group">' +
                '<button type="button" class="btn btn-danger text-white btn-nps" data-rate="1">1</button>' +
                '<button type="button" class="btn btn-danger text-white btn-nps" data-rate="2">2</button>' +
                '<button type="button" class="btn btn-danger text-white btn-nps" data-rate="3">3</button>' +
                '<button type="button" class="btn btn-danger text-white btn-nps" data-rate="4">4</button>' +
                '<button type="button" class="btn btn-warning text-white btn-nps" data-rate="5">5</button>' +
                '<button type="button" class="btn btn-warning text-white btn-nps" data-rate="6">6</button>' +
                '<button type="button" class="btn btn-warning text-white btn-nps" data-rate="7">7</button>' +
                '<button type="button" class="btn btn-success text-white btn-nps" data-rate="8">8</button>' +
                '<button type="button" class="btn btn-success text-white btn-nps" data-rate="9">9</button>' +
                '<button type="button" class="btn btn-success text-white btn-nps" data-rate="10">10</button>' +
                '<input type="hidden" name="nps_rate" class="form-control">' +
                '</div>' +
                '</div>' +
                '<div class="col-8 offset-2 center mt-1">' +
                '<textarea class="form-control" rows="3" placeholder="Comment / Suggestion" name="nps_comment"></textarea>' +
                '</div>' +
                '<div class="col-12 mt-1 center">' +
                '<button type="submit" class="btn btn-primary mb-2">Submit</button>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },


        {
            'thumbnail': 'preview/basic-05.png',
            'category': '100',
            'html':
		        '<div class="row clearfix">' +
			    '<div class="column full">' +
				'<img src="assets/minimalist-blocks/images/oleg-laptev-545268-unsplash-VD7ll2.jpg">' +
			    '</div>' +
		        '</div>'
        },


        {
            'thumbnail': 'preview/element-video.png',
            'category': '100',
            'html':
            	'<div class="embed-responsive embed-responsive-16by9">' +
                '<iframe width="560" height="315" src="//www.youtube.com/embed/P5yHEKqx86U?rel=0" frameborder="0" allowfullscreen=""></iframe>' +
                '</div>'
        },


        {
            'thumbnail': 'preview/basic-12.png',
            'category': '100',
            'html':
        		'<div class="row clearfix">' +
        		'<div class="column full">' +
        	    '<div class="spacer height-80"></div>' +
        		'</div>' +
        		'</div>'
        },


        {
            'thumbnail': 'preview/header-07.png',
            'category': '100',
            'html':
            	'<div class="row clearfix">' +
            	'<div class="column full center">' +
            	'<div class="display">' +
            	_tabs(1) + '<h1 class="size-92" style="margin-bottom:0;">Outstanding</h1>' +
            	_tabs(1) + '<p style="margin-top:0">Lorem Ipsum is dummy text of the printing and typesetting industry</p>' +
            	'\n</div>' +
            	'</div>' +
            	'</div>' +
            	'<div class="row clearfix">' +
            	'<div class="column full center">' +
            	'<a href="javascript:void(0);" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Contact Us</a>' +
            	'</div>' +
            	'</div>'
        },


        {
            'thumbnail': 'preview/button-01.png',
            'category': '100',
            'html':
		        '<div>' +
                '<a href="javascript:void(0);" class="is-btn is-btn-ghost2 is-upper">Read More</a> &nbsp;' +
                '\n<a href="javascript:void(0);" class="is-btn is-btn-ghost1 is-upper">Buy Now</a>' +
                '</div>'
        },


        {
            'thumbnail': 'preview/photos-52.png',
            'category': '100',
            'html':
        		'<div class="row clearfix">' +
                '<div class="column third">' +
                '<img src="assets/minimalist-blocks/images/chuttersnap-413002-unsplash-83HqE1.jpg" style="margin: 0;float: left;">' +
                '</div>' +
                '<div class="column third">' +
                '<img src="assets/minimalist-blocks/images/caroline-bertolini-270870-unsplash-1j5FB2.jpg" style="margin: 0;float: left;">' +
                '</div>' +
                '<div class="column third">' +
                '<img src="assets/minimalist-blocks/images/theo-roland-740436-unsplash-WqnWJ3.jpg" style="margin: 0;float: left;">' +
                '</div>' +
                '</div>'
        },



        {
            'thumbnail': 'preview/steps-13.png',
            'category': '100',
            'html':
		        '<div class="row clearfix">' +
		        '<div class="column full center">' +
		        '<h1 style="letter-spacing: 3px;">WORK STEPS</h1>' +
		        '\n<p style="border-bottom: 2px solid #333; width: 40px; display: inline-block;"></p>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column full">' +
		        '<div class="spacer height-40"></div>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column third">' +
		        '<h1 class="size-80" style="text-align: center;">01</h1>' +
		        '\n<p style="text-align: left;">Lorem Ipsum is simply dummy text of the printing industry. Vivamus leo ante, dolor sit amet vel.</p>' +
		        '</div>' +
		        '<div class="column third">' +
		        '<h1 class="size-80" style="text-align: center;">02</h1>' +
		        '\n<p>Lorem Ipsum is simply dummy text of the printing industry. Vivamus leo ante, dolor sit amet vel.</p>' +
		        '</div>' +
		        '<div class="column third">' +
		        '<h1 class="size-80" style="text-align: center;">03</h1>' +
		        '\n<p>Lorem Ipsum is simply dummy text of the printing industry. Vivamus leo ante, dolor sit amet vel.</p>' +
		        '</div>' +
		        '</div>'
        },

        {
        'thumbnail': 'preview/faq-01.png',
        'category': '100',
        'html': '<div class="col-md-6 offset-md-3 col-sm-12">' +
                '<div class="card">' +
                '<div class="row no-gutters">' +
                '<div class="col-1" style="background-color:#d3d3d3;">' +
                '<div id="campaign-1" style="height: 100%; width: 100%;"></div>' +
                '</div>' +
                '<div class="col-8">' +
                '<div class="card-body">' +
                '<div class="form-group">' +
                '<label for="username">Username:</label>' +
                '<input type="text" class="form-control" id="username" value="{{username}}" disabled>' +
                '</div>' +
                '<div class="form-group">' +
                '<label for="session_time">Session Time:</label>' +
                '<input type="text" class="form-control" id="session_time" value="{{session_time}}" disabled>' +
                '</div>' +
                '<div class="form-group">' +
                '<label for="">Quota Out:</label>' +
                '<input type="text" class="form-control" id="" value="{{quota_out}}" disabled>' +
                '</div>' +
                '<div class="form-group">' +
                '<label for="">Quota In:</label>' +
                '<input type="text" class="form-control" id="" value="{{quota_in}}" disabled>' +
                '</div>' +
                '<div class="form-group">' +
                '<label for="">Activated On:</label>' +
                '<input type="text" class="form-control" id="" value="{{date_activate}}" disabled>' +
                '</div>' +
                '<div class="form-group">' +
                '<label for="">Expired On:</label>' +
                '<input type="text" class="form-control" id="" value="{{date_expiry}}" disabled>' +
                '</div>' +
                '<div class="form-group">' +
                '<label for="">Status:</label>' +
                '<input type="text" class="form-control" id="" value="{{status}}" disabled>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },


        {
            'thumbnail': 'preview/asfeaturedon-05.png',
            'category': '100',
            'html':
		        '<div class="row clearfix">' +
		        '<div class="column full center">' +
		        '<h1 class="size-48" style="letter-spacing: 7px;">AS FEATURED ON</h1>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column full">' +
		        '<div class="spacer height-60"></div>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column fourth center">' +
		        '<img src="assets/minimalist-blocks/images/upclick.png">' +
		        '</div>' +
		        '<div class="column fourth center">' +
		        '<img src="assets/minimalist-blocks/images/digitalmag.png">' +
		        '</div>' +
		        '<div class="column fourth center">' +
		        '<img src="assets/minimalist-blocks/images/mmedia.png">' +
		        '</div>' +
		        '<div class="column fourth center">' +
		        '<img src="assets/minimalist-blocks/images/bbuzz.png">' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column fourth center">' +
		        '<img src="assets/minimalist-blocks/images/prosource.png">' +
		        '</div>' +
		        '<div class="column fourth center">' +
		        '<img src="assets/minimalist-blocks/images/light-studio.png">' +
		        '</div>' +
		        '<div class="column fourth center">' +
		        '<img src="assets/minimalist-blocks/images/nett.png">' +
		        '</div>' +
		        '<div class="column fourth center">' +
		        '<img src="assets/minimalist-blocks/images/worldwide.png">' +
		        '</div>' +
		        '</div>'
        },



        {
            'thumbnail': 'preview/faq-01.png',
            'category': '100',
            'html':
        		'<div class="row clearfix">' +
        		'<div class="column full">' +
        		'<h1 class="size-64 is-title1-64 is-title-lite">FAQs</h1>' +
        		'</div>' +
        		'</div>' +
        		'<div class="row clearfix">' +
        		'<div class="column full center">' +
        		'<div class="spacer height-40"></div>' +
        		'</div>' +
        		'</div>' +
        		'<div class="row clearfix">' +
        		'<div class="column half">' +
        		'\n<h3 class="size-24">How do I sign up? </h3>' +
        		'\n<p>Lorem Ipsum is simply dummy text of the printing industry. Lorem ipsum dolor sit amet.</p>' +
        		'</div>' +
        		'<div class="column half">' +
        		'\n<h3 class="size-24">How do I cancel my order?</h3>' +
        		'\n<p>Lorem Ipsum is simply dummy text of the printing industry. Lorem ipsum dolor sit amet.</p>' +
        		'</div>' +
        		'</div>' +
        		'<div class="row clearfix">' +
        		'<div class="column half">' +
        		'\n<h3 class="size-24">What is account limits?</h3>' +
        		'\n<p>Lorem Ipsum is simply dummy text of the printing industry. Lorem ipsum dolor sit amet.</p>' +
        		'</div>' +
        		'<div class="column half">' +
        		'\n<h3 class="size-24">How do I update my settings?</h3>' +
        		'\n<p>Lorem Ipsum is simply dummy text of the printing industry. Lorem ipsum dolor sit amet.</p>' +
        		'</div>' +
        		'</div>'
        },



        {
            'thumbnail': 'preview/faq-02.png',
            'category': '100',
            'html':
        		'<div class="row clearfix">' +
        		'<div class="column full">' +
        		'<h1 class="size-48 is-title1-48 is-title-lite">FAQs</h1>' +
        		'</div>' +
        		'</div>' +
        		'<div class="row clearfix">' +
        		'<div class="column full">' +
        		'<div class="spacer height-40"></div>' +
        		'</div>' +
        		'</div>' +
        		'<div class="row clearfix">' +
        		'<div class="column half">' +
        		'<h3 class="size-24">How do I create an account?</h3>' +
        		'\n<p style="border-bottom: 2px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' +
        		'\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet.</p>' +
        		'</div>' +
        		'<div class="column half">' +
        		'<h3 class="size-24">How do I cancel my order?</h3>' +
        		'\n<p style="border-bottom: 2px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' +
        		'\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet.</p>' +
        		'</div>' +
        		'</div>' +
        		'<div class="row clearfix">' +
        		'<div class="column full">' +
        		'<div class="spacer height-40"></div>' +
        		'</div>' +
        		'</div>' +
        		'<div class="row clearfix">' +
        		'<div class="column half">' +
        		'<h3 class="size-24">How do I close my account?</h3>' +
        		'\n<p style="border-bottom: 2px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' +
        		'\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet.</p>' +
        		'</div>' +
        		'<div class="column half">' +
        		'<h3 class="size-24">How do I update my settings?</h3>' +
        		'\n<p style="border-bottom: 2px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' +
        		'\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet.</p>' +
        		'</div>' +
        		'</div>'
        },



        {
            'thumbnail': 'preview/faq-03.png',
            'category': '100',
            'html':
		        '<div class="row clearfix">' +
		        '<div class="column full center">' +
		        '<h1 class="size-64 is-title1-64 is-title-bold">FAQs</h1>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column full center">' +
		        '<div class="spacer height-40"></div>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column full">' +
		        '<h3>How do I sign up?</h3>' +
		        '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.</p>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column full">' +
		        '<h3>How do I cancel or change my order?</h3>' +
		        '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.</p>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column full">' +
		        '<h3>How do I contact customer support?</h3>' +
		        '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.</p>' +
		        '</div>' +
		        '</div>'
        },



        {
            'thumbnail': 'preview/faq-04.png',
            'category': '100',
            'html':
		        '<div class="row clearfix">' +
		        '<div class="column full">' +
		        '<h1 class="size-48 is-title1-48 is-title-lite is-lite" style="text-align: center;">Frequently Asked Questions</h1>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column full">' +
		        '<div class="spacer height-40"></div>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column third">' +
		        '<h3 class="size-21">HOW DO I CREATE AN ACCOUNT?</h3>' +
		        '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' +
		        '\n<p style="border-bottom: 2px solid #e67e22; width: 45px; display: inline-block;"></p>' +
		        '</div>' +
		        '<div class="column third">' +
		        '<h3 class="size-21">WHAT\'S ACCOUNT LIMITS?</h3>' +
		        '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' +
		        '\n<p style="border-bottom: 2px solid #e67e22; width: 45px; display: inline-block;"></p>' +
		        '</div>' +
		        '<div class="column third">' +
		        '<h3 class="size-21">HOW DO I CANCEL MY ORDER?</h3>' +
		        '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' +
		        '\n<p style="border-bottom: 2px solid #e67e22; width: 45px; display: inline-block;"></p>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column third">' +
		        '<h3 class="size-21">HOW DO I RESET MY PASSWORD?</h3>' +
		        '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' +
		        '\n<p style="border-bottom: 2px solid #e67e22; width: 45px; display: inline-block;"></p>' +
		        '</div>' +
		        '<div class="column third">' +
		        '<h3 class="size-21">HOW DO I REPORT A BUG?</h3>' +
		        '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' +
		        '\n<p style="border-bottom: 2px solid #e67e22; width: 45px; display: inline-block;"></p>' +
		        '</div>' +
		        '<div class="column third">' +
		        '<h3 class="size-21">HOW DO I CLOSE MY ACCOUNT?</h3>' +
		        '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' +
		        '\n<p style="border-bottom: 2px solid #e67e22; width: 45px; display: inline-block;"></p>' +
		        '</div>' +
		        '</div>'
        },


        {
            'thumbnail': 'preview/faq-05.png',
            'category': '100',
            'html':
		        '<div class="row clearfix">' +
		        '<div class="column full center">' +
		        '<h1 class="size-48" style="letter-spacing: 2px;">FAQ</h1>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column full">' +
		        '<div class="spacer height-40"></div>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column half">' +
		        '<h3 class="size-21 default-font2">HOW DO I SIGN UP?</h3>' +
		        '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur elit.</p>' +
		        '</div>' +
		    	'<div class="column half">' +
		        '<h3 class="size-21 default-font2">WHAT\'S ACCOUNT LIMIT?</h3>' +
		        '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur elit.</p>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column half">' +
		        '<h3 class="size-21 default-font2">HOW DO I CONTACT SUPPORT?</h3>' +
		        '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur elit.</p>' +
		        '</div>' +
		        '<div class="column half">' +
		        '<h3 class="size-21 default-font2">HOW DO I UPDATE MY SETTINGS?</h3>' +
		        '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur elit.</p>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column half">' +
		        '<h3 class="size-21 default-font2">HOW DO I REPORT AN ISSUE?</h3>' +
		        '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur elit.</p>' +
		        '</div>' +
		        '<div class="column half">' +
		        '<h3 class="size-21 default-font2">HOW DO I CHANGE MY ORDER?</h3>' +
		        '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur elit.</p>' +
		        '</div>' +
		        '</div>'
        },


        {
            'thumbnail': 'preview/faq-06.png',
            'category': '100',
            'html':
		        '<div class="row clearfix">' +
		        '<div class="column full center">' +
		        '<h1 class="size-48" style="letter-spacing: 2px;">FAQ</h1>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column full">' +
		        '<div class="spacer height-40"></div>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column third">' +
		        '<h3 class="size-21 default-font2">HOW DO I CREATE AN ACCOUNT? </h3>' +
		        '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' +
		        '</div>' +
		        '<div class="column third">' +
		    	'<h3 class="size-21 default-font2">HOW DO I UPDATE MY SETTINGS?</h3>' +
		        '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' +
		        '</div>' +
		        '<div class="column third">' +
		        '<h3 class="size-21 default-font2">HOW DO I CHANGE MY PASSWORD?</h3>' +
		        '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' +
		        '</div>' +
		        '</div>' +
		        '<div class="row clearfix">' +
		        '<div class="column third">' +
		        '<h3 class="size-21 default-font2">HOW DO I CANCEL MY ORDER?</h3>' +
		        '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' +
		        '</div>' +
		        '<div class="column third">' +
		        '<h3 class="size-21 default-font2">HOW DO I CLOSE MY ACCOUNT?</h3>' +
		        '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' +
		        '</div>' +
		        '<div class="column third">' +
		        '<h3 class="size-21 default-font2">HOW DO I CONTACT CUSTOMER SERVICE?</h3>' +
		        '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' +
		        '</div>' +
		        '</div>'
        },

        //testhere
        {
			'thumbnail': 'preview/using_voucher.png',
			'category': '100',
			'html':
				'<div class="row">' +
        		'<div class="col-md-6 offset-md-3 col-sm-12">' +
            	'<div class="card">' +
                '<div class="card-content">' +
                '<div class="card-body">' +
                '<div class="card-title center mb-2">' +
                '<h4 class="mb-0"><b>testing</b></h4>' +
                '</div>' +
                '<form method="post" action="/user/login/?session={{session_id}}">' +
                '<div class="notification text-center text-danger font-weight-bold m-50"></div>' +
                '<div class="form-group">' +
                '<input type="text" name="username" class="form-control" id="username" placeholder="Voucher Code">' +
                '</div>' +
                '<button type="submit" class="btn btn-primary pull-right mb-2">Login</button>' +
                '</form>' +
                '</div>' +
                '</div>' +
            	'</div>' +
        		'</div>' +
    			'</div>'
		},




	]

};

document.addEventListener("DOMContentLoaded", function (event) {
	var bHideSliderSnippet = false;
	if (typeof jQuery.contentbuilder == 'undefined') {
		//content.js is on dialog (iframe)
		if (typeof parent.jQuery.fn.slick == 'undefined') {
			bHideSliderSnippet = true;
		}

	} else {
		//content.js is on side panel
		if (typeof jQuery.fn.slick == 'undefined') {
			bHideSliderSnippet = true;
		}
	}

	for (var nIndex = 0; nIndex < data_basic.snippets.length; nIndex++) {

		if (data_basic.snippets[nIndex].thumbnail.indexOf('element-slider.png') != -1 && bHideSliderSnippet) {
			data_basic.snippets.splice(nIndex, 1);
			break;
		}
	}

});