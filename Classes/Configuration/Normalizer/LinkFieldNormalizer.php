<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class LinkFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array
    {
        $eval = GeneralUtility::trimExplode(',', $dceConfiguration['eval'] ?? '', true);
        $fieldConfiguration['type'] = FieldType::LINK;
        $fieldConfiguration['config'] = [
            'allowedTypes' => $dceConfiguration['allowedTypes'] ?? null ?: null,
            'behaviour' => [
                'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
            ],
            'appearance' => [
                'allowedOptions' => $dceConfiguration['appearance']['allowedOptions'] ?? null ?: null,
                'allowedFilesExtensions' => $dceConfiguration['appearance']['allowedFilesExtensions'] ?? null ?: null,
                'browserTitle' => $dceConfiguration['appearance']['browserTitle'] ?? null ?: null,
                'enableBrowser' => $dceConfiguration['appearance']['enableBrowser'] ?? null ?: null,
            ],
            'autocomplete' => $dceConfiguration['autocomplete'] ?? null ?: null,
            'default' => $dceConfiguration['default'] ?? null,
            'fieldControl' => $dceConfiguration['fieldControl'] ?? null ?: null,
            'fieldInformation' => $dceConfiguration['fieldInformation'] ?? null ?: null,
            'fieldWizard' => $dceConfiguration['fieldWizard'] ?? null ?: null,
            'mode' => $dceConfiguration['mode'] ?? null ?: null,
            'nullable' => $dceConfiguration['nullable'] ?? null ?: in_array('null', $eval) ?: null,
            'placeholder' => $dceConfiguration['placeholder'] ?? null,
            'readOnly' => $dceConfiguration['readOnly'] ?? null ?: null,
            'required' => $dceConfiguration['required'] ?? in_array('required', $eval) ?: null,
            'search' => $dceConfiguration['search'] ?? null ?: null,
            'size' => $dceConfiguration['size'] ?? null,
            'valuePicker' => $dceConfiguration['valuePicker'] ?? null ?: null,
        ];

        return $fieldConfiguration;
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'text' && ($configuration['renderType'] ?? '') === 'inputLink'
            || $configuration['type'] === 'link';
    }
}
