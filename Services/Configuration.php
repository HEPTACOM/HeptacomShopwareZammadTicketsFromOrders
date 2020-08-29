<?php declare(strict_types=1);

namespace HeptacomZammadTicketsFromOrders\Services;

use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Plugin\ConfigReader;

/**
 * @method string   getBaseUrl()
 * @method string   getApiToken()
 * @method string   getTicketSubject()
 * @method string   getTicketDateFormat()
 * @method string   getTicketButtonUrlFormat()
 * @method string   getTicketButtonText()
 * @method string   getTicketAddressShippingLabel()
 * @method string   getTicketAddressBillingLabel()
 * @method string   getTicketPaymentLabel()
 * @method string   getCustomerTimeRange()
 * @method bool     getTestMode()
 * @method string   getCustomerNumberLabel()
 * @method int|null getTicketStateId()
 * @method int|null getTicketGroupId()
 * @method string   getAnswerCustomerLinkTitle()
 * @method string   getMailToFormat()
 * @method string   getTestModeEmailLikePattern()
 */
class Configuration
{
    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * var string
     */
    private $pluginName;

    public function __construct(ConfigReader $configReader, string $pluginName)
    {
        $this->configReader = $configReader;
        $this->pluginName = $pluginName;
    }

    public function __call($name, $_)
    {
        return $this->getConfiguration()[Container::underscore(\lcfirst(\substr($name, 3)))];
    }

    protected function getConfiguration(): array
    {
        return $this->configReader->getByPluginName($this->pluginName);
    }
}
