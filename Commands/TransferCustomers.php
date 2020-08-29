<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Commands;

use HeptacomZammadTicketsFromOrders\Services\CustomerIterator;
use HeptacomZammadTicketsFromOrders\Services\TransferCustomer;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TransferCustomers extends ShopwareCommand
{
    /**
     * @var CustomerIterator
     */
    private $customerIterator;

    /**
     * @var TransferCustomer
     */
    private $transferCustomer;

    public function __construct(CustomerIterator $customerIterator, TransferCustomer $transferCustomer)
    {
        parent::__construct('heptacom:zammad:customers');
        $this->customerIterator = $customerIterator;
        $this->transferCustomer = $transferCustomer;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->section('Zammad Customer Transfer');
        $io->progressStart();

        foreach ($this->customerIterator->iterateCustomers(1000) as $customer) {
            $io->progressAdvance();
            $this->transferCustomer->upsertCustomer($customer);
        }

        $io->progressFinish();
    }
}
