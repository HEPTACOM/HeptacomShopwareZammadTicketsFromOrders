<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Services;

use Doctrine\DBAL\Connection;
use Generator;
use HeptacomZammadTicketsFromOrders\Structs\AddressStruct;
use HeptacomZammadTicketsFromOrders\Structs\OrderDetailStruct;
use HeptacomZammadTicketsFromOrders\Structs\OrderStruct;
use Shopware\Models\Order\Status;
use Traversable;

class OrderIterator
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return OrderStruct[]
     */
    public function iterateOrders(int $pageSize): Traversable
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->from('s_order', 'o')
            ->innerJoin('o', 's_order_attributes', 'oa', $builder->expr()->eq('oa.orderID', 'o.id'))
            ->innerJoin('o', 's_user', 'u', $builder->expr()->eq('o.userID', 'u.id'))
            ->leftJoin('o', 's_core_paymentmeans', 'p', $builder->expr()->eq('o.paymentID', 'p.id'))
            ->innerJoin('o', 's_order_billingaddress', 'b', $builder->expr()->eq('b.orderID', 'o.id'))
            ->innerJoin('b', 's_core_countries', 'bc', $builder->expr()->eq('bc.id', 'b.countryID'))
            ->innerJoin('o', 's_order_shippingaddress', 's', $builder->expr()->eq('s.orderID', 'o.id'))
            ->innerJoin('s', 's_core_countries', 'sc', $builder->expr()->eq('sc.id', 's.countryID'))
            ->innerJoin('u', 's_user_attributes', 'ua', $builder->expr()->eq('u.id', 'ua.userID'))
            ->select([
                'o.id AS order_id',
                'o.ordernumber AS order_number',
                'o.ordertime AS order_time',
                'u.email AS user_email',
                's.salutation AS shipping_salutation',
                's.title AS shipping_title',
                's.company AS shipping_company',
                's.department AS shipping_department',
                's.firstname AS shipping_firstname',
                's.lastname AS shipping_lastname',
                's.street AS shipping_street',
                's.additional_address_line1 AS shipping_additional1',
                's.additional_address_line2 AS shipping_additional2',
                's.zipcode AS shipping_zipcode',
                's.city AS shipping_city',
                'sc.countryiso AS shipping_country',
                'b.salutation AS billing_salutation',
                'b.title AS billing_title',
                'b.company AS billing_company',
                'b.department AS billing_department',
                'b.firstname AS billing_firstname',
                'b.lastname AS billing_lastname',
                'b.street AS billing_street',
                'b.additional_address_line1 AS billing_additional1',
                'b.additional_address_line2 AS billing_additional2',
                'b.zipcode AS billing_zipcode',
                'b.city AS billing_city',
                'bc.countryiso AS billing_country',
                'p.description AS payment_name',
                'u.customernumber AS customer_number',
            ])
            ->where(
                $builder->expr()->neq('o.status', ':orderState'),
                $builder->expr()->isNotNull('ua.heptacom_zammad_tickets_from_orders_zammad_id'),
                $builder->expr()->isNull('oa.heptacom_zammad_tickets_from_orders_zammad_id')
            )
            ->setParameter('orderState', Status::ORDER_STATE_CANCELLED)
            ->orderBy('o.id');

        $page = 0;

        do {
            $statement = $builder->setFirstResult($page * $pageSize)
                ->setMaxResults($pageSize)
                ->execute();

            $items = $statement->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $result = new OrderStruct();
                $result->setUserEmail($item['user_email'])
                    ->setOrderNumber($item['order_number'])
                    ->setOrderId((int) $item['order_id'])
                    ->setOrderAt(\date_create_from_format('Y-m-d H:i:s', $item['order_time']))
                    ->setPaymentName($item['payment_name'] ?? '')
                    ->setDetails(\iterator_to_array($this->iterateOrderDetails($result->getOrderId())))
                    ->setBillingAddress($this->buildAddress($item, 'billing'))
                    ->setShippingAddress($this->buildAddress($item, 'shipping'))
                    ->setCustomerNumber($item['customer_number']);
                yield $item['order_id'] => $result;
            }

            ++$page;
        } while (!empty($items));
    }

    /**
     * @return OrderDetailStruct[]
     */
    protected function iterateOrderDetails(int $orderId): Generator
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->from('s_order_details', 'd')
            ->select([
                'd.id AS orderdetail_id',
                'd.articleordernumber AS orderdetail_productnumber',
                'd.name AS orderdetail_productname',
                'd.quantity AS orderdetail_quantity',
                'd.price AS orderdetail_price',
                'd.tax_rate AS orderdetail_tax',
            ])
            ->where($builder->expr()->eq('d.orderID', ':orderId'))
            ->setParameter('orderId', $orderId)
            ->orderBy('d.id');

        foreach ($builder->execute()->fetchAll(\PDO::FETCH_ASSOC) as $detailData) {
            $result = new OrderDetailStruct();
            $result->setPrice((float) $detailData['orderdetail_price'])
                ->setTax((float) $detailData['orderdetail_tax'])
                ->setProductNumber($detailData['orderdetail_productnumber'])
                ->setProductName($detailData['orderdetail_productname'])
                ->setQuantity((int) $detailData['orderdetail_quantity']);

            yield $detailData['orderdetail_id'] => $result;
        }
    }

    protected function buildAddress(array $data, string $prefix): AddressStruct
    {
        $result = new AddressStruct();
        $result->setName(\trim(\implode(' ', [
                $data[$prefix . '_salutation'] !== 'mr' ? 'Herr' : 'Frau',
                $data[$prefix . '_title'] ?? '',
                $data[$prefix . '_firstname'] ?? '',
                $data[$prefix . '_lastname'] ?? '',
            ])))
            ->setCity($data[$prefix . '_city'] ?? '')
            ->setZipCode($data[$prefix . '_zipcode'])
            ->setCountryShortcode($data[$prefix . '_country'])
            ->setStreet($data[$prefix . '_street'])
            ->setAdditionalLines(\array_filter([
                $data[$prefix . '_company'] ?? '',
                $data[$prefix . '_department'] ?? '',
                $data[$prefix . '_additional1'] ?? '',
                $data[$prefix . '_additional2'] ?? '',
            ], 'strlen'));

        return $result;
    }
}
