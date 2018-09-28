<?php
error_reporting('E_NONE');
$ip = "";
$json = [];

if (isset($_GET['ip']) && !empty($_GET['ip'])) {
    $ip = $_GET['ip'];
    $port = 25565;
    $version = "1.8";

    if (strpos($ip, ':') !== false) {
        $contend = explode(':', $ip);
        $ip = $contend[0];
        $port = $contend[1];
    }

    if (isset($_GET['port']) && !empty($_GET['port'])) {
        $port = $_GET['port'];
    }
    if (isset($_GET['version']) && !empty($_GET['version'])) {
        $version = $_GET['version'];
    }
    include_once('./status.class.forjson.php');
    $status = new MinecraftServerStatus();
    $response = $status->getStatus($ip, $port, $version);
    if (!$response) {
        array_merge($json, [
            'ip' => $ip.':'.$port,
            'error' => true,
            'error_msg' => 'Could not connect to server!',
        ]);
    } else {
        array_merge($json, [
            'ip' => $ip.':'.$port,
            'hostname' => $response['hostname'],
            'verson' => $response['version'],
            'protocol' => $response['protocol'],
            'players' => $response['players'],
            'playerlist' => $response['playerlist'],
            'maxplayers' => $response['maxplayers'],
            'motd' => $response['motd'],
            'motd_raw' => $response['motd_raw'],
            'img' => !empty($_GET['show_img']) && isset($_GET['show_img']) && ($_GET['show_img'] == "true") ? $response['favicon'] : "IMAGE HIDDEN",
            'ping' => $response['ping'],
            'error' => false,
            'error_msg' => '',
        ]);
    }
} else {
    array_merge($json, [
        'error' => true,
        'error_msg' => 'no ip was set, please use \'?ip=SERVER_IP\' at the end of the url',
    ]);
}
$jsonstring = json_encode($json);
echo $jsonstring;
die();
