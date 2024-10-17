<?php
require __DIR__ . '/vendor/autoload.php';

include_once($_SERVER['DOCUMENT_ROOT'] . '/config/access.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/config/base.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/config/connection.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/config/assets.php');

include_once($_SERVER['DOCUMENT_ROOT'] . '/helpers.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/models.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/enums.php');

include_once($_SERVER['DOCUMENT_ROOT'] . '/config/catalog.php');

$router = new \Bramus\Router\Router();
$active_servers = [];

// routes
$router->get('/asset/', function() 
{
	if (!isset($_GET['id'])) 
		die();

    if ((int)$_GET['id'] < 0)
        $assetId = reverseOverflow((int)$_GET['id']);
	else
        $assetId = (int)$_GET['id'];

    // check if stored locally
    $file = $_SERVER['DOCUMENT_ROOT'] . '/assets/' . $assetId;

    if (file_exists($file))
        die(file_get_contents($file));

	$asset = 'https://assetdelivery.roblox.com/v1/asset/?id=' . $assetId;
	
	header("Location: " . $asset);
	die();
});

$router->get('/Setting/QuietGet/{path}', function($path) 
{
	$asset = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/flags.json');
	
	header("Content-Type: text/plain");
	echo $asset;
});

$router->get('/catalog-assets', function()
{
    global $wearableAssets;
    
    header("Access-Control-Allow-Origin: qrc:");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    if (!isset($_GET['type']))
    {
        $category = AssetType::HAT;
    }
    else
    {
        $category = AssetType::from($_GET['type']);
    }

    // https://stackoverflow.com/questions/7826358/how-to-filter-an-array-of-object
    $wearableAssets = array_filter($wearableAssets, function($obj) use($category) {
        return $obj->assetType == $category;
    });

    // should worry about category choice later
    // temp response

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(value: $wearableAssets); // check config/catalog.php
    return;
});

$router->get('/ping', function() 
{
    global $announceKey;
    global $pingKey;
    global $connection;

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

    if ($canSeeServers) {
        $currentTimestamp = time();

        $stmt = $connection->prepare("SELECT * FROM servers WHERE ttl > ?");
        $stmt->execute([$currentTimestamp]);

        while ($server = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $response["ActiveServers"][] = [
                "server_name" => $server["server_name"],
                "host" => $server["host"],
                "machine_address" => $server["machine_address"],
                "player_count" => $server["player_count"],
                "player_limit" => $server["player_limit"],
                "server_port" => $server["server_port"],
                "server_motd_preview" => $server["server_motd_preview"],
                "server_motd_content" => $server["server_motd_content"],
                "custom_password" => $server["custom_password"],
                "ttl" => $server["ttl"],
                "authorization_thing" => $server["authorization_thing"],
                "virtual_version" => $server["virtual_version"],
            ];
        }
    }

    header("Content-Type: application/json");
    echo json_encode($response);
});


$router->post('/universes/validate-place-join', function() 
{
    die('true');
});

$router->get('/v1.1/avatar-fetch/', function() 
{
    global $baseUrl;
    global $wearableAssets;
    
    if (!isset($_GET['json']))
        die();

    if (!isJson(urldecode($_GET['json'])))
        die();

    $json = json_decode(urldecode($_GET['json']));
    $assets = [];

    foreach($json as $i => $asset) {
        if (in_array($asset->assetId, array_column($wearableAssets, 'assetId'))) {
            array_push($assets, $baseUrl . 'asset/?id=' . $asset->assetId);
        }
    }

    die(implode(';', $assets));
});

$router->post('/announce', function()  
{
    global $active_servers;
    global $announceKey;
    global $connection;

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
    error_log("GOOD DATA 1");

        if ($aserv->authorization != $announceKey) {
            http_response_code(403);
            echo "Unauthorized - Invalid announce key";
            return;
        }
        error_log("GOOD DATA 2");

        $aserv->machine_address = $_SERVER['REMOTE_ADDR'];

        $active_servers[$_SERVER['REMOTE_ADDR']] = $aserv;

        $result = $connection->prepare("SELECT count(*) FROM `servers` WHERE machine_address = ?");
        $result->execute([$aserv->machine_address]);
        
        if ($result->fetchColumn() == 0) {
            $stmt = $connection->prepare("INSERT INTO servers (server_name, host, machine_address, player_count, player_limit, server_port, server_motd_preview, server_motd_content, custom_password, ttl, authorization_thing, virtual_version) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $aserv->server_name,
                $aserv->host,
                $aserv->machine_address,
                $aserv->player_count,
                $aserv->player_limit,
                $aserv->server_port,
                $aserv->server_motd_preview,
                $aserv->server_motd_content,
                $aserv->custom_password,
                $aserv->ttl,
                $aserv->authorization,
                $aserv->virtual_version,
            ]);
            $stmt = null;
        } else {
            $newTTL = time() + (2 * 60);
            $stmt = $connection->prepare("UPDATE servers SET ttl = ?, player_count = ? WHERE machine_address = ?");
            $stmt->execute([$newTTL, $aserv->player_count, $aserv->machine_address]);
            $stmt = null;
    error_log("UPDATING DATA");
    error_log(json_encode($aserv));

        }        

        http_response_code(200);
        echo "OK";
    } else {
    error_log("BAD DATA");

        http_response_code(403);
        echo "Server suppressed";
    }
});

$router->run();