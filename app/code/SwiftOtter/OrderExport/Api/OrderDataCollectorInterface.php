<?php

namespace SwiftOtter\OrderExport\Api;

use Magento\Sales\Api\Data\OrderInterface;
use SwiftOtter\OrderExport\Model\HeaderData;

interface OrderDataCollectorInterface
{
    public function collect(OrderInterface $order, HeaderData $headerData): array;
}