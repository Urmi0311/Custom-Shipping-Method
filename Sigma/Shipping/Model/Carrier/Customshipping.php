<?php

namespace Sigma\Shipping\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

class Customshipping extends AbstractCarrier implements CarrierInterface
{

    /**
     * @var string
     */
    protected $_code = 'customshipping';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    private ResultFactory $rateResultFactory;

    /**
     * @var MethodFactory
     */
    private MethodFactory $rateMethodFactory;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->logger = $logger;
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $orderTotal = $request->getBaseSubtotalInclTax();
        if ($orderTotal > 100) {
            $shippingCharge = $orderTotal * 0.05;
        } else {
            $shippingCharge = 20;
        }

        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice($shippingCharge);
        $method->setCost($shippingCharge);
        $result = $this->rateResultFactory->create();
        $result->append($method);
        return $result;
    }

    /**
     * Retrieve allowed shipping methods
     */
    public function getAllowedMethods(): array
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * Retrieve weekdays
     */
    public function getSelectedWeekdays(): array
    {
        $weekdays = $this->getConfigData('weekdays');

        if (is_string($weekdays)) {
            $weekdays = explode(',', $weekdays);
        }
        return $weekdays;
    }
    /**
     * Retrieve starttime
     */
    public function getStartTime(): string
    {
        $startTime = $this->getConfigData('starting_time');
        return $startTime;
    }
    /**
     * Retrieve cutofftime
     */
    public function getCutoffTime(): string
    {
        $startTime = $this->getConfigData('cutoff_time');
        return $startTime;
    }
    /**
     * Retrieve shippingfield value
     */
    public function getShippingField(): string
    {
        $shippingfield = $this->getConfigData('shipping_information');
        return $shippingfield;
    }
}
