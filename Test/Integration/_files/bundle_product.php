<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$stockRegistry = $objectManager->get(\Magento\CatalogInventory\Api\StockRegistryInterface::class);

$sampleProduct = $productRepository->get('simple');
$sampleProduct->setCustomerGroupId(\Magento\Customer\Model\Group::CUST_GROUP_ALL);
$productRepository->save($sampleProduct);

$simpleStockItem = $stockRegistry->getStockItemBySku('simple');
$simpleStockItem->setIsInStock(true)->setQty(22);
$stockRegistry->updateStockItemBySku('simple', $simpleStockItem);

$bundleProduct = $productRepository->get('bundle-product');
$bundleProduct->setPrice(100);
$bundleProduct->setSpecialPrice(65);
$productRepository->save($bundleProduct);

$bundleProduct->priceReindexCallback();
