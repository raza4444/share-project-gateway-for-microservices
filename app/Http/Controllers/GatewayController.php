<?php

namespace App\Http\Controllers;

use App\Exceptions\DataFormatException;
use App\Exceptions\NotImplementedException;
use App\Http\Request;
use App\Routing\RouteRegistry;
use App\Services\RestClient;

/**
 * Class GatewayController
 * @package App\Http\Controllers
 */
class GatewayController extends ApiController
{

    /**
     * @var array ActionContract
     */
    protected $actions;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var PresenterContract
     */
    protected $presenter;

    /**
     * GatewayController constructor.
     * @param Request $request
     * @throws DataFormatException
     */
    private function prepareRequest(Request $request)
    {

        if (empty($request->getRoute())) throw new DataFormatException('Unable to find original URI pattern');

        $this->config = $request
            ->getRoute()
            ->getConfig();

        $this->actions = $request
            ->getRoute()
            ->getActions()
            ->groupBy(function ($action) {
                return $action->getSequence();
            })
            ->sortBy(function ($batch, $key) {
                return intval($key);
            });

        $this->presenter = $request
            ->getRoute()
            ->getPresenter();
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return \GuzzleHttp\Psr7\Response|Response
     * @throws NotImplementedException
     * @throws \App\Exceptions\UnableToExecuteRequestException
     */
    public function get(Request $request, RestClient $client)
    {
        $this->prepareRequest($request);

        if (!$request->getRoute()->isAggregate()) return $this->simpleRequest($request, $client);

        $parametersJar = array_merge($request->getRouteParams(), ['query_string' => $request->getQueryString()]);

        $output = $this->actions->reduce(function ($carry, $batch) use (&$parametersJar, $client) {
            $responses = $client->asyncRequest($batch, $parametersJar);
            $parametersJar = array_merge($parametersJar, $responses->exportParameters());
            return array_merge($carry, $responses->getResponses()->toArray());
        }, []);

        return $this->presenter->format($this->rearrangeKeys($output), 200);
    }

    /**
     * @param array $output
     * @return array
     */
    private function rearrangeKeys(array $output)
    {
        return collect(array_keys($output))->reduce(function ($carry, $alias) use ($output) {
            $key = $this->config['actions'][$alias]['output_key'] ?? $alias;

            if ($key === false) return $carry;

            $data = isset($this->config['actions'][$alias]['input_key']) ? $output[$alias][$this->config['actions'][$alias]['input_key']] : $output[$alias];

            if (empty($key)) {
                return array_merge($carry, $data);
            }

            if (is_string($key)) {
                array_set($carry, $key, $data);
            }

            if (is_array($key)) {
                collect($key)->each(function ($outputKey, $property) use (&$data, &$carry, $key) {
                    if ($property == '*') {
                        array_set($carry, $outputKey, $data);
                        return;
                    }

                    if (isset($data[$property])) {
                        array_set($carry, $outputKey, $data[$property]);
                        unset($data[$property]);
                    }
                });
            }
            return $carry;
        }, []);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return \GuzzleHttp\Psr7\Response|Response
     * @throws NotImplementedException
     * @throws \App\Exceptions\UnableToExecuteRequestException
     */
    public function delete(Request $request, RestClient $client)
    {
        $this->prepareRequest($request);
        return $this->simpleRequest($request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return \GuzzleHttp\Psr7\Response|Response
     * @throws NotImplementedException
     * @throws \App\Exceptions\UnableToExecuteRequestException
     */
    public function post(Request $request, RestClient $client)
    {
        $this->prepareRequest($request);
        return $this->simpleRequest($request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return \GuzzleHttp\Psr7\Response|Response
     * @throws NotImplementedException
     * @throws \App\Exceptions\UnableToExecuteRequestException
     */
    public function put(Request $request, RestClient $client)
    {
        $this->prepareRequest($request);
        return $this->simpleRequest($request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return \GuzzleHttp\Psr7\Response|Response
     * @throws NotImplementedException
     * @throws \App\Exceptions\UnableToExecuteRequestException
     */
    public function patch(Request $request, RestClient $client)
    {
        $this->prepareRequest($request);
        return $this->simpleRequest($request, $client);
    }

    /**
     * @param Request $request
     * @param RestClient $client
     * @return \GuzzleHttp\Psr7\Response| Response
     * @throws NotImplementedException
     * @throws \App\Exceptions\UnableToExecuteRequestException
     *
     */
    private function simpleRequest(Request $request, RestClient $client)
    {
        if ($request->getRoute()->isAggregate()) throw new NotImplementedException('Aggregate ' . strtoupper($request->method()) . 's are not implemented yet');

        $client->setBody($request->getContent());

        if (count($request->allFiles()) > 0) {
            $client->setFiles($request->allFiles());
            $client->setMultiPartParams($request->all());
        }

        $parameters = array_merge($request->getRouteParams(), ['query_string' => $request->getQueryString()]);
        //dd($this->actions->first()->first(), $parameters);
        $response = $client->syncRequest($this->actions->first()->first(), $parameters);

        return $this->presenter->format((string)$response->getBody(), $response->getStatusCode());
    }

}
