<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$simpleProduct = $productRepository->get('simple_10');
$simpleProduct
    ->setSpecialPrice(1)
    ->setSpecialFromDate(date('Y-m-d', strtotime('-6 day')))
    ->setSpecialToDate(date('Y-m-d', strtotime('-3 day')));

$productRepository->save($simpleProduct);
$simpleProduct->priceReindexCallback();

$simpleProduct = $productRepository->get('simple_20');
$simpleProduct
    ->setSpecialPrice(6.5)
    ->setSpecialFromDate(date('Y-m-d', strtotime('-3 day')))
    ->setSpecialToDate(date('Y-m-d', strtotime('+3 day')));

$productRepository->save($simpleProduct);
$simpleProduct->priceReindexCallback();

$configurableProduct = $productRepository->get('configurable');
$configurableProduct->reindex();
$configurableProduct->priceReindexCallback();

$productRepository->save($configurableProduct);
