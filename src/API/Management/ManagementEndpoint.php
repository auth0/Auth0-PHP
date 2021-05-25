<?php

declare(strict_types=1);

namespace Auth0\SDK\API\Management;

use Auth0\SDK\Utility\HttpClient;

/**
 * Class ManagementEndpoint.
 * Extended by Management API endpoints classes.
 */
abstract class ManagementEndpoint
{
    /**
     * Injected ApiClient instance to use.
     */
    protected HttpClient $apiClient;

    /**
     * ManagementEndpoint constructor.
     *
     * @param ApiClient $apiClient ApiClient instance to use.
     */
    public function __construct(
        HttpClient $apiClient
    ) {
        $this->apiClient = $apiClient;
    }

    /**
     * Get the injected ApiClient instance.
     */
    public function getApiClient(): HttpClient
    {
        return $this->apiClient;
    }

    /**
     * Check for invalid permissions with an array of permissions.
     *
     * @param array $permissions Permissions array to check.
     *
     * @throws \Auth0\SDK\Exception\InvalidPermissionsArrayException If permissions are empty or do not contain the necessary keys.
     */
    protected function validatePermissions(
        array $permissions
    ): void {
        if (! count($permissions)) {
            throw new \Auth0\SDK\Exception\InvalidPermissionsArrayException();
        }

        foreach ($permissions as $permission) {
            if (! isset($permission['permission_name'])) {
                throw new \Auth0\SDK\Exception\InvalidPermissionsArrayException();
            }

            if (! isset($permission['resource_server_identifier'])) {
                throw new \Auth0\SDK\Exception\InvalidPermissionsArrayException();
            }
        }
    }

    /**
     * Check that a variable is a string and is not empty.
     *
     * @param string $variable     The variable to check.
     * @param string $variableName The variable name.
     *
     * @throws \Auth0\SDK\Exception\EmptyOrInvalidParameterException If $var is empty or is not a string.
     */
    protected function validateString(
        string $variable,
        string $variableName
    ): void {
        if (! mb_strlen($variable)) {
            throw new \Auth0\SDK\Exception\EmptyOrInvalidParameterException($variableName);
        }
    }

    /**
     * Check that a variable contains a valid email address.
     *
     * @param string $email        The email to check.
     * @param string $variableName The variable name.
     *
     * @throws \Auth0\SDK\Exception\EmptyOrInvalidParameterException If $var is empty or is not a string.
     */
    protected function validateEmail(
        string $email,
        string $variableName
    ): void {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Auth0\SDK\Exception\EmptyOrInvalidParameterException($variableName);
        }
    }

    /**
     * Check that a variable is an array and is not empty.
     *
     * @param array  $variable     The variable to check.
     * @param string $variableName The variable name.
     *
     * @throws \Auth0\SDK\Exception\EmptyOrInvalidParameterException If $var is empty or is not a string.
     */
    protected function validateArray(
        array $variable,
        string $variableName
    ): void {
        if (! count($variable)) {
            throw new \Auth0\SDK\Exception\EmptyOrInvalidParameterException($variableName);
        }
    }
}
