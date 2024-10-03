<?php
error_reporting(E_ERROR | E_PARSE);

require __DIR__ . '/vendor/autoload.php';

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

$pingKey = "";
$announceKey = "";

$router = new \Bramus\Router\Router();
$active_servers = [];

// routes
$router->get('/asset/', function() 
{
	if (!isset($_GET['id'])) 
		return '';

	if (!ctype_digit(text: $_GET['id']))
		return '';
	
	$asset = 'https://assetdelivery.roblox.com/v1/asset/?id=' . $_GET['id'];
	
	header("Location: " . $asset);
	die();
});

$router->get('/Setting/QuietGet/{path}', function($path) 
{
	$asset = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/flags.json');
	
	header("Content-Type: text/plain");
	echo $asset;
});

$router->get('/ping', function() 
{
    global $announceKey;
    global $pingKey;

    $canSeeServers = true;
    $canHostServers = true;

    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        if ($pingKey != "") {
            $canSeeServers = false;
        }
        if ($announceKey != "") {
            $canHostServers = false;
        }
    } else {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        if ($pingKey != $authHeader) {
            $canSeeServers = false;
        }
        if ($announceKey != $authHeader) {
            $canHostServers = false;
        }
    }

    $response = [
        "ActiveServers" => [],
        "MasterMotd" => "This is the official Aya Governor instance.",
        "SpecialMotd" => "",
        "Authentication" => [
            "CanHostServers" => $canHostServers,
            "CanReadMasterServer" => $canSeeServers
        ]
    ];

    header("Content-Type: application/json");
    echo json_encode($response);
});

$router->post('/announce', function() 
{
    global $active_servers;
    global $announceKey;

    $content_length = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;

    if ($content_length <= 0) {
        http_response_code(400);
        echo "Bad Request - Missing Content-Length";
        return;
    }

    $data = file_get_contents('php://input');
    
    if (isset($_SERVER['HTTP_CONTENT_ENCODING']) && $_SERVER['HTTP_CONTENT_ENCODING'] === 'gzip') {
        try {
            $data = gzdecode($data);
            if ($data === false) {
                throw new Exception('Failed to decompress gzip');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo "Bad Request - Failed to decompress gzip";
            return;
        }
    }

    error_log($data);

    $aserv = new ActiveServer();

    if ($aserv->fromJson($data)) {
        if ($aserv->authorization != $announceKey) {
            http_response_code(403);
            echo "Unauthorized - Invalid announce key";
            return;
        }

        $aserv->machine_address = $_SERVER['REMOTE_ADDR'];

        $active_servers[$_SERVER['REMOTE_ADDR']] = $aserv;

        http_response_code(200);
        echo "OK";
    } else {
        http_response_code(403);
        echo "Server suppressed";
    }
});

$router->run();