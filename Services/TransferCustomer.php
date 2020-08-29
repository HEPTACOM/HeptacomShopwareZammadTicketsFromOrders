<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Services;

use Doctrine\DBAL\Connection;
use HeptacomZammadTicketsFromOrders\Structs\CustomerStruct;
use Shopware\Bundle\AttributeBundle\Service\DataPersister;

class TransferCustomer
{
    /**
     * @var ZammadHttpClient
     */
    private $zammadClient;

    /**
     * @var DataPersister
     */
    private $dataPersister;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(ZammadHttpClient $zammadClient, DataPersister $dataPersister, Connection $connection)
    {
        $this->zammadClient = $zammadClient;
        $this->dataPersister = $dataPersister;
        $this->connection = $connection;
    }

    public function upsertCustomer(CustomerStruct $customer): void
    {
        $zammadUser = $this->zammadClient->searchUserByEmail($customer->getEmail());

        if (empty($zammadUser)) {
            $zammadUserId = $this->zammadClient->createUser(
                $customer->getEmail(),
                $customer->getFirstname(),
                $customer->getLastname()
            );
        } else {
            $zammadUserId = $zammadUser['id'];
        }

        foreach ($this->getUserIds($customer->getEmail()) as $userId) {
            $this->dataPersister->persist([
                'heptacom_zammad_tickets_from_orders_zammad_id' => $zammadUserId,
            ], 's_user_attributes', $userId);
        }
    }

    protected function getUserIds(string $email): array
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->from('s_user', 'su')
            ->select('su.id')
            ->where($builder->expr()->eq('email', ':email'))
            ->setParameter('email', $email)
        ;

        return $builder->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }
}
