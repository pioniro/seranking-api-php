<?php

namespace Pioniro\Seranking\Tests\Service;

use DateTime;
use Http\Client\Exception;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\StreamFactoryDiscovery;
use Http\Mock\Client as HttpClient;
use PHPUnit\Framework\TestCase;
use Pioniro\Seranking\Client;
use Pioniro\Seranking\Entity\PositionItem;
use Pioniro\Seranking\Entity\PositionTask;
use Pioniro\Seranking\Entity\PositionTaskList;
use Pioniro\Seranking\Entity\PositionTaskResult;
use Pioniro\Seranking\Entity\PositionTaskStatus;
use Pioniro\Seranking\Exception\BadRequestApiException;
use Pioniro\Seranking\Exception\BadResponseApiException;
use Pioniro\Seranking\Exception\EmptyBalanceException;
use Pioniro\Seranking\Exception\PositionTaskException;
use Pioniro\Seranking\Exception\PositionTaskNotFoundException;
use Pioniro\Seranking\Exception\PositionTaskNotYetCompleteException;
use Pioniro\Seranking\Service\PositionService;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use RuntimeException;

class PositionServiceTest extends TestCase
{

    /**
     * @param PositionTask $task
     * @param string $exception
     * @param string $text
     * @throws ClientExceptionInterface
     * @throws Exception
     * @dataProvider createTaskNotValidProvider
     */
    public function testCreateTaskNotValid(PositionTask $task, string $exception, string $text)
    {
        Psr17FactoryDiscovery::findRequestFactory();
        $client = new HttpClient();
        $client->setDefaultException(new RuntimeException('Should not be performed'));

        $service = new PositionService(new Client([
            'http_client' => $client
        ]));
        $this->expectException($exception);
        $this->expectExceptionMessage($text);
        $service->createTask($task);
        $this->fail('This should not have happened');
    }

    public function createTaskNotValidProvider()
    {
        return [
            [
                (new PositionTask())
                    ->setQuery(null)
                    ->setEngine(411),
                PositionTaskException::class,
                PositionTaskException::MESSAGE_NEW_TASK_MUST_CONTAIN_QUERY
            ],
            [
                (new PositionTask())
                    ->setId(123123)
                    ->setQuery('query')
                    ->setEngine(411),
                PositionTaskException::class,
                PositionTaskException::MESSAGE_NEW_TASK_CONTAIN_ID
            ],
            [
                (new PositionTask())
                    ->setQuery('some query'),
                PositionTaskException::class,
                PositionTaskException::MESSAGE_NEW_TASK_MUST_CONTAIN_ENGINE
            ]
        ];
    }

    /**
     * @param PositionTask $task
     * @param $response
     * @param $expectedId
     * @throws ClientExceptionInterface
     * @throws Exception
     * @dataProvider createTaskValidProvider
     */
    public function testCreateTaskValid(PositionTask $task, $response, $expectedId)
    {
        $client = new HttpClient();
        $client->setDefaultResponse(
            $this->getResponseFactory()
                ->createResponse(200, 'OK')
                ->withBody(StreamFactoryDiscovery::find()->createStream($response))
        );

        $service = new PositionService(new Client([
            'http_client' => $client
        ]));
        $id = $service->createTask($task);
        $this->assertEquals($expectedId, $id);
    }

    protected function getResponseFactory(): ResponseFactoryInterface
    {
        return Psr17FactoryDiscovery::findResponseFactory();
    }

    public function createTaskValidProvider()
    {
        return [
            [
                (new PositionTask())
                    ->setQuery('some query 1')
                    ->setEngine(411),
                json_encode([
                    ['task_id' => 123123]
                ]),
                123123
            ],
            [
                (new PositionTask())
                    ->setQuery('some query 1')
                    ->setEngine(411)
                    ->setRegionYandex(341),
                json_encode([
                    ['task_id' => 123123]
                ]),
                123123
            ],
            [
                (new PositionTask())
                    ->setQuery('some query 1')
                    ->setEngine(411)
                    ->setRegionYandex(341)
                    ->setRegionGoogle('Анадырь'),
                json_encode([
                    ['task_id' => 123123]
                ]),
                123123
            ],
        ];
    }

    /**
     * @param PositionTask $task
     * @param int $responseCode
     * @param string $responseBody
     * @param string $exception
     * @param string $exceptionMessage
     * @param int $exceptionCode
     * @throws ClientExceptionInterface
     * @throws Exception
     * @dataProvider createTaskApiErrorProvider
     */
    public function testCreateTaskHandleApiError(
        PositionTask $task,
        int $responseCode,
        string $responseBody,
        string $exception,
        ?string $exceptionMessage,
        int $exceptionCode
    ) {
        $client = new HttpClient();
        $client->setDefaultResponse(
            $this->getResponseFactory()
                ->createResponse($responseCode)
                ->withBody(StreamFactoryDiscovery::find()->createStream($responseBody))
        );

        $service = new PositionService(new Client([
            'http_client' => $client
        ]));
        $this->expectExceptionCode($exceptionCode);
        $this->expectException($exception);
        if ($exceptionMessage)
            $this->expectExceptionMessage($exceptionMessage);
        $service->createTask($task);
        $this->fail('This should not have happened');
    }

//    public function testFetchResult()
//    {
//
//    }

    public function createTaskApiErrorProvider()
    {
        return [
            [
                (new PositionTask())
                    ->setQuery('some query 1')
                    ->setEngine(411),
                403,
                json_encode([
                    'message' => 'Empty balance'
                ]),
                EmptyBalanceException::class,
                EmptyBalanceException::MESSAGE,
                403,
            ],
            [
                (new PositionTask())
                    ->setQuery('some query 1')
                    ->setEngine(411),
                400,
                json_encode([
                    'message' => 'Invalid engine_id'
                ]),
                BadRequestApiException::class,
                null,
                400,
            ],
            [
                (new PositionTask())
                    ->setQuery('some query 1')
                    ->setEngine(411),
                500,
                'not ok',
                BadResponseApiException::class,
                null,
                500,
            ],
        ];
    }

    /**
     * @param int $taskId
     * @param string $response
     * @param string $status
     * @throws ClientExceptionInterface
     * @throws Exception
     * @dataProvider checkStatus200Provider
     */
    public function testCheckStatus200(int $taskId, string $response, string $status)
    {

        $client = new HttpClient();
        $client->setDefaultResponse(
            $this->getResponseFactory()
                ->createResponse(200)
                ->withBody(StreamFactoryDiscovery::find()->createStream($response))
        );

        $service = new PositionService(new Client([
            'http_client' => $client
        ]));
        $result = $service->checkStatus($taskId);
        $this->assertEquals($status, $result);
    }

    public function checkStatus200Provider()
    {
        return [
            [
                123123,
                json_encode([
                    'status' => PositionTaskStatus::STATUS_PROCESSING
                ]),
                PositionTaskStatus::STATUS_PROCESSING
            ],
            [
                123123,
                json_encode([
                    'results' => []
                ]),
                PositionTaskStatus::STATUS_DONE
            ]
        ];
    }

    /**
     * @param int $taskId
     * @param int $code
     * @param string $response
     * @param string $exception
     * @throws ClientExceptionInterface
     * @throws Exception
     * @dataProvider checkStatusWithErrorsProvider
     */
    public function testCheckStatusWithErrors(int $taskId, int $code, string $response, string $exception)
    {
        $client = new HttpClient();
        $client->setDefaultResponse(
            $this->getResponseFactory()
                ->createResponse($code)
                ->withBody(StreamFactoryDiscovery::find()->createStream($response))
        );

        $service = new PositionService(new Client([
            'http_client' => $client
        ]));
        $this->expectException($exception);
        $service->checkStatus($taskId);
    }

    public function checkStatusWithErrorsProvider()
    {
        return [
            [
                123123,
                200,
                'not ok',
                BadResponseApiException::class
            ],
            [
                123123,
                404,
                'not found',
                PositionTaskNotFoundException::class
            ]
        ];
    }

    /**
     * @param int $taskId
     * @param string $response
     * @param PositionTaskResult $expectedResult
     * @dataProvider fetchResult200Provider
     */
    public function testFetchResultStatus200(int $taskId, string $response, PositionTaskResult $expectedResult)
    {
        $client = new HttpClient();
        $client->setDefaultResponse(
            $this->getResponseFactory()
                ->createResponse(200)
                ->withBody(StreamFactoryDiscovery::find()->createStream($response))
        );

        $service = new PositionService(new Client([
            'http_client' => $client
        ]));
        $result = $service->fetchResult($taskId);
        $this->assertEquals($result, $expectedResult);
    }

    public function fetchResult200Provider()
    {
        return [
            [
                123123,
                json_encode([
                    'results' => [
                        [
                            'position' => '99',
                            'url' => 'https://techcrunch.com/2018/08/20/google-doctor-fork/',
                            'title' => 'Google created a fake pizza brand to test out creative strategies for ...',
                            'snippet' => <<<TEXT
<span class="st"><span class="f">Aug 20, 2018 - </span>Google's Unskippable Labs team has been testing ad effectiveness in a compelling new way: It created a fake <em>pizza</em> brand called Doctor Fork, ...</span>
TEXT
                            ,
                            'cache_url' => 'https://webcache.googleusercontent.com/search?q=cache:wi5sKCy0ResJ:https://techcrunch.com/2018/08/20/google-doctor-fork/+&cd=120&hl=en&ct=clnk&gl=us'
                        ]
                    ]
                ]),
                new PositionTaskResult([
                    new PositionItem(
                        99,
                        'https://techcrunch.com/2018/08/20/google-doctor-fork/',
                        'Google created a fake pizza brand to test out creative strategies for ...',
                        <<<TEXT
<span class="st"><span class="f">Aug 20, 2018 - </span>Google's Unskippable Labs team has been testing ad effectiveness in a compelling new way: It created a fake <em>pizza</em> brand called Doctor Fork, ...</span>
TEXT
                        ,
                        'https://webcache.googleusercontent.com/search?q=cache:wi5sKCy0ResJ:https://techcrunch.com/2018/08/20/google-doctor-fork/+&cd=120&hl=en&ct=clnk&gl=us'
                    )
                ])
            ]
        ];
    }

    /**
     * @param int $taskId
     * @param int $code
     * @param string $response
     * @param string $exception
     * @dataProvider fetchResultWithErrorsProvider
     */
    public function testFetchResultStatusWithErrors(int $taskId, int $code, string $response, string $exception)
    {
        $client = new HttpClient();
        $client->setDefaultResponse(
            $this->getResponseFactory()
                ->createResponse($code)
                ->withBody(StreamFactoryDiscovery::find()->createStream($response))
        );

        $service = new PositionService(new Client([
            'http_client' => $client
        ]));
        $this->expectException($exception);
        $service->fetchResult($taskId);
    }

    public function fetchResultWithErrorsProvider()
    {
        return [
            [
                123123,
                200,
                'not ok',
                BadResponseApiException::class
            ],
            [
                123123,
                404,
                'not found',
                PositionTaskNotFoundException::class
            ],
            [
                123123,
                500,
                'server error',
                BadResponseApiException::class
            ],
            [
                123123,
                200,
                json_encode([
                    'status' => PositionTaskStatus::STATUS_PROCESSING
                ]),
                PositionTaskNotYetCompleteException::class
            ]
        ];
    }

    /**
     * @param string $response
     * @param PositionTaskList $expectedResult
     * @dataProvider listTasks200Provider
     */
    public function testListTasks200(string $response, PositionTaskList $expectedResult)
    {
        $client = new HttpClient();
        $client->setDefaultResponse(
            $this->getResponseFactory()
                ->createResponse(200)
                ->withBody(StreamFactoryDiscovery::find()->createStream($response))
        );

        $service = new PositionService(new Client([
            'http_client' => $client
        ]));
        $result = $service->listTasks();
        $this->assertEquals($expectedResult, $result);
    }

    public function listTasks200Provider()
    {
        return [
            [
                json_encode([
                    'tasks' => [
                        [
                            'id' => '18639398',
                            'query' => 'pizza',
                            'region_name' => 'New York',
                            'engine_id' => '200',
                            'region_id' => '0',
                            'added' => '2018-08-28 12:25:52',
                            'is_completed' => '1'
                        ],
                        [
                            'id' => '18639399',
                            'query' => 'pizza1',
                            'region_name' => 'New York1',
                            'engine_id' => '201',
                            'region_id' => '1',
                            'added' => '2018-08-28 12:25:53',
                            'is_completed' => '0'
                        ]
                    ]
                ]),
                new PositionTaskList([
                    (new PositionTask())
                        ->setId(18639398)
                        ->setQuery('pizza')
                        ->setEngine(200)
                        ->setRegionGoogle('New York')
                        ->setRegionYandex(0)
                        ->setCreatedAt(new DateTime('2018-08-28 12:25:52'))
                        ->setStatus(PositionTaskStatus::STATUS_DONE),
                    (new PositionTask())
                        ->setId(18639399)
                        ->setQuery('pizza1')
                        ->setEngine(201)
                        ->setRegionGoogle('New York1')
                        ->setRegionYandex(1)
                        ->setCreatedAt(new DateTime('2018-08-28 12:25:53'))
                        ->setStatus(PositionTaskStatus::STATUS_PROCESSING),
                ])
            ],
            [
                json_encode([
                    'tasks' => []
                ]),
                new PositionTaskList([])
            ]
        ];
    }

    /**
     * @param int $code
     * @param string $response
     * @param string $exception
     * @dataProvider listTasksExceptionProvider
     */
    public function testListTasksException(int $code, string $response, string $exception)
    {
        $client = new HttpClient();
        $client->setDefaultResponse(
            $this->getResponseFactory()
                ->createResponse($code)
                ->withBody(StreamFactoryDiscovery::find()->createStream($response))
        );

        $service = new PositionService(new Client([
            'http_client' => $client
        ]));
        $this->expectException($exception);
        $service->listTasks();
    }

    public function listTasksExceptionProvider()
    {
        return [
            [
                200,
                'bad response',
                BadResponseApiException::class
            ]
        ];
    }
}
