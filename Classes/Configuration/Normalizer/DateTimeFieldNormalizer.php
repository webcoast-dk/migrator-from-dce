<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\Migrator\Migration\Field;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\Migrator\Utility\ArrayUtility;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class DateTimeFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(Field $normalizedField, array $dceConfiguration): void
    {
        $normalizedField->setType(FieldType::DATETIME);
        $eval = GeneralUtility::trimExplode(',', $dceConfiguration['eval'] ?? '');
        $format = null;
        foreach (['date', 'datetime', 'time', 'timesec'] as $possibleFormat) {
            if (in_array($possibleFormat, $eval, true)) {
                $format = $possibleFormat;
                break;
            }
        }
        $normalizedField->setConfiguration(
            ArrayUtility::removeEmptyValuesFromArray([
                'behaviour' => [
                    'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
                ],
                'dbType' => $dceConfiguration['dbType'] ?? null ?: null,
                'default' => $dceConfiguration['default'] ?? null,
                'disableAgeDisplay' => $dceConfiguration['disableAgeDisplay'] ?? null ?: null,
                'fieldControl' => $dceConfiguration['fieldControl'] ?? null ?: null,
                'fieldInformation' => $dceConfiguration['fieldInformation'] ?? null ?: null,
                'fieldWizard' => $dceConfiguration['fieldWizard'] ?? null ?: null,
                'format' => $format,
                'mode' => $dceConfiguration['mode'] ?? null ?: null,
                'nullable' => $dceConfiguration['nullable'] ?? null ?: in_array('null', $eval) ?: null,
                'placeholder' => $dceConfiguration['placeholder'] ?? null ?: null,
                'range' => [
                    'lower' => $dceConfiguration['range']['lower'] ?? null,
                    'upper' => $dceConfiguration['range']['upper'] ?? null,
                ],
                'readOnly' => $dceConfiguration['readOnly'] ?? null ?: null,
                'search' => $dceConfiguration['search'] ?? null ?: null,
                'softref' => $dceConfiguration['softref'] ?? null ?: null,
            ])
        );
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'text' && ($configuration['renderType'] ?? '') === 'inputDateTime';
    }
}
