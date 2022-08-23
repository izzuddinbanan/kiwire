<?php
 
$kiw['module'] = "Report -> Survey -> Response Data";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_report.php";
require_once "../includes/include_general.php";

require_once "../includes/../../libs/ssp.class.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$kiw_action = $_REQUEST['action'];

$kiw_survey_id = $kiw_db->escape($_REQUEST['survey_id']);


$kiw_timezone = $_SESSION['timezone'];

$kiw_timezone = empty($kiw_timezone) ?: "Asia/Kuala_Lumpur";


$kiw_date_start = report_date_start($_REQUEST['start_date'], 30);

$kiw_date_end = report_date_end($_REQUEST['end_date'], 1);


$kiw_survey_id = $kiw_db->query_first("SELECT * FROM kiwire_survey_list WHERE id = '{$kiw_survey_id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


// If the request is for datatable, run this if statement
if ($kiw_action == "datatable") {




    if (!empty($kiw_survey_id)) {



        $kiw_columns = array(
            array('db' => 'updated_date', 'dt' => 1),
            array('db' => 'username', 'dt' => 2),
            array('db' => 'mac_address', 'dt' => 3),
            array('db' => 'answer', 'dt' => 4)

        );


        $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db' => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);


        $kiw_data = SSP::complex($_GET, $kiw_sqlinfo, "kiwire_survey_respond", "id", $kiw_columns, null, "unique_id = '{$kiw_survey_id['unique_id']}' AND (updated_date BETWEEN '{$kiw_date_start}' AND '{$kiw_date_end}') AND tenant_id = '{$_SESSION['tenant_id']}'");


        $kiw_start = $_GET['start'] + 1;

        $kiw_end = count($kiw_data['data']) + $kiw_start;


        for ($x = $kiw_start; $x < $kiw_end; $x++) {

            // Number column
            $kiw_data['data'][$x - $kiw_start][0] = $x;

            // Date column
            $kiw_data['data'][$x - $kiw_start][1] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][1], $kiw_timezone);

            // Username Column
            $kiw_data['data'][$x - $kiw_start][2] = empty($kiw_data['data'][$x - $kiw_start][2]) ? "N/A" : $kiw_data['data'][$x - $kiw_start][2];

            // Answers column
            $kiw_current_rows = json_decode(stripslashes($kiw_data['data'][$x - $kiw_start][4]), true);


            $kiw_counter = 0;

            foreach ($kiw_current_rows as $kiw_current_row) {

                
                $kiw_data['data'][$x - $kiw_start][4 + $kiw_counter] = $kiw_current_row;

                $kiw_counter++;


            }


        }

        echo json_encode($kiw_data);


    }


} 
// Else if the request is for download_all, run this if statement
elseif ($kiw_action == "download_all"){

    // Fetch the data from the server where tenant_id is current logged on tenant
    $kiw_datas = $kiw_db->fetch_array("SELECT * FROM kiwire_survey_respond WHERE tenant_id = '{$_SESSION['tenant_id']}' AND unique_id = '{$kiw_survey_id['unique_id']}'");

    // Store the name that the file will be saved as. Add first 4 characters of the hashed time value at the end to make sure each downloaded files have unique names.
    $kiw_filename = "survey_{$kiw_survey_id['tenant_id']}_{$kiw_survey_id['unique_id']}_" . date("Ymd", strtotime($kiw_date_start)) . "_" . date("Ymd", strtotime($kiw_date_start)) . "_" . substr(md5(time()), 0, 4) . ".csv";

    $kiw_counter = 1;


    $kiw_question_count = base64_decode($kiw_survey_id['questions']);
    // decode the json
    $kiw_question_count = json_decode($kiw_question_count, true);
    // count number of questions
    $kiw_question_count = count($kiw_question_count);

    // set first row to be the titles. Hardcoded. Separated by the ","
    $kiw_current_row .= "No, Date / Time, Username, MAC Address,";

    // The titles for the actual questions. Renamed to "Question 1", "Question 2", and so on to make it easier to code.
    for($kiw_x = 1; $kiw_x <= $kiw_question_count; $kiw_x++){

        $kiw_current_row .= "Question {$kiw_x},";

    }

    // remove the ","
    $kiw_current_row = trim($kiw_current_row, ",");
    // Actually adding the contents into the file
    file_put_contents(dirname(__FILE__, 3) . "/temp/{$kiw_filename}", $kiw_current_row . "\n", FILE_APPEND);

    // Reset current row value
    $kiw_current_row = "";

    // Go through each data in the table to store each row's values
    foreach ($kiw_datas as $kiw_data){

        // store the current row's counter value followed by ","
        $kiw_current_row .= $kiw_counter . ",";
        
        // store the current row's updated_date value followed by ","
        $kiw_current_row .= $kiw_data['updated_date'] . ",";

        // store the current row's username value followed by ","
        $kiw_current_row .= $kiw_data['username'] . ",";

        // store the current row's mac_address value followed by ","
        $kiw_current_row .= $kiw_data['mac_address'] . ",";

        // Answer value is stored in json, so need to remove the "/", and decode the json data
        $kiw_json_arrays = json_decode(stripslashes($kiw_data['answer']), true);

        // go through each rows in the json array
        foreach ($kiw_json_arrays as $kiw_json_array){
            // check to make sure it's an array
            if (is_array($kiw_json_array)){
                // joins each of the array elements and separating each rows with "|"
                $kiw_current_row .= implode("|", $kiw_json_array);

            } 
            // else if only 1 row (not an array), just add it into current row
            else 
            {
                $kiw_current_row .= $kiw_json_array . ",";
            }

        }



        // add each rows into the file, followed by a line break
        file_put_contents(dirname(__FILE__, 3) . "/temp/{$kiw_filename}", $kiw_current_row . "\n", FILE_APPEND);

        // reset current row value
        $kiw_current_row = "";

        // counter++
        $kiw_counter++;


    }

    // store the path in temp variable
    $kiw_temp_path = dirname(__FILE__, 3);

    // run this to tell system to zip the specified file
    system("zip -qJj {$kiw_temp_path}/temp/{$kiw_filename}.zip {$kiw_temp_path}/temp/{$kiw_filename}");

    system("rm -rf {$kiw_temp_path}/temp/{$kiw_filename}");

    // output json message with status = success, data = filename.zip
    echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_filename . ".zip"));


}
