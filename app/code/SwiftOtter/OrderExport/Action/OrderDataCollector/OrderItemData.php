<?php

namespace SwiftOtter\OrderExport\Action\OrderDataCollector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use SwiftOtter\OrderExport\Action\GetOrderExportItems;
use SwiftOtter\OrderExport\Api\OrderDataCollectorInterface;
use SwiftOtter\OrderExport\Model\HeaderData;

class OrderItemData implements OrderDataCollectorInterface
{
    private GetOrderExportItems $getOrderExportItems;
    private ProductRepositoryInterface $productRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        GetOrderExportItems $getOrderExportItems,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->getOrderExportItems = $getOrderExportItems;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function collect(OrderInterface $order, HeaderData $headerData): array
    {
        $skus         = [];
        $items        = [];

        /** @var OrderItemInterface[] $orderItems */
        $orderItems   = $this->getOrderExportItems->execute($order);

        foreach ($orderItems as $orderItem){
            $skus[] = $orderItem->getSku();
        }

        $productBySku = $this->loadProducts($skus);

        foreach ($orderItems as $orderItem){
            $product = $productBySku[$orderItem->getSku()] ?? null;
            $items[] = $this->transform($orderItem, $product);
        }

        return [
            'items' => $items
        ];
    }

    private function transform(OrderItemInterface $orderItem, ?ProductInterface $product): array
    {
        return [
            "sku" => $this->getSku($orderItem, $product),
            "qty" => $orderItem->getQtyOrdered() ,
            "item_price" => $orderItem->getBasePrice(),
            "item_cost" => $orderItem->getBaseCost(),
            "total" => $orderItem->getBaseRowTotal()
        ];
    }


    /**
     * @param string[] $skus
     * @return ProductInterface[]
     */
    private function loadProducts(array $skus): array
    {
        $this->searchCriteriaBuilder->addFilter('sku', $skus, 'in');

        /** @var ProductInterface[] $products */
        $products = $this->productRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        $productsBySku = [];
        foreach ($products as $product){
            $productsBySku[$product->getSku()] = $product;
        }

        return $productsBySku;
    }

    private function getSku(OrderItemInterface $orderItem, ?ProductInterface $product): string
    {
        $sku = $orderItem->getSku();

        if($product === null){
            return $sku;
        }

        $skuOverride = $product->getCustomAttribute('sku_override');
        $skuOverrideVal = ($skuOverride !== null ) ? $skuOverride->getValue() : null;

        if (!empty($skuOverrideVal)){
            $sku = $skuOverrideVal;
        }

        return $sku;
    }
}