<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Structs;

use DateTimeInterface;

class OrderStruct
{
    /**
     * @var int
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $userEmail;

    /**
     * @var string
     */
    protected $orderNumber;

    /**
     * @var DateTimeInterface
     */
    protected $orderAt;

    /**
     * @var string
     */
    protected $paymentName;

    /**
     * @var array|OrderDetailStruct[]
     */
    protected $details = [];

    /**
     * @var AddressStruct
     */
    protected $billingAddress;

    /**
     * @var AddressStruct
     */
    protected $shippingAddress;

    /**
     * @var string
     */
    protected $customerNumber;

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function setUserEmail(string $userEmail): self
    {
        $this->userEmail = $userEmail;

        return $this;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getOrderAt(): DateTimeInterface
    {
        return $this->orderAt;
    }

    public function setOrderAt(DateTimeInterface $orderAt): self
    {
        $this->orderAt = $orderAt;

        return $this;
    }

    public function getPaymentName(): string
    {
        return $this->paymentName;
    }

    public function setPaymentName(string $paymentName): self
    {
        $this->paymentName = $paymentName;

        return $this;
    }

    /**
     * @return array|OrderDetailStruct[]
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param array|OrderDetailStruct[] $details
     */
    public function setDetails(array $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getBillingAddress(): AddressStruct
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(AddressStruct $billingAddress): self
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getShippingAddress(): AddressStruct
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(AddressStruct $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    public function getCustomerNumber(): string
    {
        return $this->customerNumber;
    }

    public function setCustomerNumber(string $customerNumber): self
    {
        $this->customerNumber = $customerNumber;

        return $this;
    }
}
