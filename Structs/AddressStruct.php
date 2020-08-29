<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Structs;

class AddressStruct
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $street;

    /**
     * @var string
     */
    protected $zipCode;

    /**
     * @var string
     */
    protected $countryShortcode;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var array|string[]
     */
    protected $additionalLines = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCountryShortcode(): string
    {
        return $this->countryShortcode;
    }

    public function setCountryShortcode(string $countryShortcode): self
    {
        $this->countryShortcode = $countryShortcode;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getAdditionalLines(): array
    {
        return $this->additionalLines;
    }

    /**
     * @param array|string[] $additionalLines
     */
    public function setAdditionalLines(array $additionalLines): self
    {
        $this->additionalLines = $additionalLines;

        return $this;
    }
}
