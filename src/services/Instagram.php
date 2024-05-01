<?php

namespace bubalubs\craftinstagramapi\services;

use Craft;
use yii\base\Component;
use yii\caching\CacheInterface;
use GuzzleHttp\Client;

class Instagram extends Component
{
    private CacheInterface $cache;

    public function init()
    {
        $this->cache = Craft::$app->getCache();
    }

    public function getProfile($cache = true): array
    {
        $profile = $this->cache->get('instagram-api-profile');

        if ($profile && $cache) {
            return json_decode($profile, true);
        }

        $accessToken = Craft::$app->plugins->getPlugin('instagram-api')->getSettings()->accessToken;

        if (!$accessToken) {
            return [];
        }

        $client = new Client();

        $response = $client->get("https://graph.instagram.com/me?fields=id,username,account_type,media_count&access_token={$accessToken}");

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $contents = $response->getBody()->getContents();

        if ($cache) {
            $this->cache->set('instagram-api-profile', $contents, 60 * 60 * 24);
        }

        return json_decode($contents, true);
    }

    public function getMedia($cache = true): array
    {
        $media = $this->cache->get('instagram-api-media');

        if ($media && $cache) {
            return json_decode($media, true)['data'];
        }

        $accessToken = Craft::$app->plugins->getPlugin('instagram-api')->getSettings()->accessToken;

        if (!$accessToken) {
            return [];
        }

        $client = new Client();

        $response = $client->get("https://graph.instagram.com/me/media?fields=id,caption,media_type,media_url,thumbnail_url,permalink,timestamp&access_token={$accessToken}");

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $contents = $response->getBody()->getContents();

        if ($cache) {
            $this->cache->set('instagram-api-media', $contents, 60 * 60 * 24);
        }

        return json_decode($contents, true)['data'];
    }

    public function getMediaCacheStatus(): bool
    {
        return (bool) $this->cache->get('instagram-api-media');
    }

    public function getProfileCacheStatus(): bool
    {
        return (bool) $this->cache->get('instagram-api-profile');
    }
}
