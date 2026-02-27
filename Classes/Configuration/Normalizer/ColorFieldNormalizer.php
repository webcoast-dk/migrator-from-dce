<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class ColorFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array
    {
        $fieldConfiguration['type'] = FieldType::COLOR;
        $eval = GeneralUtility::trimExplode(',', $dceConfiguration['eval'] ?? '', true);
        $fieldConfiguration['config'] = [
            'behaviour' => [
                'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
            ],
            'default' => $dceConfiguration['default'] ?? null,
            'fieldControl' => $dceConfiguration['fieldControl'] ?? null ?: null,
            'fieldInformation' => $dceConfiguration['fieldInformation'] ?? null ?: null,
            'fieldWizard' => $dceConfiguration['fieldWizard'] ?? null ?: null,
            'mode' => $dceConfiguration['mode'] ?? null ?: null,
            'nullable' => $dceConfiguration['nullable'] ?? null ?: in_array('null', $eval) ?: null,
            'placeholder' => $dceConfiguration['placeholder'] ?? null,
            'readOnly' => $dceConfiguration['readOnly'] ?? null ?: null,
            'required' => $dceConfiguration['required'] ?? in_array('required', $eval) ?: null,
            'size' => $dceConfiguration['size'] ?? null,
            'valuePicker' => $dceConfiguration['valuePicker'] ?? null ?: null,
        ];

        return $fieldConfiguration;
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'text' && ($configuration['renderType'] ?? '') === 'colorPicker';
    }
}
