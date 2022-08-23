<?php

function sql_update($kiw_db, $kiw_table, $kiw_data, $kiw_where){

    if (method_exists($kiw_db, "escape") == false){

        $kiw_escape_function = "escape_string";

    } else $kiw_escape_function = "escape";


    $query_string = "UPDATE `{$kiw_table}` SET ";

    foreach ($kiw_data as $key => $value) {

        if (strtolower(trim($value)) == "null" || strtolower(trim($value)) == "") {

            $query_string .= "`{$key}` = NULL, ";

        } elseif (strtolower(trim($value)) == "now()"){

            $query_string .= "`{$key}` = NOW(), ";

        } else {

            $query_string .= "`{$key}` = '" . $kiw_db->$kiw_escape_function(trim($value)) . "', ";

        }

    }


    $query_string = substr($query_string, 0,-2);
    $query_string .= " WHERE " . $kiw_where;

    return $query_string;


}


function sql_insert($kiw_db, $kiw_table, $kiw_data){


    if (method_exists($kiw_db, "escape") == false){

        $kiw_escape_function = "escape_string";

    } else $kiw_escape_function = "escape";


    $query_value = "";

    $query_string = "INSERT INTO `{$kiw_table}`(";


    foreach ($kiw_data as $key => $value) {


        $query_string .= "`{$key}`, ";

        if (strtolower(trim($value)) == "null") {

            $query_value .= "NULL, ";

        } elseif (strtolower(trim($value)) == "now()"){

            $query_value .= "NOW(), ";

        } else {

            $query_value .= "'" . $kiw_db->$kiw_escape_function(trim($value)) . "', ";


        }


    }


    $query_string = substr($query_string, 0,-2);
    $query_value = substr($query_value, 0,-2);

    $query_string .= ") VALUE({$query_value})";

    return $query_string;


}
