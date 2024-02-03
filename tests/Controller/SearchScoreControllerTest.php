<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\SearchResult;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

class SearchScoreControllerTest extends WebTestCase
{
    private string $existingTerm;
    private PersistenceManagerRegistry $doctrine;
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->doctrine = static::getContainer()->get(PersistenceManagerRegistry::class);

        $this->setUpDatabaseWithExistingTerm();
    }

    /**
     * Set up the database with an existing term.
     *
     */
    private function setUpDatabaseWithExistingTerm(): void
    {
        $this->existingTerm = 'existingTerm';

        $searchResult = new SearchResult();
        $searchResult->setTerm($this->existingTerm);
        $searchResult->setScore(10);
        $searchResult->setCreatedAt(new \DateTime());

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($searchResult);
        $entityManager->flush();
    }

    /**
     * Simulate a GET request with valid data
     * Add assertions to check the response content
     * Simulate a GET request with invalid data
     * Add assertions to check the response content for errors
     */
    public function testCalculateScoreAction(): void
    {
        $this->client->request(
            'GET',
            '/score',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"term": "validSearchTerm"}'
        );
        $this->assertResponseStatusCodeSame(200);


        $responseContent = $this->client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);
        $this->assertArrayHasKey('term', $responseData);
        $this->assertArrayHasKey('score', $responseData);

        $this->client->request(
            'GET',
            '/score',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"term": ""}'
        );
        $this->assertResponseStatusCodeSame(400);

        $errorResponse = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $errorResponse);
    }

    /**
     * Test the cached result by making a request and checking the response.
     *
     */
    public function testCachedResult(): void
    {
        $this->client->request(
            'GET',
            '/score',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['term' => $this->existingTerm])
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($this->existingTerm, $responseData['term']);
        $this->assertSame(true, $responseData['cached']);
    }

    protected function tearDown(): void
    {
        $this->deleteSearchResults();
        parent::tearDown();
    }

    /**
     * A method to delete records from the SearchResult table.
     */
    private function deleteSearchResults(): void
    {
        $entityManager = $this->doctrine->getManager();

        $searchResults = $entityManager->getRepository(SearchResult::class)->findAll();

        foreach ($searchResults as $searchResult) {
            $entityManager->remove($searchResult);
        }

        $entityManager->flush();
    }
}
