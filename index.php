<?php
    if(isset($_GET['ip']) && !empty($_GET['ip'])){
        include_once ('./status.class.php');
        $status = new MinecraftServerStatus();
        // $response = $status->getStatus('skyblock.online', 25565);
        $response = $status->getStatus(''.$_GET['ip'].'');
        if(!$response) {
            echo"The Server is offline!";
            echo '<meta http-equiv="refresh" content="10">';
        } else {
            echo "<img width=\"64\" height=\"64\" src=\"".$response['favicon']."\" /> <br />";
            echo "The Server <strong>".$response['hostname']."</strong> is running on <strong>".$response['version']."</strong> and is <strong>online</strong> <br />";
            echo "currently are <strong>".$response['players']."</strong> players online of a maximum of <strong>".$response['maxplayers']."</strong><br />";
            echo "The motd of the server is '<strong>".$response['motd']."</strong>' <br />";
            echo "The server has a ping of <strong>".$response['ping']. "</strong> milliseconds <br />";
            echo "<br />";
            echo "<br />";
            echo $response['playerlist'];
            echo '<meta http-equiv="refresh" content="10">';
        }
    }else{
        echo "no ip was set";
        exit();
    }
	
?>