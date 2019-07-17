Seranking api client
====================
[![Build Status](https://travis-ci.org/pioniro/seranking-api-php.svg?branch=master)](https://travis-ci.org/pioniro/seranking-api-php)
[![Coverage Status](https://coveralls.io/repos/github/pioniro/seranking-api-php/badge.svg?branch=master)](https://coveralls.io/github/pioniro/seranking-api-php?branch=master)


Library for seranking api v4

### Установка
```
composer require pioniro/seranking-api-php
```

### How to use
```php
$config = [
    'token' => YOUR_API_TOKEN,
    'http_client' => <Optional \Psr\Http\Client\ClientInterface implementation>,
    'http_request_factory' => <Optional \Http\Message\RequestFactory implementation>,
    'logger' => <Optional \Psr\Log\LoggerInterface implementation>
];
$client = new \Pioniro\Seranking\Client($config);
$service = new \Pioniro\Seranking\Service\PositionService($client);

```