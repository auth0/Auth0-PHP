<?php

declare(strict_types=1);

namespace Auth0\SDK\API\Management;

use Auth0\SDK\Utility\Request\RequestOptions;
use Auth0\SDK\Utility\Validate;
use Psr\Http\Message\ResponseInterface;

/**
 * Class LogStreams.
 * Handles requests to the Log Streams endpoint of the v2 Management API.
 *
 * @link https://auth0.com/docs/api/management/v2#!/Log_Streams
 */
final class LogStreams extends ManagementEndpoint
{
    /**
     * Create a new Log Stream.
     * Required scope: `create:log_streams`
     *
     * @param string              $type    The type of log stream being created.
     * @param array<string>       $sink    The type of log stream determines the properties required in the sink payload; see the linked documentation.
     * @param string|null         $name    Optional. The name of the log stream.
     * @param RequestOptions|null $options Optional. Additional request options to use, such as a field filtering or pagination. (Not all endpoints support these. See @link for supported options.)
     *
     * @throws \Auth0\SDK\Exception\NetworkException When the API request fails due to a network error.
     *
     * @link https://auth0.com/docs/api/management/v2#!/Log_Streams/post_log_streams
     */
    public function create(
        string $type,
        array $sink,
        ?string $name = null,
        ?RequestOptions $options = null
    ): ResponseInterface {
        Validate::string($type, 'type');
        Validate::array($sink, 'sink');

        $payload = [
            'type' => $type,
            'sink' => (object) $sink,
        ];

        if ($name !== null) {
            $payload['name'] = $name;
        }

        return $this->getHttpClient()->method('post')
            ->addPath('log-streams')
            ->withBody((object) $payload)
            ->withOptions($options)
            ->call();
    }

    /**
     * Get all Log Streams.
     * Required scope: `read:log_streams`
     *
     * @param RequestOptions|null $options Optional. Additional request options to use, such as a field filtering or pagination. (Not all endpoints support these. See @link for supported options.)
     *
     * @throws \Auth0\SDK\Exception\NetworkException When the API request fails due to a network error.
     *
     * @link https://auth0.com/docs/api/management/v2#!/Log_Streams/get_log_streams
     */
    public function getAll(
        ?RequestOptions $options = null
    ): ResponseInterface {
        return $this->getHttpClient()->method('get')
            ->addPath('log-streams')
            ->withOptions($options)
            ->call();
    }

    /**
     * Get a single Log Stream.
     * Required scope: `read:log_streams`
     *
     * @param string              $id      Log Stream ID to query.
     * @param RequestOptions|null $options Optional. Additional request options to use, such as a field filtering or pagination. (Not all endpoints support these. See @link for supported options.)
     *
     * @throws \Auth0\SDK\Exception\NetworkException When the API request fails due to a network error.
     *
     * @link https://auth0.com/docs/api/management/v2#!/Log_Streams/get_log_streams_by_id
     */
    public function get(
        string $id,
        ?RequestOptions $options = null
    ): ResponseInterface {
        Validate::string($id, 'id');

        return $this->getHttpClient()->method('get')
            ->addPath('log-streams', $id)
            ->withOptions($options)
            ->call();
    }

    /**
     * Updates an existing Log Stream.
     * Required scope: `update:log_streams`
     *
     * @param string              $id      ID of the Log Stream to update.
     * @param array<mixed>        $body    Log Stream data to update. Only certain fields are update-able; see the linked documentation.
     * @param RequestOptions|null $options Optional. Additional request options to use, such as a field filtering or pagination. (Not all endpoints support these. See @link for supported options.)
     *
     * @throws \Auth0\SDK\Exception\NetworkException When the API request fails due to a network error.
     *
     * @link https://auth0.com/docs/api/management/v2#!/Log_Streams/patch_log_streams_by_id
     */
    public function update(
        string $id,
        array $body,
        ?RequestOptions $options = null
    ): ResponseInterface {
        Validate::string($id, 'id');
        Validate::array($body, 'body');

        return $this->getHttpClient()->method('patch')
            ->addPath('log-streams', $id)
            ->withBody((object) $body)
            ->withOptions($options)
            ->call();
    }

    /**
     * Deletes a Log Stream.
     * Required scope: `delete:log_streams`
     *
     * @param string              $id      ID of the Log Stream to delete.
     * @param RequestOptions|null $options Optional. Additional request options to use, such as a field filtering or pagination. (Not all endpoints support these. See @link for supported options.)
     *
     * @throws \Auth0\SDK\Exception\NetworkException When the API request fails due to a network error.
     *
     * @link https://auth0.com/docs/api/management/v2#!/Log_Streams/delete_log_streams_by_id
     */
    public function delete(
        string $id,
        ?RequestOptions $options = null
    ): ResponseInterface {
        Validate::string($id, 'id');

        return $this->getHttpClient()->method('delete')
            ->addPath('log-streams', $id)
            ->withOptions($options)
            ->call();
    }
}
