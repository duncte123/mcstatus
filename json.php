<?php

if (!isset($_GET['ip']) || empty($_GET['ip'])) {

    respond([
        'success' => false,
        'error_msg' => 'no ip was set, please use \'?ip=<SERVER_IP>\' at the end of the url',
    ]);
}

$ip = $_GET['ip'];
$port = $_GET['port'] ?? 25565;
$version = $_GET['version'] ?? "1.8";

if (strpos($ip, ':') !== false) {
    $contend = explode(':', $ip);
    $ip = $contend[0];
    $port = $contend[1];
}

require('./status.class.forjson.php');
$status = new MinecraftServerStatus();
$response = $status->getStatus($ip, $port, $version);

if (!$response) {
    respond([
        'success' => false,
        'ip' => $ip.':'.$port,
        'error_msg' => 'Could not connect to server!',
    ]);
}
respond([
    'success' => true,
    'ip' => $ip.':'.$port,
    'hostname' => $response['hostname'],
    'verson' => $response['version'],
    'protocol' => $response['protocol'],
    'players' => $response['players'],
    'playerlist' => $response['playerlist'],
    'maxplayers' => $response['maxplayers'],
    'motd' => $response['motd'],
    'motd_raw' => $response['motd_raw'],
    'img' => isset($_GET['show_img']) && !empty($_GET['show_img']) && ($_GET['show_img'] == "true")
        ? $response['favicon'] : "IMAGE HIDDEN",
    'ping' => $response['ping'],
]);

function respond(array $json): void
{
    echo json_encode($json);
    die();
}
