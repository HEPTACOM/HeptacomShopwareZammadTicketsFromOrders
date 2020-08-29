<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Services;

use HeptacomZammadTicketsFromOrders\Structs\AddressStruct;
use HeptacomZammadTicketsFromOrders\Structs\OrderStruct;
use Shopware\Bundle\AttributeBundle\Service\DataPersister;

class TransferOrder
{
    /**
     * @var TransferGroup
     */
    private $transferGroup;

    /**
     * @var TransferTicketPriority
     */
    private $ticketPriority;

    /**
     * @var TransferTicketState
     */
    private $ticketState;

    /**
     * @var ZammadHttpClient
     */
    private $zammadClient;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var DataPersister
     */
    private $dataPersister;

    public function __construct(
        TransferGroup $transferGroup,
        TransferTicketPriority $ticketPriority,
        TransferTicketState $ticketState,
        ZammadHttpClient $zammadClient,
        Configuration $configuration,
        DataPersister $dataPersister
    ) {
        $this->transferGroup = $transferGroup;
        $this->ticketPriority = $ticketPriority;
        $this->ticketState = $ticketState;
        $this->zammadClient = $zammadClient;
        $this->configuration = $configuration;
        $this->dataPersister = $dataPersister;
    }

    public function upsertOrder(OrderStruct $order): void
    {
        $zammadUser = $this->zammadClient->searchUserByEmail($order->getUserEmail());

        if (empty($zammadUser)) {
            // process this order at a later time
            return;
        }

        $note = 'HEPTACOM_ZAMMAD_ORDER_' . $order->getOrderNumber();

        if (!empty($this->zammadClient->searchTicketByNote($note))) {
            // order already processed
            return;
        }

        $title = \sprintf(
            $this->configuration->getTicketSubject(),
            $order->getOrderNumber(),
            $order->getOrderAt()->format($this->configuration->getTicketDateFormat())
        );
        $zammadTicketId = $this->zammadClient->createTicket(
            $title,
            $title,
            $this->generateHtmlBody($order),
            $note,
            $order->getUserEmail(),
            $this->transferGroup->getOrderGroupId(),
            $this->ticketState->getOrderTicketStateId(),
            $this->ticketPriority->getOrderTicketPriorityId(),
            1
        );

        $this->dataPersister->persist([
            'heptacom_zammad_tickets_from_orders_zammad_id' => $zammadTicketId,
        ], 's_order_attributes', $order->getOrderId());
    }

    protected function generateHtmlBody(OrderStruct $order): string
    {
        $html = <<<HTML
<table style="border-collapse: separate; border-spacing: 1em 1em;">
    <tr>
        <th>#</th>
        <th>Nummer</th>
        <th>Name</th>
        <th>Anzahl</th>
        <th>Preis</th>
    </tr>
HTML;
        $position = 0;

        foreach ($order->getDetails() as $detail) {
            ++$position;
            $productNumber = $detail->getProductNumber();
            $productName = $detail->getProductName();
            $quantity = $detail->getQuantity();
            $price = \sprintf('%0.2f', $detail->getPrice());
            $html .= <<<HTML
<tr>
    <td>{$position}</td>
    <td>{$productNumber}</td>
    <td>{$productName}</td>
    <td style="text-align: right">{$quantity} &times;</td>
    <td style="text-align: right">{$price}</td>
</tr>
HTML;
        }

        $ticketButtonUrl = \sprintf(
            $this->configuration->getTicketButtonUrlFormat(),
            $order->getOrderNumber()
        );
        $ticketButtonText = $this->configuration->getTicketButtonText();

        $billingAddress = $this->generateAddressHtmlBody(
            $order->getBillingAddress(),
            $this->configuration->getTicketAddressBillingLabel()
        );
        $shippingAddress = $this->generateAddressHtmlBody(
            $order->getShippingAddress(),
            $this->configuration->getTicketAddressShippingLabel()
        );
        $paymentLabel = $this->configuration->getTicketPaymentLabel();
        $paymentName = $order->getPaymentName();

        $customerNumberLabel = $this->configuration->getCustomerNumberLabel();
        $customerNumber = $order->getCustomerNumber();

        $linkTitle = $this->configuration->getAnswerCustomerLinkTitle();
        $mailToLink = \sprintf(
            $this->configuration->getMailToFormat(),
            $order->getUserEmail()
        );

        $html .= <<<HTML
</table>
<hr>
<b>{$paymentLabel}</b>
{$paymentName}
<hr>
<br>{$customerNumberLabel}</br>
{$customerNumber}
<hr>
{$billingAddress}
<hr>
{$shippingAddress}
<hr>
<a href="mailto:${mailToLink}" target="_top">{$linkTitle}</a>
<hr>
<a href="{$ticketButtonUrl}" target="_blank">{$ticketButtonText}</a>
HTML;

        return $html;
    }

    protected function generateAddressHtmlBody(AddressStruct $address, string $label): string
    {
        $lines = [$address->getName()];

        if (!empty($address->getAdditionalLines())) {
            \array_push($lines, ...$address->getAdditionalLines());
        }

        $lines[] = $address->getStreet();
        $lines[] = \ltrim(\trim(\sprintf(
            '%s-%s %s',
            $address->getCountryShortcode(),
            $address->getZipCode(),
            $address->getCity()
        )), '-');

        $htmlLines = \nl2br(\implode(\PHP_EOL, $lines));

        return <<<HTML
<b>{$label}</b>
<br>
{$htmlLines}
HTML;
    }
}
