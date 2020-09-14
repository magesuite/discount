<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$sampleProduct = $productRepository->get('simple');
$sampleProduct->setCustomerGroupId(\Magento\Customer\Model\Group::CUST_GROUP_ALL);
$productRepository->save($sampleProduct);

$bundleProduct = $productRepository->get('bundle-product');
$bundleProduct->setSpecialPrice(65);

$bundleProduct->reindex();
$bundleProduct->priceReindexCallback();

$productRepository->save($bundleProduct);
