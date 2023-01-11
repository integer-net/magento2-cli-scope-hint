<?php

declare(strict_types=1);

namespace IntegerNet\CliScopeHint\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

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

        $resultArray = $this->parseArrayRecursively($configArray);

        return $resultArray;

    }

    private function parseArrayRecursively($array, ?string $path = null) : array {

        $resultArray = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $resultArray[] = $this->parseArrayRecursively($value, ($path ? $path . '/' : $path ). $key);
            } else {
                $resultArray[] = $path . '/' . (!is_numeric($key) ? $key : $value); }
        }

        return $this->flatten($resultArray);
    }

    private function flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }

    public function getScopesAsArray(): array
    {
        $resultArray = [];

        $scopeArray = $this->scopeConfig->getValue(null);
        foreach ($scopeArray as $element)
        {
            $resultArray[] = $this->getConfigPathName($element);
        }

        return $resultArray;
    }

    private function getConfigPathName($element, string $previousPath = ""): string
    {
        if(is_array($element)) {
            foreach ($element as $arrayName=>$arrayElements)
            {
                $previousPath .= '/' . $arrayName;
                $this->getConfigPathName($arrayElements, $previousPath);
            }
        }
        else
        {
            return $previousPath;
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

        return $configValues;
    }
}
