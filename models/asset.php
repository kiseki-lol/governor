<?php

class Asset
{
    public string $name;
    public string $description;
    public string $author;
    public string $assetThumbnail;
    public int $assetId;
    public int $version;

    public function __construct(string $name, string $description, string $author, int $assetId, string $assetThumbnail, int $version)
    {
        $this->name = $name;
        $this->description = $description;
        $this->author = $author;
        $this->assetId = $assetId;
        $this->assetThumbnail = $assetThumbnail;
        $this->version = $version;
    }

    public static function create(array $data): Asset
    {
        $thumbnailUrl = isset($data['assetId']) ? (new self(
            $data['name'],
            $data['description'],
            $data['author'],
            $data['assetId'],
            '', // Temporary empty thumbnail to initialize
            $data['version']
        ))->getThumbnailUrl() : '';

        return new self(
            $data['name'],
            $data['description'],
            $data['author'],
            $data['assetId'],
            $thumbnailUrl,
            $data['version']
        );
    }

    public function getThumbnailUrl(bool $shouldCache = false): string
    {
        $request = file_get_contents("https://thumbnails.roblox.com/v1/assets?assetIds=" . $this->assetId . "&returnPolicy=PlaceHolder&size=420x420&format=Png&isCircular=false");
        if (!$this->isJson($request)) {
            return 'failed to get thumbnail url';
        }

        $request = json_decode($request, true);
        return $request['data'][0]['imageUrl'] ?? 'failed to get thumbnail url';
    }

    // make helpers.php
    private function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}