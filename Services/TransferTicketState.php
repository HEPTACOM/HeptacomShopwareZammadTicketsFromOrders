<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Services;

class TransferTicketState
{
    /**
     * @var ZammadHttpClient
     */
    private $zammadClient;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(ZammadHttpClient $zammadClient, Configuration $configuration)
    {
        $this->zammadClient = $zammadClient;
        $this->configuration = $configuration;
    }

    public function getOrderTicketStateId(): int
    {
        if ($this->configuration->getTicketStateId()) {
            return $this->configuration->getTicketStateId();
        }
        $ticketStates = $this->zammadClient->getTicketStates();
        $ticketStateIds = \array_column($ticketStates, 'note', 'id');
        $stateId = \array_search('HEPTACOM_ZAMMAD_ORDER', $ticketStateIds, true);

        if ($stateId !== false) {
            return $stateId;
        }

        return $this->zammadClient->createTicketState('Bestellt', 'HEPTACOM_ZAMMAD_ORDER');
    }
}
