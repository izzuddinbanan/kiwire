<?
ini_set("display_errors", 1);
error_reporting(E_ALL);





//Login
function GenSOAPlogin($username,$password){
$request="
<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\">
<soapenv:Body>
<LGI><OPNAME>$username</OPNAME><PWD>$password</PWD></LGI>
</soapenv:Body>
</soapenv:Envelope>
";
return $request;
}

//logout
function GenSOAPLogout(){
   $request="
   <soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:lgo=\"http://www.huawei.com/HLR9820/LGO\">
      <soapenv:Header/>
      <soapenv:Body>
         <lgo:LGO>
         </lgo:LGO>
      </soapenv:Body>
   </soapenv:Envelope>
   ";
   return $request;
}
   

//List KI
function GenSoapLISTKI($imsi){
$request="
<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:lst=\"http://www.huawei.com/HLR9820/LST_KI\">
   <soapenv:Header/>
   <soapenv:Body>
	<lst:LST_KI><lst:IMSI>$imsi</lst:IMSI></lst:LST_KI>
   </soapenv:Body>
</soapenv:Envelope>
";
return $request;
}

//Remove KI
function GenSoapRMVKI($imsi){
$request="
<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:rmv=\"http://www.huawei.com/HLR9820/RMV_KI\">
   <soapenv:Header/>
   <soapenv:Body>
      <rmv:RMV_KI><rmv:IMSI>$imsi</rmv:IMSI></rmv:RMV_KI>
   </soapenv:Body>
</soapenv:Envelope>
";
return $request;
}

//Add KI
function GenSoapADDKI($hlrsn,$imsi,$opertype,$ki,$cardtype,$alg,$opsno,$keytype){
$request="
<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:add=\"http://www.huawei.com/HLR9820/ADD_KI\">
   <soapenv:Header/>
   <soapenv:Body>
      <add:ADD_KI>
         <add:HLRSN>$hlrsn</add:HLRSN>
         <add:IMSI>$imsi</add:IMSI>
         <add:OPERTYPE>$opertype</add:OPERTYPE>
         <add:KIVALUE>$ki</add:KIVALUE>
         <add:CARDTYPE>$cardtype</add:CARDTYPE>
         <add:ALG>$alg</add:ALG>
         <add:OPSNO>$opsno</add:OPSNO>
         <add:KEYTYPE>$keytype</add:KEYTYPE>
      </add:ADD_KI>
   </soapenv:Body>
</soapenv:Envelope>
";
return $request;
/*
 	 <add:K4SNO>0</add:K4SNO>
	 <add:AMFSNO>1</add:AMFSNO>
	 <add:K2SNO>0</add:K2SNO>
*/
}

//Add TPL SUB
function GenSoapADDTPLSUB($hlrsn,$imsi,$isdn,$tpltype,$tplid){
$request="
<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:add=\"http://www.huawei.com/HLR9820/ADD_TPLSUB\">
   <soapenv:Header/>
   <soapenv:Body>
      <add:ADD_TPLSUB>
         <add:HLRSN>$hlrsn</add:HLRSN>
         <add:IMSI>$imsi</add:IMSI>
         <add:ISDN>$isdn</add:ISDN>
         <add:TPLTYPE>$tpltype</add:TPLTYPE>
         <add:TPLID>$tplid</add:TPLID>
      </add:ADD_TPLSUB>
   </soapenv:Body>
</soapenv:Envelope>
";
return $request;
/* opt
         <add:CSPSTPLID>?</add:CSPSTPLID>
         <add:EPSUSERTPLID>?</add:EPSUSERTPLID>
         <add:LINE2NUM>?</add:LINE2NUM>
         <add:UCSITE>?</add:UCSITE>
         <add:STNSR>?</add:STNSR>
         <add:M2MCISDN>?</add:M2MCISDN>
         <add:PSUSERTPLID>?</add:PSUSERTPLID>
         <add:NGSUSERTPLID>?</add:NGSUSERTPLID>
*/
}

function GenSoapLSTSUB($imsi,$isdn){
$imsi_tag = $isdn_tag = "";
if ($imsi !=""){ $imsi_tag = "<lst:IMSI>$imsi</lst:IMSI>";}
if ($isdn !=""){ $isdn_tag = "<lst:ISDN>$isdn</lst:ISDN>";}
$request="
<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:lst=\"http://www.huawei.com/HLR9820/LST_SUB\">
   <soapenv:Header/>
   <soapenv:Body>
      <lst:LST_SUB>
	$imsi_tag
	$isdn_tag
      </lst:LST_SUB>
   </soapenv:Body>
</soapenv:Envelope>
";
return $request;
}


function GenSoapRMVSUB($imsi,$isdn){
$imsi_tag = $isdn_tag = "";
if ($imsi !=""){ $imsi_tag = "<rmv:IMSI>$imsi</rmv:IMSI>";}
if ($isdn !=""){ $isdn_tag = "<rmv:ISDN>$isdn</rmv:ISDN>";}

$request="
<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:rmv=\"http://www.huawei.com/HLR9820/RMV_SUB\">
   <soapenv:Header/>
   <soapenv:Body>
      <rmv:RMV_SUB>
	$imsi_tag
	$isdn_tag
      </rmv:RMV_SUB>
   </soapenv:Body>
</soapenv:Envelope>
";
return $request;
/*
         <rmv:RMVKI>TRUE</rmv:RMVKI>
*/
}


function GenSoapMODlock($isdn,$imsi,$lock){
if ($imsi !=""){ $imsi_tag = "<mod:IMSI>$imsi</mod:IMSI>";}
if ($isdn !=""){ $isdn_tag = " <mod:ISDN>$isdn</mod:ISDN>";}

$request="
<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:mod=\"http://www.huawei.com/HLR9820/MOD_LCK\">
   <soapenv:Header/>
   <soapenv:Body>
      <mod:MOD_LCK>
        $imsi_tag
        $isdn_tag
         <mod:IC>$lock</mod:IC>
         <mod:OC>$lock</mod:OC>
         <mod:GPRSLOCK>$lock</mod:GPRSLOCK>
         <mod:EPSLOCK>$lock</mod:EPSLOCK>
      </mod:MOD_LCK>
   </soapenv:Body>
</soapenv:Envelope>
";
return $request;
/*
         <mod:CSUPLLCK>?</mod:CSUPLLCK>
         <mod:PSUPLLCK>?</mod:PSUPLLCK>
         <mod:NON3GPPLOCK>?</mod:NON3GPPLOCK>
         <mod:NGSLOCK>?</mod:NGSLOCK>
*/
}

function GenSoapMODOPTGPRS($imsi){
$request="
<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:mod=\"http://www.huawei.com/HLR9820/MOD_OPTGPRS\">
   <soapenv:Header/>
   <soapenv:Body>
      <mod:MOD_OPTGPRS>
         <mod:IMSI>$imsi</mod:IMSI>
         <mod:PROV>ADDPDPCNTX</mod:PROV>
         <mod:APN_TYPE>EPS_APN</mod:APN_TYPE>
         <mod:APNTPLID>1</mod:APNTPLID>
         <mod:PDPTYPE>IPV4</mod:PDPTYPE>
         <mod:ADDIND>DYNAMIC</mod:ADDIND>
         <mod:VPLMN>TRUE</mod:VPLMN>
         <mod:CHARGE>NORMAL</mod:CHARGE>
         <mod:EPS_QOSTPLID>1</mod:EPS_QOSTPLID>

      </mod:MOD_OPTGPRS>
   </soapenv:Body>
</soapenv:Envelope>
";
return $request;
/*
         <mod:QOSTPLID>1</mod:QOSTPLID>
         <mod:ISDN>$isdn</mod:ISDN>
         <mod:PRIORITY_LEVEL>?</mod:PRIORITY_LEVEL>
         <mod:CNTXID>?</mod:CNTXID>
         <mod:PDPADD>?</mod:PDPADD>
         <mod:CHARGE_GLOBAL>?</mod:CHARGE_GLOBAL>
         <mod:APN>?</mod:APN>
         <mod:DEFAULTCFGFLAG>?</mod:DEFAULTCFGFLAG>
         <mod:PDPADDIPV4>?</mod:PDPADDIPV4>
         <mod:ADD2IND>?</mod:ADD2IND>
         <mod:STDCHARGE>?</mod:STDCHARGE>
         <mod:SCHARGE_GLOBAL>?</mod:SCHARGE_GLOBAL>
         <mod:PROVAPNOI>?</mod:PROVAPNOI>
         <mod:APNOITPLID>?</mod:APNOITPLID>
         <mod:NONIPAPNFLAG>?</mod:NONIPAPNFLAG>
         <mod:IW5GSINDICATOR>?</mod:IW5GSINDICATOR>
*/
}


function hss_logger($tenant_id, $message = ""){


   if (file_exists(dirname(__FILE__, 4) . "/logs/{$tenant_id}/") == false) mkdir(dirname(__FILE__, 4) . "/logs/{$tenant_id}/", 0755, true);
   file_put_contents(dirname(__FILE__, 4) . "/logs/{$tenant_id}/hss-" . date("Ymd") . ".log", date("Y-m-d H:i:s :: ") .  $message . "\n", FILE_APPEND);

}



