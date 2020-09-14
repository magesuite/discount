<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$indexBuilder = $objectManager->get(\Magento\CatalogRule\Model\Indexer\IndexBuilder::class);
$ruleRepository = $objectManager->create(\Magento\CatalogRule\Api\CatalogRuleRepositoryInterface::class);
$ruleCollectionFactory = $objectManager->get(\Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory::class);

$ruleCollection = $ruleCollectionFactory->create();
$ruleCollection->addFieldToFilter('name', ['eq' => 'Rule apply as percentage of original. Not logged user.']);
$ruleCollection->setPageSize(1);


$rule = $ruleCollection->getFirstItem();
if ($rule->getId()) {
    $ruleRepository->delete($rule);
}

$indexBuilder->reindexFull();
