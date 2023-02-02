<?php

namespace IntegerNet\CliScopeHint\Test\Integration\Controller;

use IntegerNet\CliScopeHint\Service\ScopeHintService;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @magentoAppIsolation enabled
 */
class ScopeHintServiceTest extends TestCase
{
    public function testGlobalConfig()
    {
        ConfigFixture::setGlobal('general/store_information/name', 'Test Shop');

        $scopeHintService = Bootstrap::getObjectManager()->create(ScopeHintService::class);
        $general_store_name = $scopeHintService->getConfigValuesForScopes('general/store_information/name')[0]['values'];

        $this->assertEquals('Test Shop', $general_store_name);
    }

    public function testWebsiteConfig()
    {
        ConfigFixture::setForStore('general/store_information/name', 'Test Shop', 'website');

        $scopeHintService = Bootstrap::getObjectManager()->create(ScopeHintService::class);
        $store_name = $scopeHintService->getConfigValuesForScopes('general/store_information/name')[1]['values'];

        $this->assertEquals('Test Shop', $store_name);
    }

    public function testStoreConfig()
    {
        ConfigFixture::setForStore('general/store_information/name', 'Test Shop', 'default');

        $scopeHintService = Bootstrap::getObjectManager()->create(ScopeHintService::class);
        $store_name = $scopeHintService->getConfigValuesForScopes('general/store_information/name')[2]['values'];

        $this->assertEquals('Test Shop', $store_name);
    }
}
