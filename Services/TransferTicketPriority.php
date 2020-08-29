<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Services;

class TransferTicketPriority
{
    /**
     * @var ZammadHttpClient
     */
    private $zammadClient;

    public function __construct(ZammadHttpClient $zammadClient)
    {
        $this->zammadClient = $zammadClient;
    }

    public function getOrderTicketPriorityId(): int
    {
        $ticketPriorities = $this->zammadClient->getTicketPriorities();
        $ticketPriorityIds = \array_column($ticketPriorities, 'note', 'id');
        $priorityId = \array_search('HEPTACOM_ZAMMAD_ORDER', $ticketPriorityIds, true);

        if ($priorityId !== false) {
            return $priorityId;
        }

        return $this->zammadClient->createTicketPriority('Bestellt', 'HEPTACOM_ZAMMAD_ORDER');
    }
}
