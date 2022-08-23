<?php
/**
 * 
 * !!!! ALERT !!!!
 * 
 * STANDARDIZE NAMING FILE MIGRATION TO YYYY_MM_DD_{running_number}_filename.php 
 * 
 * (FILENAME CANNOT BE SAME -> PURPOSE TO STORE AT DATABASE)
 * 
 * EXAMPLE 2021_12_02_001_create_users_table.php 
 * 
 * NO NEED PHP OPEN TAG IN FILE eg: <?php
 * 
 * FILE FORMAT CAN BE .sql OR .php
 * 
 */


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";

$kiw_db = new mysqli(SYNC_DB1_HOST,SYNC_DB1_USER,SYNC_DB1_PASSWORD,SYNC_DB1_DATABASE, SYNC_DB1_PORT);

if ($kiw_db->connect_errno){

    die("Kiwire migration: failed to connect to database");

}

//check if migrations table exist in database
$get_table = $kiw_db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'kiwire' AND table_name = 'migrations'");

$get_table = $get_table->fetch_all(MYSQLI_ASSOC);

//create table migrations if not exist
if(!$get_table){
    
    $kiw_db->query("CREATE TABLE `migrations` (
        `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `migration_file` varchar(255) DEFAULT NULL,
        `status` char(1) DEFAULT NULL,
        `updated_date` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
      )");

}

unset($get_table);

//get all migration file from folder migrations
$files = scandir("/var/www/kiwire/system/migrations");

//insert all file name into migration table
foreach($files as $file){

    if (in_array($file, array(".", "..","index.php"))) continue;

    //check file exist in migration. file should be unique name
    $check = $kiw_db->query("SELECT * FROM migrations WHERE migration_file = '{$file}' LIMIT 1 ")->num_rows;
    
    if($check == 0){
        //insert file with status n = not execute file yet
        $kiw_db->query("INSERT INTO migrations (migration_file, status, updated_date) VALUES ('{$file}', 'n', NOW())");
        
    }
    
}

unset($files);
unset($check);


//check migration file not running yet
$files = $kiw_db->query("SELECT migration_file FROM migrations WHERE status = 'n'");
$files = $files->fetch_all(MYSQLI_ASSOC);

if($files){

    //populate all query 
    foreach($files as $file){

        echo "Running migration {$file['migration_file']} \n";

        $sql = file_get_contents("/var/www/kiwire/system/migrations/" . $file['migration_file']);

        if ($kiw_db->multi_query($sql)) {
            sleep(1);
            do {

                $kiw_db->query("UPDATE migrations SET status='y', updated_date=NOW() WHERE migration_file = '{$file['migration_file']}'");

                //Prepare next result set
            } while ($kiw_db->next_result());


            echo "File {$file['migration_file']} migrated OK \n\n";
        }
        sleep(1);
    }

}
unset($sql);
unset($files);
