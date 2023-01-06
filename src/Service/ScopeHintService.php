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
        $configArray = $this->scopeConfig->getValue(null);

        $result = array();
        $this->displayArrayRecursively($configArray, $result, "");
        return $result;
    }

    function displayArrayRecursively($array, &$result_array, $path) : void {

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->displayArrayRecursively($value, $result_array, $path . $key . '/');
            } else {
                $result_array[] = $path . $key;
            }
        }
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

        // global scope
        $configValues[] = [
            'scope' => 'default',
            'scope_id'   => '',
            'path' => $configPath,
            'values'   => $this->scopeConfig->getValue($configPath),
        ];

        foreach ($this->storeManager->getWebsites() as $website) {
            $configValues[] = [
                'scope' => 'website',
                'scope_id'   => $website->getCode(),
                'path' => $configPath,
                'values'   => $this->scopeConfig->getValue(
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
                    'scope' => 'store',
                    'scope_id'   => $store->getCode(),
                    'path' => $configPath,
                    'values'   => $this->scopeConfig->getValue(
                        $configPath,
                        ScopeInterface::SCOPE_STORES,
                        $store->getId()
                    ),
                ];
            }
        }

        $configValues[0]["values"] = str_replace('\n', ' ', $configValues[0]["values"]);
        $configValues[1]["values"] = str_replace('\n', ' ', $configValues[1]["values"]);
        $configValues[2]["values"] = str_replace('\n', ' ', $configValues[2]["values"]);
        return $configValues;
    }
}
