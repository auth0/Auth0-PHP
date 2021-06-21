<?php

declare(strict_types=1);

namespace Auth0\SDK\API;

use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Utility\HttpClient;
use Auth0\SDK\Utility\HttpRequest;
use Auth0\SDK\Utility\HttpResponse;
use Auth0\SDK\Utility\HttpResponsePaginator;

/**
 * Class Management
 */
final class Management
{
    /**
     * Instance of SdkConfiguration, for shared configuration across classes.
     */
    private SdkConfiguration $configuration;

    /**
     * Instance of Auth0\SDK\API\Utility\HttpClient
     */
    private HttpClient $httpClient;

    /**
     * Cache of Management singletons
     *
     * @var array<object>
     */
    private array $instances = [];

    /**
     * Management constructor.
     *
     * @param SdkConfiguration|array<mixed> $configuration Required. Base configuration options for the SDK. See the SdkConfiguration class constructor for options.
     *
     * @psalm-suppress DocblockTypeContradiction
     */
    public function __construct(
        $configuration
    ) {
        // If we're passed an array, construct a new SdkConfiguration from that structure.
        if (is_array($configuration)) {
            $configuration = new SdkConfiguration($configuration);
        }

        // We only accept an SdkConfiguration type.
        if (! $configuration instanceof SdkConfiguration) {
            throw \Auth0\SDK\Exception\ConfigurationException::requiresConfiguration();
        }

        // Store the configuration internally.
        $this->configuration = & $configuration;

        // Retrieve any configured management token.
        $managementToken = $configuration->getManagementToken();

        // PSR-6 cache to use for management access token caching.
        $cache = $configuration->getManagementTokenCache();

        // If no token was provided, try to get one from cache.
        if ($managementToken === null) {
            if ($cache !== null) {
                $item = $cache->getItem('managementAccessToken');
                if ($item->isHit()) {
                    $managementToken = $item->get();
                }
            }
        }

        // If no token was provided or available from cache, try to get one.
        if ($managementToken === null) {
            $auth = new Authentication($configuration);
            $response = $auth->clientCredentials(['audience' => $configuration->buildDomainUri() . '/api/v2/']);

            if (HttpResponse::wasSuccessful($response)) {
                $response = HttpResponse::decodeContent($response);

                if (isset($response['access_token'])) {
                    $managementToken = $response['access_token'];

                    // If cache is available, store the token.
                    if ($cache !== null) {
                        $cachedKey = $cache->getItem('managementAccessToken');
                        $cachedKey->set($managementToken);
                        $cachedKey->expiresAfter((int) ($response['expires_in'] ?? 3600));

                        $cache->save($cachedKey);
                    }
                }
            }
        }

        // No management token could be acquired.
        if ($managementToken === null) {
            throw \Auth0\SDK\Exception\ConfigurationException::requiresManagementToken();
        }

        // Build the API client using the management token.
        $this->httpClient = new HttpClient($this->configuration, '/api/v2/', ['Authorization' => 'Bearer ' . (string) $managementToken]);
    }

    /**
     * Magic method for creating management class instances.
     *
     * @param string       $functionName The name of the magic function being invoked.
     * @param array<mixed> $arguments    Any arguments being passed to the magic function.
     *
     * @return mixed|void
     */
    public function __call(
        string $functionName,
        array $arguments
    ) {
        $classes = [
            'blacklists' => 'Blacklists',
            'clients' => 'Clients',
            'clientGrants' => 'ClientGrants',
            'connections' => 'Connections',
            'deviceCredentials' => 'DeviceCredentials',
            'emails' => 'Emails',
            'emailTemplates' => 'EmailTemplates',
            'grants' => 'Grants',
            'guardian' => 'Guardian',
            'jobs' => 'Jobs',
            'logs' => 'Logs',
            'logStreams' => 'LogStreams',
            'organizations' => 'Organizations',
            'roles' => 'Roles',
            'rules' => 'Rules',
            'resourceServers' => 'ResourceServers',
            'stats' => 'Stats',
            'tenants' => 'Tenants',
            'tickets' => 'Tickets',
            'userBlocks' => 'UserBlocks',
            'users' => 'Users',
            'usersByEmail' => 'UsersByEmail',
        ];

        if (isset($classes[$functionName])) {
            if (! isset($this->instances[$functionName])) {
                $className = 'Auth0\SDK\API\Management\\' . $classes[$functionName];
                $this->instances[$functionName] = new $className($this->httpClient);
            }

            return $this->instances[$functionName];
        }

        return;
    }

    /**
     * Return the HttpClient instance being used for management API requests.
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * Return an instance of HttpRequest representing the last issued request.
     */
    public function getLastRequest(): ?HttpRequest
    {
        return $this->httpClient->getLastRequest();
    }

    /**
     * Return a ResponsePaginator instance configured for the last HttpRequest.
     */
    public function getResponsePaginator(): HttpResponsePaginator
    {
        return new HttpResponsePaginator($this->httpClient);
    }
}
