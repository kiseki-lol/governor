<?php

class ActiveServer {
    public $server_name;
    public $host;
    public $machine_address;
    public $player_count;
    public $player_limit;
    public $server_port;
    public $server_motd_preview;
    public $server_motd_content;
    public $custom_password;
    public $ttl;
    public $authorization;

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
            "Host" => $this->host,
            "GUID" => "x"
        ];

        return json_encode($obj);
    }
}