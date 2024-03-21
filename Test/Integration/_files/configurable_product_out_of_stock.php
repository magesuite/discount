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

$processor = $objectManager->get(\Magento\InventoryCatalogApi\Model\SourceItemsProcessorInterface::class);
$data = [
    ['source_code'=>'default', 'status'=>0, 'quantity'=>0],
];
foreach (['simple_10', 'simple_20'] as $sku) {
    $processor->execute($sku, $data);
}

$configurableProduct = $productRepository->get('configurable');
$configurableProduct->reindex();
$configurableProduct->priceReindexCallback();

$productRepository->save($configurableProduct);
