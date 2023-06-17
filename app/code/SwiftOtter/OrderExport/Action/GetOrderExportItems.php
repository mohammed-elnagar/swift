<?php

namespace SwiftOtter\OrderExport\Action;

use Magento\Sales\Api\Data\OrderInterface;

class GetOrderExportItems
{

//    private $allowedTypes = [
//        Type::TYPE_SIMPLE,
//        Type::TYPE_VIRTUAL
//    ];

    private array $allowedTypes;

    public function __construct(
        array $allowedTypes = []
    ){
        $this->allowedTypes = $allowedTypes;
    }

    public function execute(OrderInterface $order): array
    {
        $items = [];
        foreach ($order->getItems() as $orderItem){
            if(in_array($orderItem->getProductType(), $this->allowedTypes)){
                $items[] = $orderItem;
            }
        }

        return $items;
    }
}