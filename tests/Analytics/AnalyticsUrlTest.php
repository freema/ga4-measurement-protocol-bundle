<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Analytics;

use Freema\GA4MeasurementProtocolBundle\Domain\AnalyticsUrl;
use PHPUnit\Framework\TestCase;

class AnalyticsUrlTest extends TestCase
{
    public function testConstructorWithValidData(): void
    {
        $url = 'https://analytics.google.com/g/collect';
        $data = [
            'payload' => ['param1' => 'value1'],
            'events' => [['name' => 'test_event']],
            'response' => ['status_code' => 200],
            'raw_json' => '{"data":"test"}',
            'debug_info' => ['client_id' => '123456'],
        ];

        $analyticsUrl = new AnalyticsUrl($url, $data);

        $this->assertEquals($url, $analyticsUrl->getUrl());
        $this->assertEquals($data['payload'], $analyticsUrl->getParameters());
        $this->assertEquals($data['events'], $analyticsUrl->getEvents());
        $this->assertEquals($data['response'], $analyticsUrl->getResponse());
        $this->assertEquals($data['raw_json'], $analyticsUrl->getRawJson());
        $this->assertEquals($data['debug_info'], $analyticsUrl->getDebugInfo());
    }

    public function testConstructorWithStringDebugInfo(): void
    {
        $url = 'https://analytics.google.com/g/collect';
        $debugInfoString = '{"client_id":"123456","error":"test error"}';
        $data = [
            'debug_info' => $debugInfoString,
        ];

        $analyticsUrl = new AnalyticsUrl($url, $data);

        // Should convert the JSON string to an array
        $this->assertIsArray($analyticsUrl->getDebugInfo());
        $this->assertEquals('123456', $analyticsUrl->getClientId());
    }

    public function testConstructorWithNonJsonStringDebugInfo(): void
    {
        $url = 'https://analytics.google.com/g/collect';
        $debugInfoString = 'Non-JSON debug info';
        $data = [
            'debug_info' => $debugInfoString,
        ];

        $analyticsUrl = new AnalyticsUrl($url, $data);

        // Should convert the string to an array with a message
        $this->assertIsArray($analyticsUrl->getDebugInfo());
        $this->assertEquals(['message' => $debugInfoString], $analyticsUrl->getDebugInfo());
    }

    public function testConstructorWithIntegerDebugInfo(): void
    {
        $url = 'https://analytics.google.com/g/collect';
        $debugInfoInt = 12345;
        $data = [
            'debug_info' => $debugInfoInt,
        ];

        $analyticsUrl = new AnalyticsUrl($url, $data);

        // Should convert the integer to an array with type information
        $this->assertIsArray($analyticsUrl->getDebugInfo());
        $this->assertEquals(['value' => $debugInfoInt, 'type' => 'integer'], $analyticsUrl->getDebugInfo());
    }

    public function testGetClientIdFromDebugInfo(): void
    {
        $url = 'https://analytics.google.com/g/collect';
        $data = [
            'debug_info' => ['client_id' => 12345], // Integer client_id
        ];

        $analyticsUrl = new AnalyticsUrl($url, $data);

        // Should convert the integer client_id to a string
        $this->assertIsString($analyticsUrl->getClientId());
        $this->assertEquals('12345', $analyticsUrl->getClientId());
    }

    public function testGetValidationErrorsWithNonArrayValue(): void
    {
        $url = 'https://analytics.google.com/g/collect';
        $data = [
            'debug_info' => ['validation_errors' => 'Single error message'],
        ];

        $analyticsUrl = new AnalyticsUrl($url, $data);

        // Should convert the string to a single-element array
        $errors = $analyticsUrl->getValidationErrors();
        $this->assertIsArray($errors);
        $this->assertCount(1, $errors);
        $this->assertEquals('Single error message', $errors[0]);
    }

    public function testIsSuccessful(): void
    {
        // Test successful response
        $analyticsUrl1 = new AnalyticsUrl('https://example.com', [
            'response' => ['status_code' => 200],
        ]);
        $this->assertTrue($analyticsUrl1->isSuccessful());

        // Test unsuccessful response
        $analyticsUrl2 = new AnalyticsUrl('https://example.com', [
            'response' => ['status_code' => 400],
        ]);
        $this->assertFalse($analyticsUrl2->isSuccessful());

        // Test missing response
        $analyticsUrl3 = new AnalyticsUrl('https://example.com', []);
        $this->assertFalse($analyticsUrl3->isSuccessful());
    }

    public function testManualRunTest(): void
    {
        // This test simulates the problematic case
        $url = 'https://analytics.google.com/g/collect';

        // Create an instance with string debug_info (the error case)
        $analyticsUrl = new AnalyticsUrl($url, [
            'debug_info' => 'String that would cause a type error',
        ]);

        // Verify that no error is thrown and debug_info is an array
        $this->assertIsArray($analyticsUrl->getDebugInfo());

        // Additional test - with null
        $analyticsUrl2 = new AnalyticsUrl($url, [
            'debug_info' => null,
        ]);
        $this->assertNull($analyticsUrl2->getDebugInfo());

        // The test passes if no TypeError is thrown
        $this->assertTrue(true);
    }

    public function testPropertyTypesAreCorrect(): void
    {
        // This test verifies that all properties have the correct types
        $url = 'https://analytics.google.com/g/collect';
        $analyticsUrl = new AnalyticsUrl($url, [
            'debug_info' => 'String that would cause a type error',
        ]);

        // Use reflection to check property types
        $reflection = new \ReflectionClass($analyticsUrl);
        $debugInfoProperty = $reflection->getProperty('debugInfo');
        $debugInfoProperty->setAccessible(true);
        $debugInfoValue = $debugInfoProperty->getValue($analyticsUrl);

        // Assert that debugInfo is an array, as required
        $this->assertIsArray($debugInfoValue);
    }
}
