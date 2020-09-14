<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$websiteRepository = $objectManager->get(\Magento\Store\Model\WebsiteRepository::class);


$indexBuilder = $objectManager->get(\Magento\CatalogRule\Model\Indexer\IndexBuilder::class);
$catalogRuleRepository = $objectManager->get(\Magento\CatalogRule\Api\CatalogRuleRepositoryInterface::class);
$catalogRuleFactory = $objectManager->get(\Magento\CatalogRule\Api\Data\RuleInterfaceFactory::class);

$baseWebsite = $websiteRepository->get('base');
$discountPercent = 20;

$catalogRule = $catalogRuleFactory->create(
    [
        'data' => [
            \Magento\CatalogRule\Api\Data\RuleInterface::IS_ACTIVE => 1,
            \Magento\CatalogRule\Api\Data\RuleInterface::NAME => 'Rule apply as percentage of original. Not logged user.',
            'customer_group_ids' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            \Magento\CatalogRule\Api\Data\RuleInterface::DISCOUNT_AMOUNT => $discountPercent,
            'website_ids' => [$baseWebsite->getId()],
            \Magento\CatalogRule\Api\Data\RuleInterface::SIMPLE_ACTION => 'by_percent',
            \Magento\CatalogRule\Api\Data\RuleInterface::STOP_RULES_PROCESSING => false,
            \Magento\CatalogRule\Api\Data\RuleInterface::SORT_ORDER => 0,
            'sub_is_enable' => 0,
            'sub_discount_amount' => 0,
        ]
    ]
);

$catalogRuleRepository->save($catalogRule);
$indexBuilder->reindexFull();
