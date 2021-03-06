<?php

declare(strict_types=1);

namespace Auth0\SDK\API\Management;

use Auth0\SDK\Utility\Request\RequestOptions;
use Auth0\SDK\Utility\Shortcut;
use Auth0\SDK\Utility\Validate;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Connections.
 * Handles requests to the Connections endpoint of the v2 Management API.
 *
 * @link https://auth0.com/docs/api/management/v2#!/Connections
 */
final class Connections extends ManagementEndpoint
{
    /**
     * Create a new Connection.
     * Required scope: `create:connections`
     *
     * @param string              $name     The name of the new connection.
     * @param string              $strategy The identity provider identifier for the new connection.
     * @param array<string>|null  $body     Additional body content to pass with the API request. See @link for supported options.
     * @param RequestOptions|null $options  Optional. Additional request options to use, such as a field filtering or pagination. (Not all endpoints support these. See @link for supported options.)
     *
     * @throws \Auth0\SDK\Exception\NetworkException When the API request fails due to a network error.
     *
     * @link https://auth0.com/docs/api/management/v2#!/Connections/post_connections
     */
    public function create(
        string $name,
        string $strategy,
        ?array $body = null,
        ?RequestOptions $options = null
    ): ResponseInterface {
        Validate::string($name, 'name');
        Validate::string($strategy, 'strategy');

        $body = Shortcut::mergeArrays([
            'name' => $name,
            'strategy' => $strategy,
        ], $body);

        return $this->getHttpClient()->method('post')
            ->addPath('connections')
            ->withBody((object) $body)
            ->withOptions($options)
            ->call();
    }

    /**
     * Get connection(s).
     * Required scope: `read:connections`
     *
     * @param array<int|string|null>|null $parameters Optional. Additional query parameters to pass with the API request. See @link for supported options.
     * @param RequestOptions|null         $options    Optional. Additional request options to use, such as a field filtering or pagination. (Not all endpoints support these. See @link for supported options.)
     *
     * @throws \Auth0\SDK\Exception\NetworkException When the API request fails due to a network error.
     *
     * @link https://auth0.com/docs/api/management/v2#!/Connections/get_connections
     */
    public function getAll(
        ?array $parameters = null,
        ?RequestOptions $options = null
    ): ResponseInterface {
        return $this->getHttpClient()->method('get')
            ->addPath('connections')
            ->withParams($parameters ?? [])
            ->withOptions($options)
            ->call();
    }

    /**
     * Get a single Connection.
     * Required scope: `read:connections`
     *
     * @param string              $id      Connection (by it's ID) to query.
     * @param RequestOptions|null $options Optional. Additional request options to use, such as a field filtering or pagination. (Not all endpoints support these. See @link for supported options.)
     *
     * @throws \Auth0\SDK\Exception\NetworkException When the API request fails due to a network error.
     *
     * @link https://auth0.com/docs/api/management/v2#!/Connections/get_connections_by_id
     */
    public function get(
        string $id,
        ?RequestOptions $options = null
    ): ResponseInterface {
        Validate::string($id, 'id');

        return $this->getHttpClient()->method('get')
            ->addPath('connections', $id)
            ->withOptions($options)
            ->call();
    }

    /**
     * Update a Connection.
     * Required scope: `update:connections`
     *
     * @param string              $id      Connection (by it's ID) to update.
     * @param array<mixed>|null   $body    Additional body content to pass with the API request. See @link for supported options.
     * @param RequestOptions|null $options Optional. Additional request options to use, such as a field filtering or pagination. (Not all endpoints support these. See @link for supported options.)
     *
     * @throws \Auth0\SDK\Exception\NetworkException When the API request fails due to a network error.
     *
     * @link https://auth0.com/docs/api/management/v2#!/Connections/patch_connections_by_id
     */
    public function update(
        string $id,
        ?array $body = null,
        ?RequestOptions $options = null
    ): ResponseInterface {
        Validate::string($id, 'id');

        $body = $body ?? [];

        return $this->getHttpClient()->method('patch')
            ->addPath('connections', $id)
            ->withBody((object) $body)
            ->withOptions($options)
            ->call();
    }

    /**
     * Delete a Connection.
     * Required scope: `delete:connections`
     *
     * @param string              $id      Connection (by it's ID) to delete.
     * @param RequestOptions|null $options Optional. Additional request options to use, such as a field filtering or pagination. (Not all endpoints support these. See @link for supported options.)
     *
     * @throws \Auth0\SDK\Exception\NetworkException When the API request fails due to a network error.
     *
     * @link https://auth0.com/docs/api/management/v2#!/Connections/delete_connections_by_id
     */
    public function delete(
        string $id,
        ?RequestOptions $options = null
    ): ResponseInterface {
        Validate::string($id, 'id');

        return $this->getHttpClient()->method('delete')
            ->addPath('connections', $id)
            ->withOptions($options)
            ->call();
    }

    /**
     * Delete a specific User for a Connection.
     * Required scope: `delete:users`
     *
     * @param string              $id      Connection (by it's ID)
     * @param string              $email   Email of the user to delete.
     * @param RequestOptions|null $options Optional. Additional request options to use, such as a field filtering or pagination. (Not all endpoints support these. See @link for supported options.)
     *
     * @throws \Auth0\SDK\Exception\NetworkException When the API request fails due to a network error.
     *
     * @link https://auth0.com/docs/api/management/v2#!/Connections/delete_users_by_email
     */
    public function deleteUser(
        string $id,
        string $email,
        ?RequestOptions $options = null
    ): ResponseInterface {
        Validate::string($id, 'id');
        Validate::email($email, 'email');

        return $this->getHttpClient()->method('delete')
            ->addPath('connections', $id, 'users')
            ->withParam('email', $email)
            ->withOptions($options)
            ->call();
    }
}
