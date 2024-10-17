<?php

class ActiveServer {
    public string $server_name;
    public string $host;
    public string $machine_address;
    public int $player_count;
    public int $player_limit;
    public int $server_port;
    public string $server_motd_preview;
    public string $server_motd_content;
    public bool $custom_password;
    public int $ttl;
    public string $authorization;
    public int $virtual_version;

    public function __construct() {
        $this->server_name = "";
        $this->host = "";
        $this->machine_address = "";
        $this->player_count = 0;
        $this->player_limit = 0;
        $this->server_port = 0;
        $this->server_motd_preview = "";
        $this->server_motd_content = "";
        $this->custom_password = false;
        $this->ttl = 0;
        $this->authorization = "";
        $this->virtual_version = 0;
    }

    public function fromJson($json) {
        $data = json_decode($json, true);

        if (!isset($data["PlayerCount"]) || !is_int($data["PlayerCount"])) {
            error_log("Bad PlayerCount");
            return false;
        }
        if (!isset($data["PlayerLimit"]) || !is_int($data["PlayerLimit"])) {
            error_log("Bad PlayerLimit");
            return false;
        }
        if (!isset($data["CustomPassword"]) || !is_bool($data["CustomPassword"])) {
            error_log("Missing CustomPassword");
            return false;
        }
        if (!isset($data["ServerPort"]) || !is_int($data["ServerPort"])) {
            error_log("Missing ServerPort");
            return false;
        }
        if (!isset($data["Authorization"])) {
            error_log("Missing Authorization");
            return false;
        }
        if (!isset($data["MotdContent"])) {
            error_log("Missing MotdContent");
            return false;
        }
        if (!isset($data["MotdPreview"])) {
            error_log("Missing MotdPreview");
            return false;
        }
        if (!isset($data["ServerName"])) {
            error_log("Missing ServerName");
            return false;
        }
        if (!isset($data["Host"])) {
            error_log("Missing Host");
            return false;
        }
        if (!isset($data["VirtualVersion"])) {
            error_log("Missing VirtualVersion");
            return false;
        }
        
        $this->server_name = $data["ServerName"];
        $this->server_motd_content = $data["MotdContent"];
        $this->server_motd_preview = $data["MotdPreview"];
        $this->player_count = $data["PlayerCount"];
        $this->player_limit = $data["PlayerLimit"];
        $this->authorization = $data["Authorization"];
        $this->custom_password = $data["CustomPassword"];
        $this->ttl = time() + 120; // 2-minute TTL
        $this->host = $data["Host"];
        $this->server_port = $data["ServerPort"];
        $this->virtual_version = $data["VirtualVersion"];

        return true;
    }

    public function toJson() {
        $obj = [
            "ServerName" => $this->server_name,
            "MotdContent" => $this->server_motd_content,
            "MotdPreview" => $this->server_motd_preview,
            "PlayerCount" => $this->player_count,
            "PlayerLimit" => $this->player_limit,
            "CustomPassword" => $this->custom_password,
            "MachineAddress" => $this->machine_address,
            "ServerPort" => $this->server_port,
            "VirtualVersion" => $this->virtual_version,
            "Host" => $this->host,
            "GUID" => "x"
        ];

        return json_encode($obj);
    }
}