<?php

if (!isset($_GET['ip']) || empty($_GET['ip'])) {

    $output = "no ip was set <br />";
    $output .= "please use '?ip=&lt;serverip&gt;' at the end of the url<br />";
    die($output);
}

$ip = $_GET['ip'];
$port = $_GET['port'] ?? 25565;
$version = $_GET['version'] ?? "1.8";

if (strpos($ip, ':') !== false) {
    $contend = explode(':', $ip);
    $ip = $contend[0];
    $port = $contend[1];
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $ip; ?></title>
        <style>
            body {
                color: white;
                background: black;
            }
        </style>
    </head>
    <body>
        <div id="app">Loading.....</div>
        <script src="/app.js"></script>
        <script>
          document.addEventListener("DOMContentLoaded", () => {
            fetchData("<?php echo $ip; ?>", <?php echo $port; ?>, "<?php echo $version; ?>");
          });
        </script>
    </body>
</html>
