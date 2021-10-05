<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use http\Exception\RuntimeException;
use PDO;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

final class FeatureContext implements Context
{
    /** @var KernelInterface */
    private $kernel;

    /** @var Response|null */
    private $response;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /** @BeforeScenario*/
    public static function prepareForTheFeature()
    {
        $pdo = new PDO('mysql:host=db;dbname=my_budget', 'root', 'root');
        $pdo->exec('DELETE FROM transactions');

        $cache = RedisAdapter::createConnection(
            'redis://cache'
        );
        $cache->flushAll();
    }

    /**
     * @Given /^I make "([^"]*)" request to "([^"]*)" endpoint$/
     */
    public function iMakeRequestToEndpoint(
        string $method,
        string $path
    ) {
        $this->response = $this->kernel->handle(Request::create($path, $method));
    }

    /**
     * @Given /^I make "([^"]*)" request to "([^"]*)" endpoint with body$/
     */
    public function iMakeRequestToEndpointWithBody(
        string $method,
        string $path,
        PyStringNode $body
    ) {
        $request = Request::create($path, $method, [], [], [], [], $body->getRaw());

        $this->response = $this->kernel->handle($request);
    }

    /**
     * @Then /^the response code should be (\d+)$/
     */
    public function theResponseCodeShouldBe(int $code)
    {
        if ($code !== $this->response->getStatusCode()) {
            throw new RuntimeException("Received response with {$this->response->getStatusCode()} code");
        }
    }

    /**
     * @Then the response should contain :value
     */
    public function theResponseShouldContain(string $value)
    {
        $content = json_decode($this->response->getContent(), true);

        if (strpos($this->response->getContent(), $value) === false) {
            throw new RuntimeException("Response doesn't contain '{$value}'");
        }
    }

    /**
     * @Then the response JSON node :node should be equal to :value
     */
    public function theResponseJsonNodeShouldBeEqualTo(string $node, $value)
    {
        $content = json_decode($this->response->getContent(), true);

        if (!array_key_exists($node, $content)) {
            throw new \RuntimeException("Node '{$node}' is not present in response JSON");
        }

        if ($content[$node] != $value) {
            throw new RuntimeException("Value of the '{$node}' is {$content[$node]}");
        }
    }
}
