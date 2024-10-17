<?php

class Asset
{
    public string $name;
    public string $description;
    public string $author;
    public string $assetThumbnail;
    public AssetType $assetType;
    public int $assetId;
    public int $version;

    public function __construct(string $name, string $description, string $author, int $assetId, string $assetThumbnail, int $version, AssetType $assetType)
    {
        $this->name = $name;
        $this->description = $description;
        $this->author = $author;
        $this->assetId = $assetId;
        $this->assetThumbnail = $assetThumbnail;
        $this->version = $version;
        $this->assetType = $assetType;
    }

    public static function create(array $data): Asset
    {
        $thumbnailUrl = isset($data['assetId']) ? (new self(
            $data['name'],
            $data['description'],
            $data['author'],
            $data['assetId'],
            '', // Temporary empty thumbnail to initialize
            $data['version'],
            $data['assetType'],
        ))->getThumbnail(true) : '';

        return new self(
            $data['name'],
            $data['description'],
            $data['author'],
            $data['assetId'],
            $thumbnailUrl,
            $data['version'],
            $data['assetType']
        );
    }

    public function getThumbnail(bool $shouldUseCache = false): string
    {
        $cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/cache/';
        $cachePath = $cacheDir . $this->assetId;
        if ($shouldUseCache)
        {
            if (file_exists($cachePath))
            {
                // should be png
                $image = file_get_contents(filename: $cachePath);
                $image = base64_encode($image);
        
                // have to do this to account for cors stuff but planning to make it so it caches locally
                return 'data:image/jpeg;base64,' . $image;
            }
        }

        $request = file_get_contents("https://thumbnails.roblox.com/v1/assets?assetIds=" . $this->assetId . "&returnPolicy=PlaceHolder&size=420x420&format=Jpeg&isCircular=false");
        if (!isJson($request)) {
            return 'failed to get thumbnail url';
        }

        $request = json_decode($request, true);
        $url = $request['data'][0]['imageUrl'];
        if ($url == null)
            return 'failed to get thumbnail url';

        $image = file_get_contents($url);

        if ($shouldUseCache)
        {
            if (!is_dir($cacheDir))
            {
                mkdir($cacheDir);
            }
            file_put_contents($cachePath, $image);
        }

        $image = base64_encode($image);

        // have to do this to account for cors stuff but planning to make it so it caches locally
        return 'data:image/jpeg;base64,' . $image;
    }
}
