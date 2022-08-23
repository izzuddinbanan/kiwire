<?php

session_start();

$session_id = session_id();

$_SESSION['user']['mac']            = htmlspecialchars($_REQUEST['mac'], ENT_QUOTES | ENT_HTML5);
$_SESSION['user']['ip']             = htmlspecialchars($_REQUEST['ip'], ENT_QUOTES | ENT_HTML5);
$_SESSION['user']['ipv6']           = htmlspecialchars($_REQUEST['ipv6'], ENT_QUOTES | ENT_HTML5);
$_SESSION['user']['destination']    = htmlspecialchars(urldecode($_REQUEST['url']), ENT_QUOTES | ENT_HTML5);
$_SESSION['user']['time']           = time();

$_SESSION['controller']['ip']       = htmlspecialchars($_REQUEST['gw_address'], ENT_QUOTES | ENT_HTML5);
$_SESSION['controller']['login']    = htmlspecialchars($_REQUEST['gw_address'] . ":" . $_REQUEST['gw_port'], ENT_QUOTES | ENT_HTML5);
$_SESSION['controller']['id']       = htmlspecialchars($_REQUEST['gw_id'], ENT_QUOTES | ENT_HTML5);
$_SESSION['controller']['vlan']     = htmlspecialchars($_REQUEST['vlanid'], ENT_QUOTES | ENT_HTML5);
$_SESSION['controller']['type']     = "rwifidog";
$_SESSION['controller']['ssid']     = htmlspecialchars($_REQUEST['ssid'], ENT_QUOTES | ENT_HTML5);
$_SESSION['controller']['zone']     = htmlspecialchars($_REQUEST['zone'], ENT_QUOTES | ENT_HTML5);

$_SESSION['response']['error']      = "";


$_SESSION['user']['mac'] = str_replace("-", ":", strtolower($_SESSION['user']['mac']));

?>

<script>

    window.onload = function () {

        var language = window.navigator.language;

        window.location.href = '/user/init/?session=<?= $session_id ?>&lang=' + language;

    }

</script>

