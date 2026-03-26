<?php

namespace App\Support;

use FedaPay\HttpClient\CurlClient;

/**
 * Configure le client HTTP du SDK FedaPay (timeouts cURL).
 */
class FedaPayHttpConfig
{
    public static function appliquer(): void
    {
        $timeout = max(1, (int) config('services.fedapay.timeout_seconds', 15));
        $connect = max(1, (int) config('services.fedapay.connect_timeout_seconds', 5));

        $client = CurlClient::instance();
        $client->setTimeout($timeout);
        $client->setConnectTimeout($connect);
    }
}
