<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$bundleProduct = $productRepository->get('bundle-product');
$bundleProduct->setSpecialPrice(65);

$productRepository->save($bundleProduct);
