<?php
declare(strict_types=1);

namespace Pioniro\Seranking;

use Http\Client\Exception;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Client
{
    protected const BASE_PATH = 'https://api4.seranking.com';

    /**
     * @var string|null
     */
    protected $login;

    /**
     * @var string|null
     */
    protected $pass;

    /**
     * @var string|null
     */
    protected $token;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var RequestFactory
     */
    protected $httpMessageFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Client constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {

        if (isset($config['token'])) {
            $this->token = $config['token'];
        }

        if (isset($config['http_client']) && $config['http_client'] instanceof ClientInterface) {
            $this->httpClient = $config['http_client'];
        } else {
            $this->httpClient = HttpClientDiscovery::find();
        }

        if (isset($config['http_request_factory']) && $config['http_request_factory'] instanceof RequestFactoryInterface) {
            $this->httpMessageFactory = $config['http_request_factory'];
        } else {
            $this->httpMessageFactory = MessageFactoryDiscovery::find();
        }

        if (isset($config['logger']) && $config['logger'] instanceof LoggerInterface) {
            $this->logger = $config['logger'];
        } else {
            $this->logger = new NullLogger();
        }
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $payload
     * @return ResponseInterface
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function request(string $method, string $path, array $payload = []): ResponseInterface
    {
        $request = $this->httpMessageFactory->createRequest($method, $path, [
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Token %s', $this->token),
            ($method === 'GET' ? null : json_encode($payload))
        ]);
        return $this->httpClient->sendRequest($request);
    }
}