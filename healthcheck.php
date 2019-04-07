<?php

use Dotenv\Dotenv;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\Serializer\CompactSerializer;
use GuzzleHttp\Client;
use JJG\Ping;
use GuzzleHttp\Exception\ClientException;

include 'vendor/autoload.php';
$dotenv = Dotenv::create(__DIR__);
$dotenv->load();

function getIamToken()
{
    $service_account_id = $_ENV['YC_SERVICE_ACCOUNT_ID'];
    $key_id = $_ENV['YC_KEY_ID'];

    $jsonConverter = new StandardConverter();
    $algorithmManager = AlgorithmManager::create([
        new PS256()
    ]);

    $jwsBuilder = new JWSBuilder($jsonConverter, $algorithmManager);

    $now = time();

    $claims = [
        'aud' => 'https://iam.api.cloud.yandex.net/iam/v1/tokens',
        'iss' => $service_account_id,
        'iat' => $now,
        'exp' => $now + 3600
    ];

    $header = [
        'alg' => 'PS256',
        'typ' => 'JWT',
        'kid' => $key_id
    ];

    $key = JWKFactory::createFromKeyFile('private.pem');
    $payload = $jsonConverter->encode($claims);

    $jws = $jwsBuilder
        ->create()
        ->withPayload($payload)
        ->addSignature($key, $header)
        ->build();

    $serializer = new CompactSerializer($jsonConverter);

    $token = $serializer->serialize($jws);

    $client = new Client();
    $response = $client->request('POST', 'https://iam.api.cloud.yandex.net/iam/v1/tokens', [
        'json' => [
            'jwt' => $token
        ]
    ]);

    $responseToken = json_decode($response->getBody()->getContents());
    return $responseToken->iamToken;
}

function startInstance($name)
{
    $client = new Client();

    try {
        $client->request('POST', 'https://compute.api.cloud.yandex.net/compute/v1/instances/' . $name . ':start', [
            'headers' => [
                'Authorization' => 'Bearer ' . getIamToken()
            ]
        ]);
    } catch (ClientException $e) {
        print 'Start Instance error:' . $e->getMessage() . PHP_EOL;
    }
}


$ping = new Ping($_ENV['YC_INSTANCE_IP'], 128, 5);
$latency = $ping->ping();
if ($latency !== false) {
    print 'Latency is ' . $latency . ' ms' . PHP_EOL;
} else {
    print 'Start Instance' . PHP_EOL;
    startInstance($_ENV['YC_INSTANCE_NAME']);
}
