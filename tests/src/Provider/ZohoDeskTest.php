<?php

namespace Bybrand\OAuth2\Client\Test\Provider;

use PHPUnit\Framework\TestCase;
use Bybrand\OAuth2\Client\Provider\ZohoDesk;
use Mockery as m;

class ZohoDeskTest extends TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new ZohoDesk([
            'clientId'     => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri'  => 'none',
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @group Zoho
     * @group Zoho.AuthorizationUrl
     */
    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    /**
     * @group Zoho
     * @group Zoho.GetAuthorizationUrl
     */
    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/v2/auth', $uri['path']);
    }

    /**
     * @group Zoho
     * @group Zoho.GetBaseAccessTokenUrl
     */
    public function testGetBaseAccessTokenUrl()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/oauth/v2/token', $uri['path']);
    }

    /**
     * @group Zoho
     * @group Zoho.GetAccessToken
     */
    public function testGetAccessToken()
    {
        $json = [
            'access_token'   => 'mock_access_token',
            'refresh_token'  => 'mock_refresh_token',
            // 'expires_in_sec' => 3600,
            'token_type'     => 'Bearer',
            'expires_in'     => 3600,
            'access_type'  => 'offline', // To refresh token
        ];

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(json_encode($json));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);

        $this->provider->setHttpClient($client);

        $token = $this->provider
            ->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
    }

    /**
     * @group Zoho
     * @group Zoho.GetResourceOwner
     */
    public function testGetResourceOwner()
    {
        $accessTokenJson = [
            'access_token'   => 'mock_access_token',
            'refresh_token'  => 'mock_refresh_token',
            'token_type'     => 'Bearer',
            'expires_in'     => 3600
        ];

        // Set organization Id.
        $organizationId = uniqid();

        $userJson['data'][] = [
            'id'               => $organizationId,
            'organizationName' => 'zylker'
        ];

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(json_encode($accessTokenJson));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn(json_encode($userJson));
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(2)->andReturn($response, $userResponse);

        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $organization = $this->provider->getResourceOwner($token);

        $this->assertEquals($organizationId, $organization->getId());
        $this->assertEquals('zylker', $organization->getOrganizationName());
    }
}
