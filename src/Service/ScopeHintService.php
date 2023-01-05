<?php

declare(strict_types=1);

namespace IntegerNet\CliScopeHint\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use IntegerNet\CliScopeHint\Service\TreePaths;

class ScopeHintService
{
    private ScopeConfigInterface                       $scopeConfig;
    private StoreManagerInterface                      $storeManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function getAllScopes() : array
    {
        //$configArray = $this->treePaths->generate($this->scopeConfig->getValue(null));

        //$all_configs = $this->scopeConfig->getValue(null);


        return [];
    }

    public function getScopesAsArray(): array
    {
        $result_array = [];

        $scope_array = $this->scopeConfig->getValue(null);
        foreach ($scope_array as $element)
        {
            $result_array[] = $this->getConfigPathName($element);
        }

        return $result_array;
    }

    private function getConfigPathName($element, string $previous_path = ""): string
    {
        if(is_array($element)) {
            foreach ($element as $array_name=>$array_elements)
            {
                $previous_path .= '/' . $array_name;
                $this->getConfigPathName($array_elements, $previous_path);
            }
        }
        else
        {
            return $previous_path;
        }

        return "";
    }

    public function getConfigValuesForScopes(
        string $configPath
    ): array {
        $configValues = [];

        //$allConfigPaths = $this->getScopesAsArray();

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
