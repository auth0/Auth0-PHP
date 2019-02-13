<?php
namespace Auth0\Tests\API\Authentication;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Helpers\InformationHeaders;

class UrlBuildersTest extends \PHPUnit_Framework_TestCase
{

    public static $telemetry;

    public static $telemetryParam;

    public static function setUpBeforeClass()
    {
        $infoHeadersData = new InformationHeaders;
        $infoHeadersData->setCorePackage();

        self::$telemetry      = urlencode( $infoHeadersData->build() );
        self::$telemetryParam = 'auth0Client='.self::$telemetry;
    }

    public function testThatBasicAuthorizeLinkIsBuiltCorrectly()
    {
        $api = new Authentication('test-domain.auth0.com', '__test_client_id__');

        $authorize_url       = $api->get_authorize_link('code', 'https://example.com/cb');
        $authorize_url_parts = parse_url( $authorize_url );

        $this->assertEquals('https', $authorize_url_parts['scheme']);
        $this->assertEquals('test-domain.auth0.com', $authorize_url_parts['host']);
        $this->assertEquals('/authorize', $authorize_url_parts['path']);

        $authorize_url_query = explode( '&', $authorize_url_parts['query'] );
        $this->assertContains('redirect_uri=https%3A%2F%2Fexample.com%2Fcb', $authorize_url_query);
        $this->assertContains('response_type=code', $authorize_url_query);
        $this->assertContains('client_id=__test_client_id__', $authorize_url_query);
        $this->assertContains(self::$telemetryParam, $authorize_url_query);
        $this->assertNotContains('connection=', $authorize_url_parts['query']);
        $this->assertNotContains('state=', $authorize_url_parts['query']);
    }

    public function testThatAuthorizeLinkIncludesConnection()
    {
        $api = new Authentication('test-domain.auth0.com', '__test_client_id__');

        $authorize_url       = $api->get_authorize_link('code', 'https://example.com/cb', 'test-connection');
        $authorize_url_query = parse_url( $authorize_url, PHP_URL_QUERY );
        $authorize_url_query = explode( '&', $authorize_url_query );

        $this->assertContains('connection=test-connection', $authorize_url_query);
        $this->assertContains(self::$telemetryParam, $authorize_url_query);
    }

    public function testThatAuthorizeLinkIncludesState()
    {
        $api = new Authentication('test-domain.auth0.com', '__test_client_id__');

        $authorize_url       = $api->get_authorize_link('code', 'https://example.com/cb', null, '__test_state__');
        $authorize_url_query = parse_url( $authorize_url, PHP_URL_QUERY );
        $authorize_url_query = explode( '&', $authorize_url_query );

        $this->assertContains('state=__test_state__', $authorize_url_query);
        $this->assertContains(self::$telemetryParam, $authorize_url_query);
    }

    public function testThatAuthorizeLinkIncludesAdditionalParams()
    {
        $api = new Authentication('test-domain.auth0.com', '__test_client_id__');

        $additional_params   = [ 'param1' => 'value1' ];
        $authorize_url       = $api->get_authorize_link('code', 'https://example.com/cb', null, null, $additional_params);
        $authorize_url_query = parse_url( $authorize_url, PHP_URL_QUERY );
        $authorize_url_query = explode( '&', $authorize_url_query );

        $this->assertContains('param1=value1', $authorize_url_query);
        $this->assertContains(self::$telemetryParam, $authorize_url_query);
    }

    public function testThatBasicLogoutLinkIsBuiltCorrectly()
    {
        $api = new Authentication('test-domain.auth0.com', '__test_client_id__');

        $logout_link_parts = parse_url($api->get_logout_link());

        $this->assertEquals('https', $logout_link_parts['scheme']);
        $this->assertEquals('test-domain.auth0.com', $logout_link_parts['host']);
        $this->assertEquals('/v2/logout', $logout_link_parts['path']);
        $this->assertEquals(self::$telemetryParam, $logout_link_parts['query']);
    }

    public function testThatReturnToLogoutLinkIsBuiltCorrectly()
    {
        $api = new Authentication('test-domain.auth0.com', '__test_client_id__');

        $logout_link_query = parse_url($api->get_logout_link('https://example.com/return-to'), PHP_URL_QUERY);
        $logout_link_query = explode( '&', $logout_link_query );

        $this->assertContains('returnTo=https%3A%2F%2Fexample.com%2Freturn-to', $logout_link_query);
        $this->assertContains(self::$telemetryParam, $logout_link_query);
    }

    public function testThatClientIdLogoutLinkIsBuiltCorrectly()
    {
        $api = new Authentication('test-domain.auth0.com', '__test_client_id__');

        $logout_link_query = parse_url($api->get_logout_link(null, '__test_client_id__'), PHP_URL_QUERY);
        $logout_link_query = explode( '&', $logout_link_query );

        $this->assertContains('client_id='.'__test_client_id__', $logout_link_query);
        $this->assertContains(self::$telemetryParam, $logout_link_query);
    }

    public function testThatFederatedLogoutLinkIsBuiltCorrectly()
    {
        $api = new Authentication('test-domain.auth0.com', '__test_client_id__');

        $logout_link_query = parse_url($api->get_logout_link(null, null, true), PHP_URL_QUERY);
        $logout_link_query = explode( '&', $logout_link_query );

        $this->assertContains('federated=', $logout_link_query);
        $this->assertContains(self::$telemetryParam, $logout_link_query);
    }
}