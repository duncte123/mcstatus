<?php
$output = "";
$ip = "";
if(isset($_GET['ip']) && !empty($_GET['ip'])){
    $ip = $_GET['ip'];
    $port = 25565;
    $version = "1.8";

    if (strpos($ip, ':') !== false) {
        $contend = explode(':', $ip);
        $ip = $contend[0];
        $port = $contend[1];
    }

    if(isset($_GET['port']) && !empty($_GET['port'])){ $port = $_GET['port']; }
    if(isset($_GET['version']) && !empty($_GET['version'])){ $version = $_GET['version']; }
    include_once ('./status.class.php');
    $status = new MinecraftServerStatus();
    $response = $status->getStatus($ip, $port, $version);
    if(!$response) {
        $output .= "The Server is offline! ({$ip})";
        $output .= '<meta http-equiv="refresh" content="10">';
    } else {
        $output .= "<img width=\"64\" height=\"64\" src=\"{$response['favicon']}\" /> <br />";
        $output .= "The Server <strong>{$ip}</strong> is running on <strong>{$response['version']}</strong> and is <strong>online</strong> <br />";
        $output .= "currently are <strong>{$response['players']}</strong> players online of a maximum of <strong>{$response['maxplayers']}</strong><br />";
        $output .= "The motd of the server is '<strong>{$response['motd']}</strong>' <br />";
        $output .= "The server has a ping of <strong>{$response['ping']}</strong> milliseconds <br />";
        $output .= "<br />";
        $output .= "<br />";
        $output .= $response['playerlist'];
        $output .= '<meta http-equiv="refresh" content="10">';
    }
}else{
    $output .= "no ip was set <br />";
    $output .= "please use '?ip=&lt;serverip&gt;' at the end of the url<br />";
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $ip; ?></title>
        <style>
        body{
            color: white;
            background: black;
        }
        </style>
    </head>
    <body bgcolor="aqua">
        <?php echo $output; ?>
    </body>
</html>
