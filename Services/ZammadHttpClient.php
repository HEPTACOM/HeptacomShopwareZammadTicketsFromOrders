<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Services;

use Monolog\Logger;
use Shopware\Components\HttpClient\HttpClientInterface;
use Shopware\Components\HttpClient\RequestException;

class ZammadHttpClient
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $apiToken;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(HttpClientInterface $httpClient, Configuration $configuration, Logger $logger)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = \rtrim($configuration->getBaseUrl(), '/') . '/api/v1/';
        $this->apiToken = $configuration->getApiToken();
        $this->logger = $logger;
    }

    public function getUsers(): array
    {
        try {
            $response = $this->httpClient->get($this->baseUrl . 'users', $this->getHeaders())->getBody();
        } catch (RequestException $e) {
            $this->logger->error($e);

            return [];
        }

        return \json_decode($response, true);
    }

    public function getUser(int $id): array
    {
        try {
            $response = $this->httpClient->get($this->baseUrl . 'users/' . $id, $this->getHeaders())->getBody();
        } catch (RequestException $e) {
            $this->logger->error($e);

            return [];
        }

        return \json_decode($response, true);
    }

    public function searchUserByEmail(string $email): array
    {
        try {
            $query = \http_build_query([
                'query' => $email,
                'limit' => 1,
            ]);
            $response = $this->httpClient->get($this->baseUrl . 'users/search?' . $query, $this->getHeaders())->getBody();
        } catch (RequestException $e) {
            $this->logger->error($e);

            return [];
        }

        $result = \json_decode($response, true);

        if (!empty($result)) {
            $result = \current($result);
        }

        return $result;
    }

    public function createUser(string $email, string $firstname, string $lastname): ?int
    {
        try {
            $response = $this->httpClient->post($this->baseUrl . 'users', $this->getHeaders(), [
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
            ])->getBody();
        } catch (RequestException $e) {
            $this->logger->error($e);

            return null;
        }

        return \json_decode($response, true)['id'];
    }

    public function getGroups(): array
    {
        try {
            $response = $this->httpClient->get($this->baseUrl . 'groups', $this->getHeaders())->getBody();
        } catch (RequestException $e) {
            $this->logger->error($e);

            return [];
        }

        return \json_decode($response, true);
    }

    public function createGroup(string $name, string $note): ?int
    {
        try {
            $response = $this->httpClient->post($this->baseUrl . 'groups', $this->getHeaders(), [
                'name' => $name,
                'active' => true,
                'note' => $note,
            ])->getBody();
        } catch (RequestException $e) {
            $this->logger->error($e);

            return null;
        }

        return \json_decode($response, true)['id'];
    }

    public function getTicketPriorities(): array
    {
        try {
            $response = $this->httpClient->get($this->baseUrl . 'ticket_priorities', $this->getHeaders())->getBody();
        } catch (RequestException $e) {
            $this->logger->error($e);

            return [];
        }

        return \json_decode($response, true);
    }

    public function createTicketPriority(string $name, string $note): ?int
    {
        try {
            $response = $this->httpClient->post($this->baseUrl . 'ticket_priorities', $this->getHeaders(), [
                'name' => $name,
                'active' => true,
                'note' => $note,
            ])->getBody();
        } catch (RequestException $e) {
            $this->logger->error($e);

            return null;
        }

        return \json_decode($response, true)['id'];
    }

    public function getTicketStates(): array
    {
        try {
            $response = $this->httpClient->get($this->baseUrl . 'ticket_states', $this->getHeaders())->getBody();
        } catch (RequestException $e) {
            $this->logger->error($e);

            return [];
        }

        return \json_decode($response, true);
    }

    public function createTicketState(string $name, string $note): ?int
    {
        try {
            $response = $this->httpClient->post($this->baseUrl . 'ticket_states', $this->getHeaders(), [
                'name' => $name,
                'state_type_id' => 1,
                'next_state_id' => null,
                'ignore_escalation' => true,
                'active' => true,
                'note' => $note,
            ])->getBody();
        } catch (RequestException $e) {
            $this->logger->error($e);

            return null;
        }

        return \json_decode($response, true)['id'];
    }

    public function searchTicketByNote(string $note): array
    {
        try {
            $query = \http_build_query([
                'query' => $note,
                'limit' => 1,
            ]);
            $response = $this->httpClient->get($this->baseUrl . 'tickets/search?' . $query, $this->getHeaders())->getBody();
        } catch (RequestException $e) {
            $this->logger->error($e);

            return [];
        }

        $result = \json_decode($response, true)['assets'] ?? [];

        if (!empty($result)) {
            $result = \current($result);
        }

        return $result;
    }

    public function createTicket(
        string $title,
        string $subject,
        string $body,
        string $note,
        string $customerEmail,
        int $groupId,
        int $stateId,
        int $priorityId,
        int $organizationId
    ): ?int {
        try {
            $response = $this->httpClient->post($this->baseUrl . 'tickets', $this->getHeaders(), [
                'title' => $title,
                'customer' => $customerEmail,

                'article' => [
                    'content_type' => 'text/html',
                    'subject' => $subject,
                    'body' => $body,
                ],
                'note' => $note,
                'group_id' => $groupId,
                'organization_id' => $organizationId,
                'state_id' => $stateId,
                'priority_id' => $priorityId,
            ])->getBody();
        } catch (RequestException $e) {
            $this->logger->error($e);

            return null;
        }

        return \json_decode($response, true)['id'];
    }

    protected function getHeaders(array $headers = []): array
    {
        return \array_replace_recursive($headers, [
            'Authorization' => 'Token token=' . $this->apiToken,
            'Accept' => 'application/json',
        ]);
    }
}
