<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Services;

class TransferGroup
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

    public function getOrderGroupId(): int
    {
        if ($this->configuration->getTicketGroupId()) {
            return $this->configuration->getTicketGroupId();
        }
        $groups = $this->zammadClient->getGroups();
        $groupIds = \array_column($groups, 'note', 'id');
        $groupId = \array_search('HEPTACOM_ZAMMAD_ORDER', $groupIds, true);

        if ($groupId !== false) {
            return $groupId;
        }

        return $this->zammadClient->createGroup('Bestellung', 'HEPTACOM_ZAMMAD_ORDER');
    }
}
