<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\Migrator\Migration\Field;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\Migrator\Utility\ArrayUtility;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class ColorFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(Field $normalizedField, array $dceConfiguration): void
    {
        $normalizedField->setType(FieldType::COLOR);
        $eval = GeneralUtility::trimExplode(',', $dceConfiguration['eval'] ?? '', true);
        $normalizedField->setConfiguration(
            ArrayUtility::removeEmptyValuesFromArray([
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
            ])
        );
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'text' && ($configuration['renderType'] ?? '') === 'colorPicker';
    }
}
