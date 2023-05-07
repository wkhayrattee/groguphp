<?php

use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/** *** *** *** ***  *** *** *** ***  *** *** *** ***  *** *** *** ***  *** *** *** ***
 * Test method: sredirect()
 */
test('[sredirect] Correctly redirects to the expected page', function () {
    $page = '/test-page-to-redirect-to/';

    // Create a mock of the RedirectResponse object
    $responseMock = \Mockery::mock(RedirectResponse::class);

    // Set up expectations
    $responseMock->shouldReceive('send')->once();

    // Call sredirect() with the mocked RedirectResponse object
    $result = sredirect($page, $responseMock);

    // Assert that the result is the same as the mock object
    expect($result)->toBe($responseMock);
});

afterEach(function () {
    // Clean up the Mockery mocks after each test
    \Mockery::close();
});

/** *** *** *** ***  *** *** *** ***  *** *** *** ***  *** *** *** ***  *** *** *** ***
 * Test method - sp()
 */
test('(sp) Correctly returns a formatted string without printing & without dying', function () {
    $array = ['foo' => 'bar'];

    // Call sp() with $print set to false and $die set to false
    $result = sp($array, false, false);

    $expectedResult = '<pre>' . print_r($array, true) . '</pre><br/>';

    // Assert that sp() returns the expected formatted string
    expect($result)->toBe($expectedResult);
});
test('(sp) prints the formatted string without dying', function () {
    $array = ['foo' => 'bar'];

    // Start output buffering to capture the echo output
    ob_start();

    // Call sp() with $print set to true and $die set to false
    $result = sp($array, true, false);

    // Get the captured output and clean the buffer
    $capturedOutput = ob_get_clean();

    $expectedResult = '<pre>' . print_r($array, true) . '</pre><br/>';

    // Assert that sp() prints the expected formatted string
    expect($capturedOutput)->toBe($expectedResult);

    // Assert that sp() returns the expected formatted string
    expect($result)->toBe($expectedResult);
});
test('(sp) prints the formatted string and dies', function () {
    $array = ['foo' => 'bar'];

    // Start output buffering to capture the echo output
    ob_start();

    // Call sp() with $print set to true and $die set to true, and catch the exception
    try {
        $result = sp($array, true, true);
    } catch (RuntimeException $e) {
        // Get the captured output and clean the buffer
        $capturedOutput = ob_get_clean();

        $expectedResult = '<pre>' . print_r($array, true) . '</pre><br/>';

        // Assert that the function prints the expected formatted string
        expect($capturedOutput)->toBe($expectedResult);

        // Assert that the exception message matches the expected message
        expect($e->getMessage())->toBe('Script terminated by sp function');

        return;
    }

    // If no exception is caught, the test will fail
    fail('(sp) did not throw an exception as expected.');
});

/** *** *** *** ***  *** *** *** ***  *** *** *** ***  *** *** *** ***  *** *** *** ***
 * Test method - sd()
 */
test('(sd) prints the formatted string and dies when called with sd', function () {
    $array = ['foo' => 'bar'];

    // Start output buffering to capture the echo output
    ob_start();

    // Call the sd function with $print set to true, and catch the exception
    try {
        sd($array, true);
    } catch (RuntimeException $e) {
        // Get the captured output and clean the buffer
        $capturedOutput = ob_get_clean();

        $expectedResult = '<pre>' . print_r($array, true) . '</pre><br/>';

        // Assert that the function prints the expected formatted string
        expect($capturedOutput)->toBe($expectedResult);

        // Assert that the exception message matches the expected message
        expect($e->getMessage())->toBe('Script terminated by sp function');

        return;
    }

    // If no exception is caught, the test will fail
    throw new ExpectationFailedException('(sd) did not throw an exception as expected.');
});
