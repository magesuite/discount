<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$simpleProduct = $productRepository->get('simple_10');
$simpleProduct->setSpecialPrice(1);
$productRepository->save($simpleProduct);

$simpleProduct = $productRepository->get('simple_20');
$simpleProduct->setSpecialPrice(6.5);
$productRepository->save($simpleProduct);

$configurableProduct = $productRepository->get('configurable');
$configurableProduct->reindex();
$configurableProduct->priceReindexCallback();

$productRepository->save($configurableProduct);
