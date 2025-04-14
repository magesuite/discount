<?php

declare(strict_types=1);

$resolver = \Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance();
$resolver->requireDataFixture('Magento/GroupedProduct/_files/product_grouped_with_simple_rollback.php');
