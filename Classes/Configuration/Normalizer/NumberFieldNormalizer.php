<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\Migrator\Migration\Field;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\Migrator\Utility\ArrayUtility;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class NumberFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(Field $normalizedField, array $dceConfiguration): void
    {
        $eval = GeneralUtility::trimExplode(',', $dceConfiguration['eval'] ?? '', true);
        $normalizedField->setType(FieldType::NUMBER);
        $normalizedField->setConfiguration(
            ArrayUtility::removeEmptyValuesFromArray([
                'behaviour' => [
                    'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
                ],
                'autocomplete' => $dceConfiguration['autocomplete'] ?? null ?: null,
                'default' => $dceConfiguration['default'] ?? null,
                'fieldControl' => $dceConfiguration['fieldControl'] ?? null ?: null,
                'fieldInformation' => $dceConfiguration['fieldInformation'] ?? null ?: null,
                'fieldWizard' => $dceConfiguration['fieldWizard'] ?? null ?: null,
                'format' => in_array('int', $eval) ? null : 'decimal',
                'mode' => $dceConfiguration['mode'] ?? null ?: null,
                'nullable' => $dceConfiguration['nullable'] ?? null ?: in_array('null', $eval) ?: null,
                'range' => [
                    'lower' => $dceConfiguration['range']['lower'] ?? null,
                    'upper' => $dceConfiguration['range']['upper'] ?? null,
                ],
                'readOnly' => $dceConfiguration['readOnly'] ?? null ?: null,
                'required' => $dceConfiguration['required'] ?? in_array('required', $eval) ?: null,
                'size' => $dceConfiguration['size'] ?? null,
                'slider' => $dceConfiguration['slider'] ?? null ?: null,
                'valuePicker' => $dceConfiguration['valuePicker'] ?? null ?: null,
            ])
        );
    }

    public function supports(array $configuration): bool
    {
        if ($configuration['type'] === 'input' && ($configuration['renderType'] ?? '') === '') {
            $eval = GeneralUtility::trimExplode(',', $configuration['eval'] ?? '', true);

            return in_array('int', $eval, true) || in_array('double2', $eval, true);
        }

        return false;
    }
}
