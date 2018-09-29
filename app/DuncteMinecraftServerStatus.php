<?php

namespace DuncteMinecraftServerStatus;

use MinecraftServerStatus\MinecraftServerStatus;
use MinecraftServerStatus\Packets\HandshakePacket;
use MinecraftServerStatus\Packets\PingPacket;

class DuncteMinecraftServerStatus extends MinecraftServerStatus
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
     *
     * @return array|bool The array of data or false on failure
     */
    public function getStatus(string $host = '127.0.0.1', int $port = 25565)
    {

        // check if the host is in ipv4 format
        $host = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!@socket_connect($socket, $host, $port)) {
            return false;
        }

        // create the handshake and ping packet
        $handshakePacket = new HandshakePacket($host, $port, 107, 1);
        $pingPacket = new PingPacket();

        $handshakePacket->send($socket);

        // high five
        $start = microtime(true);
        $pingPacket->send($socket);
        $length = $this->readVarInt($socket);
        $ping = round((microtime(true) - $start) * 1000);

        // read the requested data
        $data = socket_read($socket, $length, PHP_NORMAL_READ);
        $data = strstr($data, '{');
        $data = json_decode($data);

        $descriptionRaw = $data->description ?? false;
        $descriptionParsed = $descriptionRaw;
        $description = $descriptionRaw;

        // colorize the description if it is supported
        if (gettype($descriptionRaw) == 'object') {
            $description = '';

            if (isset($descriptionRaw->text)) {
                $description = [
                    'color' => $descriptionRaw->color ?? '',
                    'text' => $descriptionRaw->text ?? ''
                ];
            }

            if (isset($descriptionRaw->extra)) {
                $description = [];
                $descriptionParsed = '';
                foreach ($descriptionRaw->extra as $item) {
                    $descriptionParsed .= $item->text ?? '';
                    $description[] = [
                        'bold' => $item->bold ?? false,
                        'color' => $item->color ?? '',
                        'text' => $item->text ?? '',
                    ];
                }
            }
        }

        $playerCount = $data->players->online ?? 0;
        $onlinePlayers = [];

        if (!isset($data->players->sample)) {
            $onlinePlayers = false;
        } else {
            foreach ($data->players->sample as $player) {

                if (empty($player)) continue;

                array_push($onlinePlayers, $this->formatPlayer($player->name, $player->id));
            }
        }

        return [
            'hostname' => $host,
            'port' => $port,
            'ping' => $ping,
            'version' => $data->version->name ?? false,
            'protocol' => $data->version->protocol ?? false,
            'players' => $playerCount,
            'playerlist' => $onlinePlayers,
            'max_players' => $data->players->max ?? false,
            'description' => $description,
            'description_parsed' => $this->cleanMotd($descriptionParsed),
            'description_raw' => $descriptionRaw,
            'favicon' => $data->favicon ?? false,
            'modinfo' => $data->modinfo ?? false,
        ];
    }

    private static function readVarInt($socket)
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

    /**
     * @param $player
     * @param $uuid
     *
     * @return array
     */
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
