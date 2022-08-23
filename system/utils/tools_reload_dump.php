<?php

// check that date need to be provided

if ($argc < 2) die("Please provide all required variable");


// change the date to valid format

$kiw_date = date("Ymd", strtotime($argv[1]));


// get the backup path

$kiw_path = dirname(__FILE__, 1) . "/backups";


// check if there back up data for this date actually existed

if (file_exists("{$kiw_path}/{$kiw_date}/") == false) die("There is no backup for this date: {$kiw_date}");


// scan the directory for all available backup

$kiw_dump_list = scandir("{$kiw_path}/{$kiw_date}/");


echo date("Y-m-d H:i:s") . " :: Start reload data for date: {$kiw_date}\n";


foreach ($kiw_dump_list as $kiw_dump){


    if (substr($kiw_dump, 0, 1) != ".") {


        echo "Load data for table: {$kiw_dump}\n";

        $kiw_fn = $kiw_dump;


        // gunzip the backup file first

        system("gunzip {$kiw_path}/{$kiw_date}/{$kiw_fn}");


        // remove the gzip extension

        $kiw_fn = str_replace(".gz", "", $kiw_fn);

        system("mysql kiwire < {$kiw_path}/{$kiw_date}/{$kiw_fn}");


        // keep the raw data in gzip for space

        system("gzip {$kiw_path}/{$kiw_date}/{$kiw_fn}");


    }


}


sleep(1);

echo "\n\n";
echo date("Y-m-d H:i:s") . " :: Completed\n";
echo "\n";
