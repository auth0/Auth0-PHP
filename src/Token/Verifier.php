<?php

declare(strict_types=1);

namespace Auth0\SDK\Token;

use Auth0\SDK\API\Helpers\RequestBuilder;
use Auth0\SDK\Token;
use OpenSSLAsymmetricKey;
use Psr\SimpleCache\CacheInterface;

/**
 * Class Verifier.
 */
class Verifier
{
    /**
     * A string representing the headers and claims portions of a JWT.
     */
    protected string $payload;

    /**
     * A string representing the signature portion of a JWT.
     */
    protected string $signature;

    /**
     * An array of the headers for the JWT. Expects an 'alg' header, and in the case of RS256, a 'kid' header.
     */
    protected array $headers;

    /**
     * Client Secret found in the Application settings for verifying HS256 tokens.
     */
    protected ?string $clientSecret = null;

    /**
     * Algorithm to use for verification. Expects either RS256 or HS256. Defaults to RS256.
     */
    protected ?string $algorithm = null;

    /**
     * URI to the JWKS when verifying RS256 tokens.
     */
    protected ?string $jwksUri = null;

    /**
     * Time in seconds to keep JWKS records cached. Defaults to 60.
     */
    protected ?int $cacheExpires = null;

    /**
     * An PSR-6 ("SimpleCache") CacheInterface instance to cache JWKS results within.
     */
    protected ?CacheInterface $cache = null;

    /**
     * Constructor for the Token Verifier class.
     *
     * @param string $payload A string representing the headers and claims portions of a JWT.
     * @param string $signature A string representing the signature portion of a JWT.
     * @param array $headers An array of the headers for the JWT. Expects an 'alg' header, and in the case of RS256, a 'kid' header.
     * @param string|null $algorithm Optional. Algorithm to use for verification. Expects either RS256 or HS256. Defaults to RS256.
     * @param string|null $jwksUri Optional. URI to the JWKS when verifying RS256 tokens.
     * @param string|null $clientSecret Optional. Client Secret found in the Application settings for verifying HS256 tokens.
     * @param int|null $cacheExpires Optional. Time in seconds to keep JWKS records cached.
     * @param CacheInterface|null $cache Optional. A PSR-6 ("SimpleCache") CacheInterface instance to cache JWKS results within.
     */
    public function __construct(
        string $payload,
        string $signature,
        array $headers,
        ?string $algorithm = null,
        ?string $jwksUri = null,
        ?string $clientSecret = null,
        ?int $cacheExpires = null,
        ?CacheInterface $cache = null
    ) {
        $this->payload = $payload;
        $this->signature = $signature;
        $this->headers = $headers;
        $this->algorithm = $algorithm;
        $this->jwksUri = $jwksUri;
        $this->clientSecret = $clientSecret;
        $this->cacheExpires = $cacheExpires;
        $this->cache = $cache;

        $this->verify();
    }

    /**
     * Verify the token signature.
     *
     * @throws InvalidTokenException When signature verification fails. See exception message for details.
     */
    public function verify(): self
    {
        $alg = $this->headers['alg'] ?? null;

        if ($this->algorithm && ($this->algorithm !== $alg)) {
            throw \Auth0\SDK\Exception\InvalidTokenException::unexpectedSigningAlgorithm($this->algorithm, $alg);
        }

        if ($alg === Token::ALGO_RS256) {
            $kid = $this->headers['kid'] ?? null;

            if ($kid === null) {
                throw \Auth0\SDK\Exception\InvalidTokenException::missingKidHeader();
            }

            $key = $this->getKey($kid);
            $valid = openssl_verify($this->payload, $this->signature, $key, OPENSSL_ALGO_SHA256);
            $this->freeKey($key);

            if ($valid !== 1) {
                throw \Auth0\SDK\Exception\InvalidTokenException::badSignature();
            }

            return $this;
        }

        if ($alg === Token::ALGO_HS256) {
            if (! $this->clientSecret) {
                throw \Auth0\SDK\Exception\InvalidTokenException::requiresClientSecret();
            }

            $hash = hash_hmac('sha256', $this->payload, $this->clientSecret, true);
            $valid = hash_equals($this->signature, $hash);

            if (! $valid) {
                throw \Auth0\SDK\Exception\InvalidTokenException::badSignature();
            }

            return $this;
        }

        throw \Auth0\SDK\Exception\InvalidTokenException::unsupportedSigningAlgorithm($alg);
    }

    /**
     * Query a JWKS endpoint and return an array representing the key set.
     *
     * @throws InvalidTokenException When the JWKS uri is not properly configured, or is unreachable.
     */
    protected function getKeySet(): array
    {
        if ($this->jwksUri === null) {
            throw \Auth0\SDK\Exception\InvalidTokenException::requiresJwksUri();
        }

        $jwksCacheKey = md5($this->jwksUri);
        $jwksUri = parse_url($this->jwksUri);

        $scheme = $jwksUri['scheme'] ?? 'https';
        $path = $jwksUri['path'] ?? '/.well-known/jwks.json';
        $response = [];

        if ($this->cache !== null) {
            $cached = $this->cache->get($jwksCacheKey);

            if ($cached !== null) {
                return $cached;
            }
        }

        $keys = (new RequestBuilder([
            'method' => 'get',
            'domain' => $scheme . '://' . $jwksUri['host'],
            'basePath' => $path,
        ]))->call();

        if (is_array($keys) && isset($keys['keys']) && count($keys['keys'])) {
            foreach ($keys['keys'] as $key) {
                if (isset($key['kid']) && isset($key['x5c']) && is_array($key['x5c']) && count($key['x5c'])) {
                    $response[$key['kid']] = $key;
                }
            }
        }

        if (count($response) && $this->cache !== null) {
            $this->cache->set($jwksCacheKey, $response, $this->cacheExpires ?? 60);
        }

        return $response;
    }

    /**
     * Query a JWKS endpoint for a matching key. Parse and return a OpenSSLAsymmetricKey suitable for verification.
     *
     * @param string $kid The 'kid' header value to use for key lookup.
     *
     * @throws InvalidTokenException When unable to retrieve key. See error message for details.
     */
    protected function getKey(
        string $kid
    ): OpenSSLAsymmetricKey {
        $keys = $this->getKeySet();

        if (! isset($keys[$kid])) {
            throw \Auth0\SDK\Exception\InvalidTokenException::badSignatureMissingKid();
        }

        $key = openssl_pkey_get_public("-----BEGIN CERTIFICATE-----\n" . chunk_split($keys[$kid]['x5c'][0], 64, "\n") . '-----END CERTIFICATE-----');

        if (is_bool($key)) {
            throw \Auth0\SDK\Exception\InvalidTokenException::badSignature();
        }

        $details = openssl_pkey_get_details($key);

        if ($details['type'] !== OPENSSL_KEYTYPE_RSA) {
            throw \Auth0\SDK\Exception\InvalidTokenException::badSignatureIncompatibleAlgorithm();
        }

        return $key;
    }

    /**
     * Free key resource in PHP <8.0.
     *
     * @param mixed $key An instance of OpenSSLAsymmetricKey (PHP 8.0+) or 'resource' (PHP <8.0).
     */
    protected function freeKey(
        $key
    ): void {
        // openssl_free_key is deprecated in PHP 8.0, so avoid calling it there:
        if ($key instanceof \OpenSSLAsymmetricKey) {
            return;
        }

        // TODO: Remove when PHP 7.x support is EOL
        openssl_free_key($key);
    }
}