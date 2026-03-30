<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;

class HttpClientService
{
    public function client(array $config = []): Client
    {
        return new Client($config + ['timeout' => 15]);
    }
}
