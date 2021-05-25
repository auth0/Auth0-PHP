<?php

declare(strict_types=1);

namespace Auth0\SDK\Utility;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class HttpResponse
 */
class HttpResponse
{
    /**
     * Returns true when the ResponseInterface identifies a 200 status code; otherwise false.
     *
     * @param ResponseInterface $response           A ResponseInterface instance to extract from.
     * @param int               $expectedStatusCode Optional. The status code expected to consider the request successful. Defaults to 200.
     */
    public static function wasSuccessful(
        ResponseInterface $response,
        int $expectedStatusCode = 200
    ): bool {
        return $response->getStatusCode() === $expectedStatusCode;
    }

    /**
     * Extract the status code from an HTTP response (ResponseInterface).
     *
     * @param ResponseInterface $response A ResponseInterface instance to extract from.
     */
    public static function getStatusCode(
        ResponseInterface $response
    ): int {
        return $response->getStatusCode();
    }

    /**
     * Extract the headers from an HTTP response (ResponseInterface).
     *
     * @param ResponseInterface $response A ResponseInterface instance to extract from.
     */
    public static function getHeaders(
        ResponseInterface $response
    ): array {
        return $response->getHeaders();
    }

    /**
     * Extract the content from an HTTP response (ResponseInterface).
     *
     * @param ResponseInterface $response A ResponseInterface instance to extract from.
     */
    public static function getContent(
        ResponseInterface $response
    ): string {
        $body = $response->getBody();

        // True response bodies are of type StreamInterface and need transformed to strings.
        if ($body instanceof StreamInterface) {
            return $body->__toString();
        }

        // Simplification for mocked responses.
        if (is_string($body)) {
            return $body;
        }

        return '';
    }

    /**
     * Extract the content from an HTTP response and parse as JSON (ResponseInterface).
     *
     * @param ResponseInterface $response A ResponseInterface instance to extract from.
     */
    public static function decodeContent(
        ResponseInterface $response
    ) {
        return json_decode(self::getContent($response), true);
    }
}
