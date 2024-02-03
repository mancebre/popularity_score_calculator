<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use App\Entity\SearchResult;
use App\Repository\SearchResultRepository;
use App\Provider\SearchProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class SearchScoreController extends AbstractController
{

    public function __construct(
        private SearchResultRepository $searchResultRepository,
        private PersistenceManagerRegistry $doctrine,
        private SearchProviderInterface $gitHubSearchProvider,
        private LoggerInterface $loggerInterface,
    ) {
    }

    /**
     * Validate the search term.
     *
     * @param string $term
     * @return JsonResponse|null Returns null if validation passes, JsonResponse if validation fails.
     */
    private function validateTerm(string $term): JsonResponse|null
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($term, [
            new Assert\NotBlank(),
            new Assert\Length(['min' => 3, 'max' => 256]),
            new Assert\Regex(['pattern' => '/^[a-zA-Z0-9\s]+$/']),
        ]);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            return new JsonResponse(['errors' => $errors], 400);
        }

        return null; // Validation passed
    }

    /**
     * Calculate the score based on the given search term and store the result in the database.
     *
     * @param Request $request The request object containing the search term
     * @return JsonResponse
     */
    #[Route('/score', name: 'app_search_score', methods: ['GET'])]
    public function calculateScore(Request $request): JsonResponse
    {
        $term = trim($request->getPayload()->get('term'));

        $validationResult = $this->validateTerm($term);
        if ($validationResult !== null) {
            return $validationResult; // Validation failed, return JsonResponse
        }

        // Check if result is already in the database
        $searchResult = $this->searchResultRepository->findOneBy(['term' => $term]);
        if ($searchResult) {
            return new JsonResponse([
                'term' => $term,
                'score' => $searchResult->getScore(),
                'cached' => true
            ]);
        }

        $score = $this->gitHubSearchProvider->searchAndCalculateScore($term);

        // Store result in the database
        $searchResult = new SearchResult();
        $searchResult->setTerm($term);
        $searchResult->setScore($score);
        $searchResult->setCreatedAt(new \DateTime());

        try {
            // Store result in the database
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($searchResult);
            $entityManager->flush();
        } catch (\PDOException $e) {
            // Log or handle the exception
            $this->loggerInterface->error('An error occurred while saving the search result: ' . $e->getMessage());
            // Return an appropriate error response to the client
            return new JsonResponse(['error' => 'An error occurred while saving the search result.'], 500);
        }

        return new JsonResponse([
            'term' => $term,
            'score' => $score,
        ]);
    }

    /**
     * Returns the first search result from the database for the given term.
     *
     * @param string $term
     * @return SearchResult|null
     */
    public function getFirstResult(string $term): ?SearchResult
    {
        return $this->searchResultRepository->findOneByTerm($term);
    }
}
