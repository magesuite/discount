<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Model\ProductRepository $productRepository */
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

/** @var \Magento\Catalog\Model\Product $simpleProduct */
$simpleProduct = $productRepository->get('simple_10');
$simpleProduct->setSpecialPrice(1);
$simpleProduct->setStockData([
   'qty' => 0,
   'is_in_stock' => false
]);
$productRepository->save($simpleProduct);

/** @var \Magento\Catalog\Model\Product $simpleProduct */
$simpleProduct = $productRepository->get('simple_20');
$simpleProduct->setSpecialPrice(6.5);
$simpleProduct->setStockData([
    'qty' => 0,
    'is_in_stock' => false
]);
$productRepository->save($simpleProduct);

$configurableProduct = $productRepository->get('configurable');
$configurableProduct->reindex();
$configurableProduct->priceReindexCallback();

$productRepository->save($configurableProduct);
