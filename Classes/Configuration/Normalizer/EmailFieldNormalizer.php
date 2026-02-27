<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class EmailFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array
    {
        $eval = GeneralUtility::trimExplode(',', $dceConfiguration['eval'] ?? '', true);
        $eval = ArrayUtility::removeArrayEntryByValue($eval, 'email');
        $fieldConfiguration['type'] = FieldType::EMAIL;
        $fieldConfiguration['config'] = [
            'behaviour' => [
                'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
            ],
            'autocomplete' => $dceConfiguration['autocomplete'] ?? null ?: null,
            'eval' => implode(',', $eval) ?: null,
            'fieldInformation' => $dceConfiguration['fieldInformation'] ?? null ?: null,
            'fieldWizard' => $dceConfiguration['fieldWizard'] ?? null ?: null,
            'mode' => $dceConfiguration['mode'] ?? null ?: null,
            'nullable' => $dceConfiguration['nullable'] ?? null ?: in_array('null', $eval) ?: null,
            'placeholder' => $dceConfiguration['placeholder'] ?? null ?: null,
            'readOnly' => $dceConfiguration['readOnly'] ?? null ?: null,
            'required' => in_array('required', $eval) ?: null,
            'size' => $dceConfiguration['size'] ?? null ?: null,
        ];

        return $fieldConfiguration;
    }

    public function supports(array $configuration): bool
    {
        if ($configuration['type'] === 'text') {
            $eval = GeneralUtility::trimExplode(',', $configuration['eval'] ?? '', true);

            return in_array('email', $eval);
        }

        return $configuration['type'] === 'email';
    }
}
