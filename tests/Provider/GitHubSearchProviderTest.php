<?php

namespace App\Tests\Provider;

use App\Provider\GitHubSearchProvider;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7\Response;

class GitHubSearchProviderTest extends TestCase
{
    /**
     * A test for searching and calculating the score.
     */
    public function testSearchAndCalculateScore()
    {
        $httpClient = $this->createMock(Client::class);
        $logger = $this->createMock(LoggerInterface::class);
        $searchProvider = new GitHubSearchProvider($httpClient, $logger);

        $positiveResults = rand(0, 99999);
        $negativeResults = rand(0, 99999);

        $httpClient->expects($this->exactly(2))
            ->method('get')
            ->willReturn(
                new Response(200, [], $this->createMockResponse($positiveResults)), // Positive results
                new Response(200, [], $this->createMockResponse($negativeResults))  // Negative results
            );

        $query = 'test';
        $score = $searchProvider->searchAndCalculateScore($query);

        $this->assertEquals(($positiveResults / ($positiveResults + $negativeResults)) * 10, $score);
    }

    /**
     * A function to create a mock response.
     *
     * @param int $totalResults 
     * @return string
     */
    private function createMockResponse(int $totalResults): string
    {
        return json_encode(['total_count' => $totalResults]);
    }

}
