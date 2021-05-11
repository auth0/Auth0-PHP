<?php

declare(strict_types=1);

namespace Auth0\Tests\Unit\Helpers;

use Auth0\SDK\Helpers\TransientStoreHandler;
use Auth0\SDK\Store\SessionStore;
use PHPUnit\Framework\TestCase;

/**
 * Class TransientStoreHandlerTest.
 */
class TransientStoreHandlerTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        $_SESSION = [];
    }

    public function testThatTransientIsStored(): void
    {
        $sessionStore = new SessionStore('test_store');
        $transientStore = new TransientStoreHandler($sessionStore);
        $transientStore->store('test_store_key', '__test_store_value__');

        $this->assertEquals('__test_store_value__', $_SESSION['test_store_test_store_key']);
    }

    public function testThatTransientIsIssued(): void
    {
        $sessionStore = new SessionStore('test_store');
        $transientStore = new TransientStoreHandler($sessionStore);
        $issuedValue = $transientStore->issue('test_issue_key');

        $this->assertEquals($issuedValue, $_SESSION['test_store_test_issue_key']);
        $this->assertGreaterThanOrEqual(16, strlen($issuedValue));
    }

    public function testThatTransientIsGottenOnce(): void
    {
        $sessionStore = new SessionStore('test_store');
        $transientStore = new TransientStoreHandler($sessionStore);
        $transientStore->store('test_get_key', '__test_get_value__');

        $this->assertEquals('__test_get_value__', $transientStore->getOnce('test_get_key'));
        $this->assertNull($transientStore->getOnce('test_get_key'));
        $this->assertArrayNotHasKey('test_store_test_get_key', $_SESSION);
    }

    public function testThatTransientIsVerified(): void
    {
        $sessionStore = new SessionStore('test_store');
        $transientStore = new TransientStoreHandler($sessionStore);
        $transientStore->store('test_verify_key', '__test_get_value__');

        $this->assertTrue($transientStore->verify('test_verify_key', '__test_get_value__'));
        $this->assertFalse($transientStore->verify('test_verify_key', '__test_get_value__'));
        $this->assertNull($transientStore->getOnce('test_verify_key'));
        $this->assertArrayNotHasKey('test_store_test_verify_key', $_SESSION);
    }

    public function testThatTransientIssetReturnsCorrectly(): void
    {
        $sessionStore = new SessionStore('test_store');
        $transientStore = new TransientStoreHandler($sessionStore);

        $this->assertFalse($transientStore->isset('test_verify_key'));

        $transientStore->store('test_verify_key', '__test_get_value__');

        $this->assertTrue($transientStore->isset('test_verify_key'));

        $transientStore->getOnce('test_verify_key');

        $this->assertFalse($transientStore->isset('test_verify_key'));
    }
}
