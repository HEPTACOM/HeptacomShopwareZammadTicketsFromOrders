<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Commands;

use HeptacomZammadTicketsFromOrders\Services\OrderIterator;
use HeptacomZammadTicketsFromOrders\Services\TransferOrder;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TransferOrders extends ShopwareCommand
{
    /**
     * @var OrderIterator
     */
    private $orderIterator;

    /**
     * @var TransferOrder
     */
    private $transferOrder;

    public function __construct(OrderIterator $orderIterator, TransferOrder $transferOrder)
    {
        parent::__construct('heptacom:zammad:orders');
        $this->orderIterator = $orderIterator;
        $this->transferOrder = $transferOrder;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->section('Zammad Order Transfer');
        $io->progressStart();

        foreach ($this->orderIterator->iterateOrders(1000) as $order) {
            $io->progressAdvance();
            $this->transferOrder->upsertOrder($order);
        }

        $io->progressFinish();
    }
}
