<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productId = 884;
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);

$product->load($productId);
if ($product->getId()) {
    $product->delete();
}
