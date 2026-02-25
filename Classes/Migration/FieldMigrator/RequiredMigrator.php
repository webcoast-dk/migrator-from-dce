<?php

declare(strict_types=1);


namespace WEBcoast\MigratorFromDce\Migration\FieldMigrator;


use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\MigratorFromDce\Migration\FieldConfigurationMigratorInterface;

class RequiredMigrator implements FieldConfigurationMigratorInterface
{

    public function process(array $fieldConfiguration): array
    {
        if ($fieldConfiguration['eval'] ?? '') {
            $eval = GeneralUtility::trimExplode(',', $fieldConfiguration['eval']);
            if (in_array('required', $eval)) {
                $fieldConfiguration['required'] = true;
                $eval = ArrayUtility::removeArrayEntryByValue($eval, 'required');
            }
            $fieldConfiguration['eval'] = implode(',', $eval);
        }

        return $fieldConfiguration;
    }

    public function getDependencies(): array
    {
        return [];
    }
}
