<?php


$kiw_input_stream = fopen("php://stdin", "r");


echo "Vendor for this license [ empty for synchroweb ]: ";

$kiw_client['vendor'] = trim(fread($kiw_input_stream, 1024));

if (empty($kiw_client['vendor'])) $kiw_client['vendor'] = "synchroweb";


while (!in_array($kiw_client['product'], array("kiwire", "omaya"))) {

    echo "Product name [ kiwire or omaya ]: ";

    $kiw_client['product'] = trim(fread($kiw_input_stream, 1024));

}


while (empty($kiw_client['client_name'])) {

    echo "Client name: ";

    $kiw_client['client_name'] = trim(fread($kiw_input_stream, 1024));

}


echo "Number of device limit [1]: ";

$kiw_client['device_limit'] = trim(fread($kiw_input_stream, 1024));

if (empty($kiw_client['device_limit'])) $kiw_client['device_limit'] = 1;


echo "Expiry date of this license [2020-12-31 or 'next year']: ";

$kiw_client['expire_on'] = strtotime(trim(fread($kiw_input_stream, 1024)));

if (empty($kiw_client['expire_on'])) $kiw_client['expire_on'] = strtotime("tomorrow");


echo "This license for multi-tenant [true or false]: ";

$kiw_client['multi-tenant'] = trim(fread($kiw_input_stream, 1024));


if ($kiw_client['multi-tenant'] == "true") $kiw_client['multi-tenant'] = true;
else $kiw_client['multi-tenant'] = false;


if ($kiw_client['product'] == "omaya"){


    while (!in_array($kiw_client['type'], array("wifi", "workspace", "vision"))) {


        echo "Application type [wifi or workspace or vision]: ";

        $kiw_client['type'] = trim(fread($kiw_input_stream, 1024));


    }


    if ($kiw_client['type'] != "vision") {


        echo "Enable triangulation [true or false]: ";

        $kiw_client['triangulation'] = trim(fread($kiw_input_stream, 1024));

        if ($kiw_client['triangulation'] == "true") $kiw_client['triangulation'] = true;
        else $kiw_client['triangulation'] = false;


    }


}


while (empty($kiw_client['your_name'])) {

    echo "Please provide your name [ hantu ]: ";

    $kiw_client['your_name'] = trim(fread($kiw_input_stream, 1024));

}


echo "\n";



// create kiwire license

$kiw_client['generate_on'] = time();


$kiw_license = json_encode($kiw_client);

$kiw_license = openssl_encrypt($kiw_license, "AES-256-CBC", "e1gOtk*9Ox_R", 0, "7vO*STBUdm_7tU4i");

$kiw_license = base64_encode($kiw_license);

echo "Summary: \n\n";

echo json_encode($kiw_client, JSON_PRETTY_PRINT);

echo "\n\n";

echo "\nYeay!! Your license string as per below. Please say thanks to Hakim.\n\n";
echo str_repeat("*", 80) . "\n\n";
echo $kiw_license . "\n\n";
echo str_repeat("*", 80) . "\n";
echo "\n";
echo "Go go go " . ucfirst($kiw_client['product']) . " v3 !!!";
echo "\n\n";