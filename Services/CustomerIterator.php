<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Services;

use DateInterval;
use Doctrine\DBAL\Connection;
use HeptacomZammadTicketsFromOrders\Structs\CustomerStruct;
use Traversable;

class CustomerIterator
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Connection $connection, Configuration $configuration)
    {
        $this->connection = $connection;
        $this->configuration = $configuration;
    }

    /**
     * @return CustomerStruct[]
     */
    public function iterateCustomers(int $pageSize): Traversable
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->from('s_user', 'u')
            ->leftJoin('u', 's_user_attributes', 'ua', $builder->expr()->eq('ua.userID', 'u.id'))
            ->select([
                'u.id AS user_id',
                'u.email AS user_email',
                'u.firstname AS user_firstname',
                'u.lastname AS user_lastname',
            ])
            ->orderBy('u.id');

        $builder->where(
            $builder->expr()->isNull('ua.heptacom_zammad_tickets_from_orders_zammad_id'),
            $builder->expr()->gt('u.lastlogin', ':time')
        );
        $backInTime = \date_create()->sub(new DateInterval($this->configuration->getCustomerTimeRange()));
        $builder->setParameter('time', $backInTime->format('Y-m-d H:i:s'));

        if ($this->configuration->getTestMode() && $this->configuration->getTestModeEmailLikePattern() !== '') {
            $builder->andWhere($builder->expr()->like('u.email', ':emailPattern'));
            $builder->setParameter('emailPattern', $this->configuration->getTestModeEmailLikePattern());
        }

        $page = 0;

        do {
            $statement = $builder->setFirstResult($page * $pageSize)
                ->setMaxResults($pageSize)
                ->execute();

            $items = $statement->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $result = new CustomerStruct();
                $result->setFirstname($item['user_firstname'])
                    ->setLastname($item['user_lastname'])
                    ->setEmail($item['user_email']);
                yield $item['user_id'] => $result;
            }

            ++$page;
        } while (!empty($items));
    }
}
