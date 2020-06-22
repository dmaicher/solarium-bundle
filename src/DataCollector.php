<?php

namespace DAMA\SolariumBundle;

use Psr\Log\LoggerInterface;
use Solarium\Core\Client\Endpoint as SolariumEndpoint;
use Solarium\Core\Client\Request as SolariumRequest;
use Solarium\Core\Event\Events as SolariumEvents;
use Solarium\Core\Event\PostExecuteRequest as SolariumPostExecuteRequestEvent;
use Solarium\Core\Event\PreExecuteRequest as SolariumPreExecuteRequestEvent;
use Solarium\Core\Plugin\AbstractPlugin as SolariumPlugin;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class DataCollector extends SolariumPlugin implements DataCollectorInterface, \Serializable
{
    private $data = [];
    private $queries = [];
    private $currentRequest;
    private $currentStartTime;
    private $currentEndpoint;

    private $logger;
    private $stopwatch;
    private $eventDispatchers = [];

    /**
     * Plugin init function.
     *
     * Register event listeners
     */
    protected function initPluginType(): void
    {
        $dispatcher = $this->client->getEventDispatcher();
        if (!in_array($dispatcher, $this->eventDispatchers, true)) {
            if ($dispatcher instanceof EventDispatcherInterface) {
                $dispatcher->addListener(SolariumEvents::PRE_EXECUTE_REQUEST, [$this, 'preExecuteRequest'], 1000);
                $dispatcher->addListener(SolariumEvents::POST_EXECUTE_REQUEST, [$this, 'postExecuteRequest'], -1000);
            }
            $this->eventDispatchers[] = $dispatcher;
        }
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setStopwatch(Stopwatch $stopwatch): void
    {
        $this->stopwatch = $stopwatch;
    }

    public function log(SolariumRequest $request, $response, SolariumEndpoint $endpoint, $duration): void
    {
        $this->queries[] = [
            'request' => $request,
            'response' => $response,
            'duration' => $duration,
            'base_uri' => $endpoint->getBaseUri(),
        ];
    }

    public function collect(HttpRequest $request, HttpResponse $response, \Throwable $exception = null): void
    {
        if (isset($this->currentRequest)) {
            $this->failCurrentRequest();
        }

        $time = 0;
        foreach ($this->queries as $queryStruct) {
            $time += $queryStruct['duration'];
        }
        $this->data = [
            'queries' => $this->queries,
            'total_time' => $time,
        ];
    }

    public function getName(): string
    {
        return 'solr';
    }

    public function getQueries()
    {
        return array_key_exists('queries', $this->data) ? $this->data['queries'] : [];
    }

    public function getQueryCount()
    {
        return count($this->getQueries());
    }

    public function getTotalTime()
    {
        return array_key_exists('total_time', $this->data) ? $this->data['total_time'] : 0;
    }

    public function preExecuteRequest(SolariumPreExecuteRequestEvent $event): void
    {
        if (isset($this->currentRequest)) {
            $this->failCurrentRequest();
        }

        if (null !== $this->stopwatch) {
            $this->stopwatch->start('solr', 'solr');
        }

        $this->currentRequest = $event->getRequest();
        $this->currentEndpoint = $event->getEndpoint();

        if (null !== $this->logger) {
            $this->logger->debug($this->currentEndpoint->getBaseUri().$this->currentRequest->getUri());
        }
        $this->currentStartTime = microtime(true);
    }

    public function postExecuteRequest(SolariumPostExecuteRequestEvent $event): void
    {
        $endTime = microtime(true) - $this->currentStartTime;
        if (!isset($this->currentRequest)) {
            throw new \RuntimeException('Request not set');
        }
        if ($this->currentRequest !== $event->getRequest()) {
            throw new \RuntimeException('Requests differ');
        }

        if (null !== $this->stopwatch && $this->stopwatch->isStarted('solr')) {
            $this->stopwatch->stop('solr');
        }

        $this->log($event->getRequest(), $event->getResponse(), $event->getEndpoint(), $endTime);

        $this->currentRequest = null;
        $this->currentStartTime = null;
        $this->currentEndpoint = null;
    }

    public function failCurrentRequest(): void
    {
        $endTime = microtime(true) - $this->currentStartTime;

        if (null !== $this->stopwatch && $this->stopwatch->isStarted('solr')) {
            $this->stopwatch->stop('solr');
        }

        $this->log($this->currentRequest, null, $this->currentEndpoint, $endTime);

        $this->currentRequest = null;
        $this->currentStartTime = null;
        $this->currentEndpoint = null;
    }

    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize($data): void
    {
        $this->data = unserialize($data);
    }

    public function reset(): void
    {
        $this->data = [];
        $this->queries = [];
    }
}
