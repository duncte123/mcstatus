<?php

class MinecraftServerStatus
{
    private $timeout;

    /**
     * MinecraftServerStatus constructor.
     *
     * @param int $timeout
     */
    public function __construct(int $timeout = 2)
    {
        $this->timeout = $timeout;
    }

    /**
     * @param string $host
     * @param int    $port
     * @param string $version
     *
     * @return array|bool The array of data or false on failure
     */
    public function getStatus(string $host = '127.0.0.1', int $port = 25565, string $version = '1.8.*')
    {
        if (substr_count($host, '.') != 4) $host = gethostbyname($host);

        $serverData = [];
        $serverData['hostname'] = $host;
        $serverData['hostnameRaw'] = $host;
        $serverData['version'] = false;
        $serverData['protocol'] = false;
        $serverData['players'] = false;
        $serverData['playerlist'] = false;
        $serverData['maxplayers'] = false;
        $serverData['motd'] = false;
        $serverData['motd_raw'] = false;
        $serverData['favicon'] = null;
        $serverData['ping'] = false;

        $socket = $this->connect($host, $port);

        if (!$socket) {
            return false;
        }

        // Get the microtime for the ping
        $start = microtime(true);

        if (preg_match('/1.7|1.8|1.9|1.10|1.11|1.12|1.13/', $version)) {
            $handshake = pack('cccca*', hexdec(strlen($host)),
                    0, 0x04, strlen($host), $host).pack('nc', $port, 0x01);

            socket_send($socket, $handshake, strlen($handshake), 0); //give the server a high five
            socket_send($socket, "\x01\x00", 2, 0);
            socket_read($socket, 1);
            $ping = round((microtime(true) - $start) * 1000); //calculate the high five duration
            $packetlength = $this->read_packet_length($socket);

            if ($packetlength < 0) {
                return false;
            }

            socket_read($socket, 1);
            $packetlength = $this->read_packet_length($socket);
            $data = socket_read($socket, $packetlength, PHP_NORMAL_READ);

            if (!$data) {
                return false;
            }

            $data = json_decode($data);
            $serverData['version'] = $data->version->name;
            $serverData['protocol'] = $data->version->protocol;
            $serverData['players'] = $data->players->online;
            $serverData['data'] = $data;
            $serverData['maxplayers'] = $data->players->max;

            $onlinePlayers = [];

            foreach ($data->players->sample as $player) {

                if (empty($player)) continue;

                array_push($onlinePlayers, $this->formatPlayer($player->name, $player->id));
            }

            if (empty($data->players->sample) && $serverData['players'] != 0) {
                $onlinePlayers = false;
            }

            $serverData['playerlist'] = $onlinePlayers;

            $motd = $data->description->text ?? $data->description;

            $serverData['motd'] = $this->cleanMotd($motd);
            $serverData['motd_raw'] = $motd;

            if (!empty($data->favicon)) {
                $serverData['favicon'] = $data->favicon;
            }

            $serverData['ping'] = $ping;
        } else {

            socket_send($socket, "\xFE\x01", 2, 0);

            $length = socket_recv($socket, $data, 512, 0);
            $ping = round((microtime(true) - $start) * 1000);//calculate the high five duration

            if ($length < 4 || $data[0] != "\xFF") {
                return false;
            }
            $motd = "";

            //Evaluate the received data
            if (substr((String) $data, 3, 5) == "\x00\xa7\x00\x31\x00") {
                $result = explode("\x00", mb_convert_encoding(
                    substr((String) $data, 15), 'UTF-8', 'UCS-2'));
                $motd = $result[1];
            } else {
                $result = explode('§', mb_convert_encoding(
                    substr((String) $data, 3), 'UTF-8', 'UCS-2'));

                foreach ($result as $key => $string) {
                    if ($key != sizeof($result) - 1 && $key != sizeof($result) - 2 && $key != 0) {
                        $motd .= '§'.$string;
                    }
                }
            }

            $serverData['version'] = $result[0];
            $serverData['players'] = $result[sizeof($result) - 2];
            $serverData['maxplayers'] = $result[sizeof($result) - 1];
            $serverData['motd'] = $this->cleanMotd($motd);;
            $serverData['motd_raw'] = $motd;
            $serverData['ping'] = $ping;
        }
        $this->disconnect($socket);

        return $serverData;
    }

    private function connect(string $host, int $port)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!socket_connect($socket, $host, $port)) {
            $this->disconnect($socket);

            return false;
        }

        return $socket;
    }

    private function disconnect($socket): void
    {
        if ($socket != null) {
            socket_close($socket);
        }
    }

    private function read_packet_length($socket)
    {
        $a = 0;
        $b = 0;
        while (true) {
            $c = socket_read($socket, 1);
            if (!$c) {
                return 0;
            }
            $c = Ord($c);
            $a |= ($c & 0x7F) << $b++ * 7;
            if ($b > 5) {
                return false;
            }
            if (($c & 0x80) != 128) {
                break;
            }
        }

        return $a;
    }

    private function formatPlayer($player, $uuid): array
    {
        return [
            'name' => $player,
            'uuid' => $uuid,
        ];
    }

    private function cleanMotd($motd): string
    {
        // Remove §
        $motd = preg_replace("/(§.)/", "", $motd);

        //Remove all special characters from a string
        return preg_replace("/[^[:alnum:][:punct:] ]/", "", $motd);
    }
}
