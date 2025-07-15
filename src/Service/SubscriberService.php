<?php
namespace App\Service;

use App\DTO\SubscriberInput;
use App\HttpClient\CrmApiClientInterface;
use App\Validator\AgeValidator;
use App\Exception\ValidationException;

class SubscriberService
{
    public function __construct(
        private CrmApiClientInterface $client,
        private AgeValidator $validator
    ) {
        // Constructor injection for dependencies
        // CrmApiClientInterface for CRM API interactions
        // AgeValidator for validating age-related constraints
    }

    /**
     * Registers a new subscriber with the CRM.
     * Validates the input data, checks age requirements,   
     * and creates the subscriber in the CRM.
     * @param SubscriberInput $input The subscriber input data
     * @return string The ID of the created subscriber          
     */
    public function register(SubscriberInput $input): string
    {
        $this->validator->ensureAdult($input->dateOfBirth);

        $lists = $input->marketingConsent
            ? $this->extractCityListIds(
                $this->client->getLists(),
                ['London', 'Birmingham', 'Edinburgh']
              )
            : [];

        $data = [
            'emailAddress' => $input->email,
            'firstName' => $input->firstName,
            'lastName' => $input->lastName,
            'dateOfBirth' => $input->dateOfBirth->format('Y-m-d'),
            'marketingConsent' => $input->marketingConsent,
            'lists' => $lists
        ];

        $subscriber = $this->client->createSubscriber($data);
        return $subscriber['id'] ?? throw new ValidationException('Subscriber ID missing from CRM response.');
    }

    /**
     * Extracts the IDs of city-specific lists from the CRM.
     * @param array $lists The list of CRM lists
     * @param array $cities The city names to filter by
     * @return array The IDs of the matching lists
     * @throws ValidationException If no matching lists are found
     */
    private function extractCityListIds(array $lists, array $cities): array
    {
        return array_map(fn($l) => $l['id'], array_filter(
            $lists,
            fn($l) => in_array($l['name'], $cities, true)
        ));
    }
}
