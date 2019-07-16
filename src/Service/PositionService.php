<?php
declare(strict_types=1);

namespace Pioniro\Seranking\Service;

use DateTime;
use Http\Client\Exception;
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
use Psr\Http\Client\ClientExceptionInterface;

class PositionService
{
    const PATH_NEW_TASK = '/parsing/serp/tasks';
    const PATH_CHECK_TASK = '/parsing/serp/tasks/%d';
    const PATH_LIST_TASKS = '/parsing/serp/tasks';

    /**
     * @var Client
     */
    protected $client;

    /**
     * PositionService constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param PositionTask $task
     * @return int
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function createTask(PositionTask $task): int
    {
        if ($task->getId()) {
            throw PositionTaskException::newTaskContainId();
        }
        if (!$task->getQuery()) {
            throw PositionTaskException::newTaskNotContainQuery();
        }
        if (!$task->getEngine()) {
            throw PositionTaskException::newTaskNotContainEngine();
        }
        $data = [
            'query' => $task->getQuery(),
            'region_id' => $task->getRegionYandex(),
            'region_name' => $task->getRegionGoogle(),
            'engine' => $task->getEngine(),
        ];
        $response = $this->client->request('POST', self::PATH_NEW_TASK, $data);
        $body = $response->getBody()->getContents();
        $json = null;
        if ($body) {
            $json = json_decode($body, true);
        }
        switch ($response->getStatusCode()) {
            case 403:
                throw new EmptyBalanceException();
            case 400:
                throw BadRequestApiException::invalidRequestField(
                    isset($json['message']) ? $json['message'] : 'undefined error'
                );
            /** @noinspection PhpMissingBreakStatementInspection */
            case 200:
                if (isset($json[0]['task_id'])) {
                    $id = intval($json[0]['task_id']);
                    $task->setId($id);
                    return $id;
                }
            default:
                throw new BadResponseApiException($body, $response->getStatusCode());
        }
    }

    /**
     * @param int $taskId
     * @return string
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function checkStatus(int $taskId): string
    {
        $response = $this->client->request('GET', sprintf(self::PATH_CHECK_TASK, $taskId));
        $body = $response->getBody()->getContents();
        $json = null;
        if ($body) {
            $json = json_decode($body, true);
        }
        switch ($response->getStatusCode()) {
            case 404:
                throw new PositionTaskNotFoundException();
            /** @noinspection PhpMissingBreakStatementInspection */
            case 200:
                if (isset($json['status']) && $json['status'] === PositionTaskStatus::STATUS_PROCESSING) {
                    return PositionTaskStatus::STATUS_PROCESSING;
                } elseif (isset($json['results'])) {
                    return PositionTaskStatus::STATUS_DONE;
                }
            default:
                throw new BadResponseApiException($body, $response->getStatusCode());
        }
    }

    public function fetchResult(int $taskId)
    {
        $response = $this->client->request('GET', sprintf(self::PATH_CHECK_TASK, $taskId));
        $body = $response->getBody()->getContents();
        $json = null;
        if ($body) {
            $json = json_decode($body, true);
        }
        switch ($response->getStatusCode()) {
            case 404:
                throw new PositionTaskNotFoundException();
            /** @noinspection PhpMissingBreakStatementInspection */
            case 200:
                if (isset($json['status']) && $json['status'] === PositionTaskStatus::STATUS_PROCESSING) {
                    throw new PositionTaskNotYetCompleteException();
                } elseif (isset($json['results'])) {
                    return $this->buildPositionResult($json['results']);
                }
            default:
                throw new BadResponseApiException($body, $response->getStatusCode());
        }
    }

    public function listTasks()
    {
        $response = $this->client->request('GET', self::PATH_LIST_TASKS);
        $body = $response->getBody()->getContents();
        $json = null;
        if ($body) {
            $json = json_decode($body, true);
        }
        switch ($response->getStatusCode()) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 200:
                if (isset($json['tasks'])) {
                    return $this->buildTaskList($json['tasks']);
                }
            default:
                throw new BadResponseApiException($body, $response->getStatusCode());
        }
    }

    protected function buildPositionResult(array $results): PositionTaskResult
    {
        $items = [];
        foreach ($results as $result) {
            $items[] = new PositionItem(
                intval($result['position']),
                strval($result['url']),
                strval($result['title']),
                strval($result['snippet']),
                strval($result['cache_url'])
            );
        }
        return new PositionTaskResult($items);
    }

    protected function buildTaskList(array $results): PositionTaskList
    {
        $tasks = [];
        foreach ($results as $result) {
            $task = new PositionTask();
            $task->setId(intval($result['id']));
            $task->setQuery($result['query']);
            $task->setRegionGoogle($result['region_name']);
            $task->setRegionYandex(intval($result['region_id']));
            $task->setEngine(intval($result['engine_id']));
            $task->setCreatedAt(new DateTime($result['added']));
            $task->setStatus(
                $result['is_completed'] === '1' ?
                    PositionTaskStatus::STATUS_DONE :
                    PositionTaskStatus::STATUS_PROCESSING
            );
            $tasks[] = $task;
        }
        return new PositionTaskList($tasks);
    }
}