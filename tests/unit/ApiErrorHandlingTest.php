<?php

use App\Controllers\AuthController;
use App\Controllers\ChatbotController;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;

final class ApiErrorHandlingTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpControllerTestTrait();
    }

    public function testLoginInvalidCredentialsReturnsJsonForApiRequest(): void
    {
        $request = service('request');
        $request->setMethod('POST');
        $request->setGlobal('post', [
            'username' => 'invalid-user',
            'password' => 'wrong-password',
        ]);
        $request->setGlobal('server', [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $result = $this->withRequest($request)
            ->controller(AuthController::class)
            ->execute('login');

        $result->assertStatus(200);

        $json = $result->getJSON();

        $this->assertIsArray(json_decode($json, true));
        $this->assertFalse(json_decode($json, true)['success']);
        $this->assertNotEmpty(json_decode($json, true)['message'] ?? '');
    }

    public function testChatbotEmptyMessageReturnsJsonForApiRequest(): void
    {
        $request = service('request');
        $request->setMethod('POST');
        $request->setGlobal('post', []);
        $request->setGlobal('server', [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $result = $this->withRequest($request)
            ->controller(ChatbotController::class)
            ->execute('chat');

        $result->assertStatus(200);

        $json = $result->getJSON();

        $this->assertIsArray(json_decode($json, true));
        $this->assertFalse(json_decode($json, true)['success']);
    }

    public function testLandingChatbotRefusesOffTopicPrompt(): void
    {
        $request = service('request');
        $request->setMethod('POST');
        $request->setGlobal('post', [
            'message' => 'Tell me a joke about space',
            'source'  => 'landing',
        ]);
        $request->setGlobal('server', [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $result = $this->withRequest($request)
            ->controller(ChatbotController::class)
            ->execute('chat');

        $result->assertStatus(200);

        $payload = json_decode($result->getJSON(), true);

        $this->assertTrue($payload['success']);
        $this->assertSame('website_scope', $payload['source']);
        $this->assertStringContainsString(
            'Barangay Bacolod Information System',
            $payload['response']
        );
    }

    public function testLandingChatbotAnswersWebsiteQuestion(): void
    {
        $request = service('request');
        $request->setMethod('POST');
        $request->setGlobal('post', [
            'message' => 'hello',
            'source'  => 'landing',
        ]);
        $request->setGlobal('server', [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $result = $this->withRequest($request)
            ->controller(ChatbotController::class)
            ->execute('chat');

        $result->assertStatus(200);

        $payload = json_decode($result->getJSON(), true);

        $this->assertTrue($payload['success']);
        $this->assertSame('local', $payload['source']);
        $this->assertStringContainsString('BIS Assistant', $payload['response']);
    }
}
