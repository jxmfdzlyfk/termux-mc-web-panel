<?php
/**
 * Minecraft RCON 客户端
 * 实现 Source RCON 协议
 */
class Rcon {
    private $socket = null;
    private $requestId = 0;

    public function connect($host, $port, $password, $timeout = 3) {
        $this->socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$this->socket) {
            throw new Exception("无法连接 RCON ($host:$port): $errstr");
        }
        stream_set_timeout($this->socket, $timeout);

        // 登录
        $this->requestId = 1;
        $this->writePacket(3, $password);
        $response = $this->readPacket();
        if (!$response || $response['id'] !== $this->requestId) {
            throw new Exception("RCON 认证失败（密码错误？）");
        }
        return true;
    }

    public function command($cmd) {
        $this->requestId++;
        $this->writePacket(2, $cmd);
        $response = $this->readPacket();
        return $response['body'] ?? '';
    }

    private function writePacket($type, $body) {
        $data = pack('VV', $this->requestId, $type) . $body . "\x00\x00";
        $packet = pack('V', strlen($data)) . $data;
        fwrite($this->socket, $packet);
    }

    private function readPacket() {
        $sizeData = fread($this->socket, 4);
        if (strlen($sizeData) < 4) return null;
        $size = unpack('V', $sizeData)[1];
        $data = '';
        while (strlen($data) < $size) {
            $chunk = fread($this->socket, $size - strlen($data));
            if ($chunk === false || $chunk === '') break;
            $data .= $chunk;
        }
        if (strlen($data) < 10) return null;
        $unpacked = unpack('Vid/Vtype', substr($data, 0, 8));
        $body = substr($data, 8, -2);
        return ['id' => $unpacked['id'], 'type' => $unpacked['type'], 'body' => $body];
    }

    public function close() {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    public function __destruct() {
        $this->close();
    }
}
