<?php

declare(strict_types=1);

namespace IntegerNet\CliScopeHint\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ScopeHintService
{
    private ScopeConfigInterface $scopeConfig;
    private StoreManagerInterface $storeManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function getConfigValuesForScopes(
        string $configPath
    ): array {
        $configValues = [];

        // global scope
        $configValues[] = [
            'website' => '',
            'store'   => '',
            'value'   => $this->scopeConfig->getValue($configPath),
        ];

        foreach ($this->storeManager->getWebsites() as $website) {
            $configValues[] = [
                'website' => $website->getCode(),
                'store'   => '',
                'value'   => $this->scopeConfig->getValue(
                    $configPath,
                    ScopeInterface::SCOPE_WEBSITE,
                    $website->getId()
                ),
            ];

            foreach ($this->storeManager->getStores() as $store) {
                if ($store->getWebsiteId() !== $website->getId()) {
                    continue;
                }

                $configValues[] = [
                    'website' => '',
                    'store'   => $store->getCode(),
                    'value'   => $this->scopeConfig->getValue(
                        $configPath,
                        ScopeInterface::SCOPE_STORES,
                        $store->getId()
                    ),
                ];
            }
        }

        return $configValues;
    }
}
