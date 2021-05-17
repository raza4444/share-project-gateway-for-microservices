<?php

namespace App\Services;

use App\Exceptions\UnableToExecuteRequestException;
use App\Routing\ActionContract;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Response as PsrResponse;
use App\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Class RestClient
 * @package App\Services
 */
class RestClient
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ServiceRegistryContract
     */
    protected $services;

    /**
     * @var array
     */
    protected $guzzleParams = [
        'headers' => [],
        'timeout' => 40
    ];

    /**
     * @var int
     */
    const USER_ID_ANONYMOUS = -1;

    /**
     * RestClient constructor.
     * @param Client $client
     * @param ServiceRegistryContract $services
     * @param Request $request
     */
    public function __construct(Client $client, ServiceRegistryContract $services, Request $request)
    {
        $this->client = $client;
        $this->services = $services;
        $this->injectHeaders($request);
    }

    /**
     * @param Request $request
     */
    private function injectHeaders(Request $request)
    {

        /*
        - iat (issued at time): Time at which the JWT was issued; can be used to determine age of the JWT
        - iss (issuer): Issuer of the JWT
        - exp (expiration time): Time after which the JWT expires
        - user (user detail) current user detail
        */

        $this->setHeaders(
            self::getCustomHeaders($request)
        );
    }

    /**
     * @param Request $request
     * @return array
     */
    public static function getCustomHeaders(Request $request){

        return [
            'SERVICE-KEY' => $request->header('SERVICE-KEY'),
            'X-Login-JWT' => self::getJWT(),
            'X-Token-Scopes' => $request->user() && !empty($request->user()->token()) ? implode(',', $request->user()->token()->scopes) : '',
            'X-Client-Ip' => $request->getClientIp(),
            'User-Agent' => $request->header('User-Agent'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'tz' => $request->hasHeader('tz') ? $request->header('tz') : env('APP_TZ', 'Europe/Amsterdam'),
            'locale' => $request->hasHeader('locale') ? $request->header('locale') : env('APP_LANG', 'nl'),
        ];
    }

    /**
     * @return string
     */
    private static function getJWT()
    {
        $user = auth('api')->user();
        $payload = [
            'iat' => time(),
            'iss' => 'zoofy-gateway',
            'exp' => time() + (15 * 60),
            'user' => $user,
        ];

        // TODO: pass all user roles
        if ($user) {
            $payload['user_role_id'] = $user->roles[0]->id;
        }
        return JWT::encode($payload, env('JWT_KEY', 'Av21j7LWfmsvJA0vUFNRoMrd'));
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->guzzleParams['headers'] = $headers;
    }

    /**
     * @param $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->guzzleParams['headers']['Content-Type'] = $contentType;

        return $this;
    }

    /**
     * @param $contentSize
     * @return $this
     */
    public function setContentSize($contentSize)
    {
        $this->guzzleParams['headers']['Content-Length'] = $contentSize;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->guzzleParams['headers'];
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->guzzleParams['body'] = $body;
        return $this;
    }

    /**
     * @param array $files
     * @return $this
     */
    public function setFiles($files)
    {
        global $request;
        // Get rid of everything else
        $headers = self::getCustomHeaders($request);
        unset($headers['Content-Type']);
        $this->setHeaders(array_intersect_key($this->getHeaders(), $headers));

        if (isset($this->guzzleParams['body'])) unset($this->guzzleParams['body']);

        $this->guzzleParams['timeout'] = 20;
        $this->guzzleParams['multipart'] = [];

        foreach ($files as $key => $file) {
            $this->guzzleParams['multipart'][] = [
                'name' => $key,
                'contents' => fopen($file->getRealPath(), 'r'),
                'filename' => $file->getClientOriginalName()
            ];
        }
        return $this;
    }

    /**
     * @param $request
     * @return $this
     */
    public function setMultiPartParams($request)
    {

        foreach ($request as $key => $param) {
            $this->guzzleParams['multipart'][] = [
                'name' => $key,
                'contents' => $param
            ];
        }

        return $this;
    }

    /**
     * @param $url
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function post($url)
    {
        return $this->client->post($url, $this->guzzleParams);
    }

    /**
     * @param $url
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function put($url)
    {
        return $this->client->put($url, $this->guzzleParams);
    }

    /**
     * @param $url
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function patch($url)
    {
        return $this->client->patch($url, $this->guzzleParams);
    }

    /**
     * @param $url
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($url)
    {
        return $this->client->get($url, $this->guzzleParams);
    }

    /**
     * @param $url
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete($url)
    {
        return $this->client->delete($url, $this->guzzleParams);
    }

    /**
     * @param Collection $batch
     * @param $parametersJar
     * @return RestBatchResponse
     */
    public function asyncRequest(Collection $batch, $parametersJar)
    {
        $wrapper = new RestBatchResponse();
        $wrapper->setCritical($batch->filter(function ($action) {
            return $action->isCritical();
        })->count());

        $promises = $batch->reduce(function ($carry, $action) use ($parametersJar) {
            $method = strtolower($action->getMethod());
            $url = $this->buildUrl($action, $parametersJar);
            $carry[$action->getAlias()] = $this->client->{$method . 'Async'}($url, $this->guzzleParams);
            return $carry;
        }, []);

        return $this->processResponses(
            $wrapper,
            collect(Promise\settle($promises)->wait())
        );
    }

    /**
     * @param RestBatchResponse $wrapper
     * @param Collection $responses
     * @return RestBatchResponse
     */
    private function processResponses(RestBatchResponse $wrapper, Collection $responses)
    {
        // Process successful responses
        $responses->filter(function ($response) {
            return $response['state'] == 'fulfilled';
        })->each(function ($response, $alias) use ($wrapper) {
            $wrapper->addSuccessfulAction($alias, $response['value']);
        });

        // Process failures
        $responses->filter(function ($response) {
            return $response['state'] != 'fulfilled';
        })->each(function ($response, $alias) use ($wrapper) {
            $response = $response['reason']->getResponse();
            if ($wrapper->hasCriticalActions()) throw new UnableToExecuteRequestException($response);

            // Do we have an error response from the service?
            if (!$response) $response = new PsrResponse(502, []);
            $wrapper->addFailedAction($alias, $response);
        });

        return $wrapper;
    }

    /**
     * @param ActionContract $action
     * @param array $parametersJar
     * @return PsrResponse
     * @throws UnableToExecuteRequestException
     */
    public function syncRequest(ActionContract $action, $parameters)
    {
        try {
            $response = $this->{strtolower($action->getMethod())}(
                $this->buildUrl($action, $parameters)
            );
        } catch (ConnectException $e) {
            throw new UnableToExecuteRequestException();
        } catch (RequestException $e) {
            return $e->getResponse();
        }

        return $response;
    }

    /**
     * @param string $url
     * @param array $params
     * @param string $prefix
     * @return string
     */
    private function injectParams($url, array $params, $prefix = '')
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $url = $this->injectParams($url, $value, $prefix . $key . '.');
            }

            if (is_string($value) || is_numeric($value)) {
                $url = str_replace("{" . $prefix . $key . "}", $value, $url);
            }
        }

        return $url;
    }

    /**
     * @param ActionContract $action
     * @param $parametersJar
     * @return string
     */
    private function buildUrl(ActionContract $action, $parametersJar)
    {
        $url = $this->injectParams($action->getUrl(), $parametersJar);
        if ($url[0] != '/') $url = '/' . $url;
        if (isset($parametersJar['query_string'])) $url .= '?' . $parametersJar['query_string'];
        return $action->getServiceUrl() . $url;
    }
}
