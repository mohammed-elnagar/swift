<?php

namespace SwiftOtter\OrderExport\Action;

use Magento\Config\Console\Command\ConfigShowCommandTest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use SwiftOtter\OrderExport\Model\HeaderData;
use SwiftOtter\OrderExport\Model\Config;

class ExportOrder
{

    private OrderRepositoryInterface $orderRepository;
    private Config $config;
    private CollectOrderData $collectOrderData;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Config $config,
        CollectOrderData $collectOrderData
    ) {
        $this->orderRepository = $orderRepository;
        $this->config = $config;
        $this->collectOrderData = $collectOrderData;
    }

    public function execute(int $orderId, HeaderData $headerData): array
    {
        $order = $this->orderRepository->get($orderId);

        if(!$this->config->isEnabled(ScopeInterface::SCOPE_STORE, $order->getStoreId())){
            throw new LocalizedException(__('Order export is disable'));
        }

        $results = ['success' => false, 'errors' => null];

        $exportData = $this->collectOrderData->execute($order, $headerData);
        // TODO Export to web service, save export details

        return $results;
    }
}