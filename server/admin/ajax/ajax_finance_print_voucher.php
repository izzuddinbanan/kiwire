<?php


require_once dirname(__FILE__, 2) . "/includes/include_config.php";
require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";

require_once dirname(__FILE__, 3) . "/libs/phpqrcode/qrlib.php";


$kiw_username = $_REQUEST['username'];


$kiw_path = sync_brand_decrypt(SYNC_QR_URL) . "?s_type=qr&u={$kiw_username}";


QRcode::png($kiw_path, null, QR_ECLEVEL_L, 4, 1);

