<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class TextareaFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array
    {
        $eval = GeneralUtility::trimExplode(',', $dceConfiguration['eval'] ?? '', true);
        $fieldConfiguration['type'] = FieldType::TEXTAREA;
        $fieldConfiguration['config'] = [
            'renderType' => $dceConfiguration['renderType'] ?? null ?: null,
            'behaviour' => [
                'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
            ],
            'cols' => $dceConfiguration['cols'] ?? null ?: null,
            'default' => $dceConfiguration['default'] ?? null ?: null,
            'enableRichtext' => $dceConfiguration['enableRichtext'] ?? null ?: null,
            'enableTabulator' => $dceConfiguration['enableTabulator'] ?? null ?: null,
            'eval' => $dceConfiguration['eval'] ?? null ?: null,
            'fieldControl' => $dceConfiguration['fieldControl'] ?? null ?: null,
            'fieldInformation' => $dceConfiguration['fieldInformation'] ?? null ?: null,
            'fieldWizard' => $dceConfiguration['fieldWizard'] ?? null ?: null,
            'fixedFont' => $dceConfiguration['fixedFont'] ?? null ?: null,
            'format' => $dceConfiguration['format'] ?? null ?: null,
            'is_in' => $dceConfiguration['is_in'] ?? null ?: null,
            'max' => $dceConfiguration['max'] ?? null ?: null,
            'min' => $dceConfiguration['min'] ?? null ?: null,
            'nullable' => $dceConfiguration['nullable'] ?? null ?: in_array('null', $eval) ?: null,
            'placeholder' => $dceConfiguration['placeholder'] ?? null ?: null,
            'readOnly' => $dceConfiguration['readOnly'] ?? null ?: null,
            'required' => $dceConfiguration['required'] ?? null ?: null,
            'richtextConfiguration' => $dceConfiguration['richtextConfiguration'] ?? null ?: null,
            'rows' => $dceConfiguration['rows'] ?? null ?: null,
            'search' => $dceConfiguration['search'] ?? null ?: null,
            'softref' => $dceConfiguration['softref'] ?? null ?: null,
            'wrap' => $dceConfiguration['wrap'] ?? null ?: null,
        ];

        return $fieldConfiguration;
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'text';
    }
}
