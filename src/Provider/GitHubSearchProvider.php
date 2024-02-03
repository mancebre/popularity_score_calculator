<?php

namespace App\Provider;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class GitHubSearchProvider implements SearchProviderInterface
{
    public function __construct(private Client $httpClient, private LoggerInterface $loggerInterface)
    {
    }

    /**
     * A function to search for positive and negative results and calculate a score based on the query.
     *
     * @param string $query The search query
     * @return float The calculated score
     */
    public function searchAndCalculateScore(string $query): float
    {
        $positiveResults = $this->search($query . ' rocks');
        $negativeResults = $this->search($query . ' sucks');

        $totalResults = $positiveResults + $negativeResults;

        $score = ($totalResults === 0) ? 0.0 : ($positiveResults / $totalResults) * 10.0;

        return $score;
    }

    /**
     * A function to search for repositories on GitHub based on a query.
     *
     * @param string $query The search query
     * @throws \GuzzleHttp\Exception\RequestException Description of the exception
     * @return int The total number of search results
     */
    public function search(string $query): int
    {
        try {
            $response = $this->httpClient->get('https://api.github.com/search/repositories', [
                'query' => [
                    'q' => $query,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $totalResults = $data['total_count'];

            return $totalResults;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Log detailed information about the error
            $this->loggerInterface->error('Error interacting with the GitHub API.', [
                'error_message' => $e->getMessage(),
                'request' => $e->getRequest(),
                'response' => $e->getResponse(),
            ]);

            // Throw an exception
            throw new \Exception('Error interacting with the GitHub API. Please try again later.');
        }
    }
}
