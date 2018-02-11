<?php

namespace App\Component\Exchange;

use Kuxin\Helper\Http;
use Kuxin\Helper\Json;

class Helper
{
    protected function getJson(string $endpoint, string $path, array $params = []): array
    {
        $httpResult = Http::get($endpoint . $path, $params);
        $jsonResult = Json::decode($httpResult);
        return $jsonResult ?: [];
    }

    protected function postJson(string $endpoint, string $path, array $params = []): array
    {
        $httpResult = Http::post($endpoint . $path, $params);
        $jsonResult = Json::decode($httpResult);
        return $jsonResult ?: [];
    }
}