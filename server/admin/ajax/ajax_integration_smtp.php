<?php

require_once dirname(__FILE__, 3) . "/libs/phpmailer/Exception.php";
require_once dirname(__FILE__, 3) . "/libs/phpmailer/PHPMailer.php";
require_once dirname(__FILE__, 3) . "/libs/phpmailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set("max_execution_time", 15);

$kiw['module'] = "Integration -> SMTP";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$action = $_REQUEST['action'];

switch ($action) {

    case "update": update(); break;
    case "test": test(); break;
    default: echo "ERROR: Wrong implementation";

}

function update()
{


    header("Content-Type: application/json");

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $data['host']           = $kiw_db->escape($_POST['host']);
        $data['port']           = $kiw_db->escape($_POST['port']);
        $data['auth']           = $kiw_db->escape($_POST['auth']);
        $data['user']           = $kiw_db->escape($_POST['user']);

        $data['password']       = $kiw_db->escape($_POST['password']);
        $data['from_email']     = $kiw_db->escape($_POST['from_email']);
        $data['from_name']      = $kiw_db->escape($_POST['from_name']);
        $data['cc_email']       = $kiw_db->escape($_POST['cc_email']);

        $data['profile']        = $kiw_db->escape($_POST['profile']);
        $data['validity']       = $kiw_db->escape($_POST['validity']);
        $data['email_template'] = $kiw_db->escape($_POST['email_template']);
        $data['allowed_domain'] = $kiw_db->escape($_POST['allowed_domain']);

        $data['data']            = $kiw_db->escape(implode(",", $_POST['data']));

        $data['updated_date']    = date('Y-m-d H-i-s');
        $data['enabled']         = (isset($_POST['enabled']) ? "y" : "n");
        $data['confirm_page']    = $_POST['confirm_page'];

        // $data['is_24_hour']     = isset($_REQUEST['is_24']) && $_REQUEST['is_24'] ? 1 : 0;
        // $data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

        // if(!$data['is_24_hour']){
            
        //     if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
        //         die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
        //     else
        //         $data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);

        // }
        
        if($kiw_db->update("kiwire_int_email", $data, "tenant_id = '$tenant_id'")){

            sync_logger("{$_SESSION['user_name']} updated Email setting ", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Email setting has been saved", "data" => null));
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }



    } else {


        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


    }


}


function test()
{


    header("Content-Type: text/plain");


    $kiw_temp['host']   = trim($_REQUEST['host']);
    $kiw_temp['port']   = trim($_REQUEST['port']);
    $kiw_temp['auth']   = trim($_REQUEST['auth']);
    $kiw_temp['user']   = trim($_REQUEST['user']);

    $kiw_temp['password']    = trim($_REQUEST['password']);
    $kiw_temp['from_email']  = trim($_REQUEST['from_email']);
    $kiw_temp['from_name']   = trim($_REQUEST['from_name']);

    $kiw_temp['cc']      = explode(",", $_REQUEST['cc_email']);
    $kiw_temp['send_to'] = trim($_REQUEST['emailto']);


    if (in_array($kiw_temp['host'], array("mail-delivery-system.synchroweb.com"))) {

        if (filter_var($kiw_temp['send_to'], FILTER_VALIDATE_EMAIL)) {


            $ch = curl_init("https://{$kiw_temp['host']}/");

            $mail_data['id']        = $kiw_temp['user'];
            $mail_data['time']      = date("Y-m-d H:i:s");
            $mail_data['from']      = $kiw_temp['from_email'];
            $mail_data['to']        = $kiw_temp['send_to'];
            $mail_data['subject']   = "Email Test: Succeed";
            $mail_data['content']   = base64_encode("Succeed! This is a test email. Generated on: " . date("Y-m-d H:i:s"));
            $mail_data['token']     = md5("{$mail_data['time']}|{$mail_data['to']}|{$kiw_temp['password']}");
            $mail_data              = json_encode($mail_data);

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Content-Length: ' . strlen($mail_data)));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $mail_data);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = json_decode(curl_exec($ch));

            curl_close($ch);


        } else {

            $response = "Please provide email address to send email";

        }


        echo "<span>RESPONSE:</span> <br> <pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";


    } else {


        if (filter_var($kiw_temp['send_to'], FILTER_VALIDATE_EMAIL)) {

            echo "<pre>";


            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 1;
            $mail->Timeout = 5;

            $mail->Host = trim($kiw_temp['host']);
            $mail->Port = trim($kiw_temp['port']);


            if (!empty($kiw_temp['auth']) && $kiw_temp['auth'] != "none") {
                $mail->SMTPSecure = trim($kiw_temp['auth']);
            }


            if (!empty($kiw_temp['user']) && !empty($kiw_temp['password'])) {

                $mail->SMTPAuth = true;
                $mail->Username = $kiw_temp['user'];
                $mail->Password = $kiw_temp['password'];

            }


            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->isSMTP();

            try {

                $mail->setFrom($kiw_temp['from_email'], $kiw_temp['from_name']);
                $mail->addAddress($kiw_temp['send_to'], $kiw_temp['send_to']);

                foreach ($kiw_temp['cc'] as $kiw_email){

                    if (!empty(trim($kiw_email))){

                        $mail->addCC(trim($kiw_email));

                    }

                }

                $mail->addReplyTo($kiw_temp['from_email'], $kiw_temp['from_name']);
                $mail->isHTML(true);

                $mail->Subject = "SMTP Test: Succeed";
                $mail->Body = "Succeed! This is a SMTP test email. Generated on: " . date("Y-m-d H:i:s");

                $mail->send();


            } catch (Exception $e) {

                echo $e->getMessage();

            }

            echo "</pre>";


        } else {

            echo "Please provide email address to send email";

        }


    }

}
