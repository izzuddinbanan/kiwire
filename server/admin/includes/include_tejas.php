<?
ini_set("display_errors", 1);
error_reporting(E_ALL);


function imsi_add(){

   return "   
   <imsi>
      <imsi>987654321123458</imsi>
      <msisdn>987654321123458</msisdn>
      <profile-id>1</profile-id>
      <imsi-odb>8</imsi-odb>
      <imsi-status>GSP-STATUS-GRANTED</imsi-status>
      <eps-service>GSP-SERVICE-ENABLE</eps-service>
      <lte-auth-algo>GSP-LTE-AUTH-AKA</lte-auth-algo>
      <traceDepth>GSP-HSS-NO-TRACE</traceDepth>
      <subs-name>telecom_123</subs-name>
   </imsi>
   ";

}


function imsi_apn_add(){

   return "
   <imsi-apn>
      <imsi>987654321123458</imsi>
      <apn-id>1</apn-id>
      <max-ul>123</max-ul>
      <max-dl>123</max-dl>
      <charging-chars>12</charging-chars>
      <qci>GSP-HSS-QCI-9</qci>
      <arp>5</arp>
      <served-ip>10.9.8.7</served-ip>
      <served-ipv6>::1</served-ipv6>
      <pdn-type>GSP-HSS-IPv4</pdn-type>
      <pgw-address>1.2.3.4</pgw-address>
      <pgw-addressV6>9876::1234</pgw-addressV6>
      <pgw-identity-host>pgw_host123</pgw-identity-host>
      <pgw-identity-realm>pgw_realm123</pgw-identity-realm>
   </imsi-apn>

   ";
}

function imsi_auth_add(){

   return "
   <imsi-auth>
      <imsi>987654321123458</imsi>
      <op>12345678901234567890123456789012</op>
      <amf>12345678901234567890123456789012</amf>
      <k>12345678901234567890123456789012</k>
   </imsi-auth>
   ";
}


function profile_add(){

   return "<profile>
   <profile-id>3</profile-id>
      <dflt-apn-id>1</dflt-apn-id>
      <ntw-access-mode>GSP-HSS-PACKET-AND-CIRCUIT</ntw-access-mode>
      <hss-plmn>
      <mcc>123</mcc>
      <mnc>123</mnc>
      </hss-plmn>
      <hss-plmn>
      <mcc>111</mcc>
      <mnc>90</mnc>
      </hss-plmn>
      <profile-max-ul>123000</profile-max-ul>
      <profile-max-dl>123000</profile-max-dl>
      <profile-name>prof1</profile-name>
      <zonal-code>mcc103.mnc011.gprs</zonal-code>
      <apn-oi-rplcmnt>aricent.com</apn-oi-rplcmnt>
      <subs-periodic-rau-tau-timer>8000</subs-periodic-rau-tau-timer>
   </profile>
   ";

}



function hss_logger($tenant_id, $message = ""){


   if (file_exists(dirname(__FILE__, 4) . "/logs/{$tenant_id}/") == false) mkdir(dirname(__FILE__, 4) . "/logs/{$tenant_id}/", 0755, true);
   file_put_contents(dirname(__FILE__, 4) . "/logs/{$tenant_id}/hss-" . date("Ymd") . ".log", date("Y-m-d H:i:s :: ") .  $message . "\n", FILE_APPEND);

}



