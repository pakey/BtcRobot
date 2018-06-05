<?php

namespace App\Component\Exchange;

use Kuxin\Helper\Http;
use Kuxin\Helper\Json;

class Kernel
{

    protected function getJson(string $endpoint, string $path, array $params = []): array
    {
        $httpResult = Http::get($endpoint . $path, $params, ['Content-Type' => 'application/x-www-form-urlencoded']);
        $jsonResult = Json::decode($httpResult);
        return $jsonResult ?: [];
    }

    protected function postJson(string $endpoint, string $path, array $params = []): array
    {
        $httpResult = Http::post($endpoint . $path, $params, ['Content-Type' => 'application/json']);
        $jsonResult = Json::decode($httpResult);
        return $jsonResult ?: [];
    }
}