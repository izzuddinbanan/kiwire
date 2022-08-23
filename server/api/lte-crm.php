<?php


global $kiw_request, $kiw_api, $kiw_roles;

require_once dirname(__FILE__, 2) . "/admin/includes/include_general.php";


if (in_array("Account -> Account -> List", $kiw_roles) == false) {

    die(json_encode(array("status" => "error", "message" => "This API key not allowed to access this module [ {$request_module[0]} ]", "data" => "")));

}

if ($kiw_request['method'] == "POST") {
    
    if(isset($_GET['action'])){

        switch ($_GET['action']) {

            case "activation": user_batch_activation($kiw_request); break;
            case "suspension": user_batch_suspension($kiw_request); break;
    
            default: echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "404", "message" => "Action not found")));
    
        }
    }
    else if(isset($_POST['action'])){

        switch ($_POST['action']) {

            case "activation": user_activation($kiw_request); break;
            case "suspension": user_suspension($kiw_request); break;
    
            default: echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "404", "message" => "Action not found")));
    
        }
    }
}


function user_activation($kiw_request){

    global $kiw_db;

    $kiw_request = array_merge($kiw_request, $_POST);

    if($kiw_request['imsi'] != '' && $kiw_request['tenant'] != '' && $kiw_request['profile'] != ''){
        
        $kiw_acc = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_request['imsi']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");

        if($kiw_acc){

            if($kiw_acc['status'] == 'suspend' || $kiw_acc['status'] == 'expired'){

                if($kiw_acc['profile_subs'] == $kiw_request['profile']){

                    //update status
                    $kiw_data['status'] = 'active';
                    $kiw_data['date_activate'] = date('Y-m-d H:i:s');

                    if(isset($kiw_request['email']) && $kiw_request['email'] != '') $kiw_data['email_address']   = $kiw_request['email'];
                    if(isset($kiw_request['phone']) && $kiw_request['phone'] != '') $kiw_data['phone_number']    = $kiw_request['phone'];
                    if(isset($kiw_request['other']) && $kiw_request['other'] != '') $kiw_data['remark']          = $kiw_request['other'];
                    
                    if($kiw_acc['status'] == 'expired')  $kiw_data['date_expiry'] =  date('Y-m-d H:i:s',strtotime($kiw_acc['date_expiry'] . " + 365 day"));


                    $kiw_db->update("kiwire_account_auth", $kiw_data, "username = '{$kiw_request['imsi']}' AND tenant_id = '{$kiw_request['tenant']}'");
                    
                    unset($kiw_acc);
                    
                    if ($kiw_db->db_affected_row > 0) {

                        $kiw_acc = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_request['imsi']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");

                        $kiw_return = array(
                            'IMSI'          => $kiw_acc['username'],                    
                            'email'         => $kiw_acc['email_address'],                    
                            'phone'         => $kiw_acc['phone_number'],                    
                            'profile'       => $kiw_acc['profile_subs'],                    
                            'date_activate' => $kiw_acc['date_activate'],                    
                            'other'         => $kiw_acc['remark'],                    
                        );

                        // $kiw_hss = $kiw_db->query_first("SELECT isdn FROM kiwire_account_hss WHERE username = '{$kiw_request['imsi']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");

                        // if($kiw_hss) $kiw_return['isdn'] = $kiw_hss['isdn'];

                        echo json_encode(array("data" => $kiw_return, "status" => array("type" => "success", "code" => "200", "message" => "")));
            
                        logger_api($kiw_request['tenant'], "[ LTE-CRM ACTIVATE ]  {$kiw_request['api_key']} activated user {$kiw_request['imsi']}");
            
            
                    } else  echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "Database Error (something went wrong on our end)")));

                }else echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "Profile not found")));
                
            }else if($kiw_acc['status'] == 'active')  echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "Account already active")));

        } else echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "No account found"))); 

    } else echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "Parameter not recognized")));

}

function user_suspension($kiw_request){

    global $kiw_db;

    $kiw_request = array_merge($kiw_request, $_POST);

    if($kiw_request['imsi'] != '' && $kiw_request['tenant'] != ''){
        
        $kiw_acc = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_request['imsi']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");

        if($kiw_acc){

            if($kiw_acc['status'] == 'active'){

                // if($kiw_acc['profile_subs'] == $kiw_request['profile']){

                    //update status
                    $kiw_data['status'] = 'suspend';

                    // if(isset($kiw_request['email']) && $kiw_request['email'] != '') $kiw_data['email_address']   = $kiw_request['email'];
                    // if(isset($kiw_request['phone']) && $kiw_request['phone'] != '') $kiw_data['phone_number']    = $kiw_request['phone'];
                    if(isset($kiw_request['other']) && $kiw_request['other'] != '') $kiw_data['remark']          = $kiw_request['other'];


                    $kiw_db->update("kiwire_account_auth", $kiw_data, "username = '{$kiw_request['imsi']}' AND tenant_id = '{$kiw_request['tenant']}'");
                    
                    unset($kiw_acc);
                    
                    if ($kiw_db->db_affected_row > 0) {

                        $kiw_acc = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_request['imsi']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");

                        $kiw_return = array(
                            'IMSI'          => $kiw_acc['username'],                    
                            'email'         => $kiw_acc['email_address'],                    
                            'phone'         => $kiw_acc['phone_number'],                    
                            'profile'       => $kiw_acc['profile_subs'],                    
                            'other'         => $kiw_acc['remark'],                    
                        );

                        // $kiw_hss = $kiw_db->query_first("SELECT isdn FROM kiwire_account_hss WHERE username = '{$kiw_request['imsi']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");

                        // if($kiw_hss) $kiw_return['isdn'] = $kiw_hss['isdn'];

                        echo json_encode(array("data" => $kiw_return, "status" => array("type" => "success", "code" => "200", "message" => "")));
            
                        logger_api($kiw_request['tenant'], "[ LTE-CRM SUSPEND ]  {$kiw_request['api_key']} suspended user {$kiw_request['imsi']}");
            
            
                    } else  echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "Database Error (something went wrong on our end)")));

                // }else echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "Profile not found")));
                
            }else if($kiw_acc['status'] == 'suspend' || $kiw_acc['status'] == 'expired')  echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "Account is non-active")));

        } else echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "No account found"))); 

    } else echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "Parameter not recognized")));


}

function user_batch_activation($kiw_request){

    global $kiw_db;

    $users_data = file_get_contents("php://input");

    $users_data = json_decode($users_data, true);

    if(empty($kiw_request['tenant'])){
        echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "Tenant is required")));
        die();
    }

    //CHECK REQUEST MAKE SURE HAVE DATA SENT
    if(is_array($users_data) && sizeof($users_data) > 0){

        //LOOP REQUEST DATA
        foreach($users_data as $user_data){

            if(!empty($user_data['imsi']) && !empty($user_data['profile'])){
        
                $kiw_acc = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$user_data['imsi']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");
        
                if($kiw_acc){
        
                    if($kiw_acc['status'] == 'suspend' || $kiw_acc['status'] == 'expired'){
        
                        if($kiw_acc['profile_subs'] == $user_data['profile']){
        
                            $kiw_data['status'] = 'active';
                            $kiw_data['date_activate'] = date('Y-m-d H:i:s');
        
                            if(isset($user_data['email']) && $user_data['email'] != '') $kiw_data['email_address']   = $user_data['email'];
                            if(isset($user_data['phone']) && $user_data['phone'] != '') $kiw_data['phone_number']    = $user_data['phone'];
                            if(isset($user_data['other']) && $user_data['other'] != '') $kiw_data['remark']          = $user_data['other'];
                            
                            if($kiw_acc['status'] == 'expired')  $kiw_data['date_expiry'] =  date('Y-m-d H:i:s',strtotime($kiw_acc['date_expiry'] . " + 365 day"));
        
                            //UPDATE
                            $kiw_db->update("kiwire_account_auth", $kiw_data, "username = '{$user_data['imsi']}' AND tenant_id = '{$kiw_request['tenant']}'");
                            
                            unset($kiw_acc);
                            
                            if ($kiw_db->db_affected_row > 0) {
        
                                $kiw_acc = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$user_data['imsi']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");
        
                                $kiw_return[] = array(
                                    'IMSI'          => $kiw_acc['username'],                    
                                    'email'         => $kiw_acc['email_address'],                    
                                    'phone'         => $kiw_acc['phone_number'],                    
                                    'profile'       => $kiw_acc['profile_subs'],                    
                                    'date_activate' => $kiw_acc['date_activate'],                    
                                    'other'         => $kiw_acc['remark'],   
                                    'api_remark'    => 'success'                 
                                );
                                
                                logger_api($kiw_request['tenant'], "[ LTE-CRM ACTIVATE ]  {$kiw_request['api_key']} activated user {$user_data['imsi']}");            
                    
                            } 
                            else{

                                $kiw_return[] = array(
                                    'IMSI'          => $user_data['imsi'],                    
                                    'api_remark'    => 'Database Error (something went wrong on our end)'                  
                                );
                            }
                        }
                        else{
                            
                            $kiw_return[] = array(
                                'IMSI'          => $user_data['imsi'],                    
                                'api_remark'    => 'Profile not found'                  
                            );
                        } 
                    }
                    else if($kiw_acc['status'] == 'active'){
                        
                        $kiw_return[] = array(
                            'IMSI'          => $user_data['imsi'],                    
                            'api_remark'    => 'Account already active'                  
                        );
                    }  
                } 
                else {

                    $kiw_return[] = array(
                        'IMSI'          => $user_data['imsi'],                    
                        'api_remark'    => 'No account found'                  
                    );
                } 
            } 
            else{
                
                $kiw_return[] = array(
                    'IMSI'          => $user_data['imsi'],                    
                    'api_remark'    => 'Parameter not recognized'                  
                );
            } 
        }
    }
    else{
        echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "Invalid POST data")));
        die();
    }

    echo json_encode(array("data" => $kiw_return, "status" => array("type" => "success", "code" => "200", "message" => "")));

}

function user_batch_suspension($kiw_request){

    global $kiw_db;

    $users_data = file_get_contents("php://input");

    $users_data = json_decode($users_data, true);

    if(empty($kiw_request['tenant'])){
        echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "Tenant is required")));
        die();
    }

    if(is_array($users_data) && sizeof($users_data) > 0){

        foreach($users_data as $user_data){

            if(!empty($user_data['imsi'])){
                
                $kiw_acc = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$user_data['imsi']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");
                
                if($kiw_acc){
        
                    if($kiw_acc['status'] == 'active'){
        
                        $kiw_data['status'] = 'suspend';
        
                        if(isset($user_data['other']) && $user_data['other'] != '') $kiw_data['remark'] = $user_data['other'];
        
                        //UPDATE
                        $kiw_db->update("kiwire_account_auth", $kiw_data, "username = '{$user_data["imsi"]}' AND tenant_id = '{$kiw_request['tenant']}'");
                        
                        unset($kiw_acc);
                        
                        if ($kiw_db->db_affected_row > 0) {
        
                            $kiw_acc = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$user_data["imsi"]}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");
        
                            $kiw_return[] = array(
                                'IMSI'          => $kiw_acc['username'],                    
                                'email'         => $kiw_acc['email_address'],                    
                                'phone'         => $kiw_acc['phone_number'],                    
                                'profile'       => $kiw_acc['profile_subs'],                    
                                'other'         => $kiw_acc['remark'],
                                'api_remark'    => 'success'          
                            );
                
                            logger_api($kiw_request['tenant'], "[ LTE-CRM SUSPEND ]  {$kiw_request['api_key']} suspended user {$user_data["imsi"]}");
                        } 
                        else{
                            $kiw_return[] = array(
                                'IMSI'          => $user_data["imsi"],                    
                                'api_remark'    => 'Database Error (something went wrong on our end)'                  
                            );
                        }  
                    }
                    else if($kiw_acc['status'] == 'suspend' || $kiw_acc['status'] == 'expired'){
                        $kiw_return[] = array(
                            'IMSI'          => $user_data["imsi"],                    
                            'api_remark'    => 'Account is non-active'                  
                        );
                    }  
                } 
                else{
                    $kiw_return[] = array(
                        'IMSI'          => $user_data["imsi"],                    
                        'api_remark'    => 'No account found'                  
                    );
                } 
            } 
            else{
                $kiw_return[] = array(
                    'IMSI'          => $user_data["imsi"],                    
                    'api_remark'    => 'Empty IMSI'                  
                );
            } 
        }
    }
    else{
        echo json_encode(array("data" => "", "status" => array("type" => "failed", "code" => "999", "message" => "Invalid POST data")));
        die();

    }
    
    echo json_encode(array("data" => $kiw_return, "status" => array("type" => "success", "code" => "200", "message" => "")));

}