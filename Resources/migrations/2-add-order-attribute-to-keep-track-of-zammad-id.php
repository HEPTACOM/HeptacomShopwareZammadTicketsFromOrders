<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Migrations;

use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Migrations\AbstractPluginMigration;

class Migration2 extends AbstractPluginMigration
{
    public function up($modus): void
    {
        Shopware()->Container()->get('shopware_attribute.crud_service')->update(
            's_order_attributes',
            'heptacom_zammad_tickets_from_orders_zammad_id',
            TypeMapping::TYPE_INTEGER,
            [
                'translatable' => false,
                'displayInBackend' => true,
                'custom' => false,
            ]
        );
    }

    public function down(bool $keepUserData): void
    {
        if ($keepUserData) {
            Shopware()->Container()->get('shopware_attribute.crud_service')->update(
                's_order_attributes',
                'heptacom_zammad_tickets_from_orders_zammad_id',
                TypeMapping::TYPE_INTEGER,
                ['displayInBackend' => false]
            );

            return;
        }

        Shopware()->Container()->get('shopware_attribute.crud_service')->delete(
            's_order_attributes',
            'heptacom_zammad_tickets_from_orders_zammad_id'
        );
    }
}
