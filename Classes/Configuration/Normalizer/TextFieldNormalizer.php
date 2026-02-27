<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class TextFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array
    {
        $eval = GeneralUtility::trimExplode(',', $dceConfiguration['eval'], true);
        $fieldConfiguration['type'] = FieldType::TEXT;
        $fieldConfiguration['config'] = [
            'behaviour' => [
                'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
            ],
            'autocomplete' => $dceConfiguration['autocomplete'] ?? null ?: null,
            'default' => $dceConfiguration['default'] ?? null,
            'eval' => $dceConfiguration['eval'] ?? null ?: null,
            'fieldControl' => $dceConfiguration['fieldControl'] ?? null ?: null,
            'fieldInformation' => $dceConfiguration['fieldInformation'] ?? null ?: null,
            'fieldWizard' => $dceConfiguration['fieldWizard'] ?? null ?: null,
            'is_in' => $dceConfiguration['is_in'] ?? null ?: null,
            'max' => $dceConfiguration['max'] ?? null ?: null,
            'min' => $dceConfiguration['min'] ?? null,
            'mode' => $dceConfiguration['mode'] ?? null ?: null,
            'nullable' => $dceConfiguration['nullable'] ?? null ?: in_array('null', $eval) ?: null,
            'placeholder' => $dceConfiguration['placeholder'] ?? null ?: null,
            'readOnly' => $dceConfiguration['readOnly'] ?? null ?: null,
            'required' => $dceConfiguration['required'] ?? in_array('required', $eval) ?: null,
            'search' => $dceConfiguration['search'] ?? null ?: null,
            'size' => $dceConfiguration['size'] ?? null ?: null,
            'softref' => $dceConfiguration['softref'] ?? null ?: null,
            'valuePicker' => $dceConfiguration['valuePicker'] ?? null ?: null,
        ];

        return $fieldConfiguration;
    }

    public function supports(array $configuration): bool
    {
        if ($configuration['type'] === 'input' && ($configuration['renderType'] ?? '') === '') {
            $eval = GeneralUtility::trimExplode(',', $configuration['eval'] ?? '', true);

            return !in_array('email', $eval)
                && !in_array('int', $eval)
                && !in_array('double2', $eval)
                && !in_array('password', $eval)
                && !in_array('saltedPassword', $eval);
        }

        return false;
    }
}
