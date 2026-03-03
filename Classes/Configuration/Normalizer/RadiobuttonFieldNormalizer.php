<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use WEBcoast\Migrator\Migration\Field;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\Migrator\Utility\ArrayUtility;
use WEBcoast\Migrator\Utility\TcaUtility;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class RadiobuttonFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(Field $normalizedField, array $dceConfiguration): void
    {
        $normalizedField->setType(FieldType::RADIO);
        $normalizedField->setConfiguration(
            ArrayUtility::removeEmptyValuesFromArray([
                'behavior' => [
                    'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
                ],
                'default' => $dceConfiguration['default'] ?? null ?: null,
                'fieldControl' => $dceConfiguration['fieldControl'] ?? null ?: null,
                'fieldInformation' => $dceConfiguration['fieldInformation'] ?? null ?: null,
                'fieldWizard' => $dceConfiguration['fieldWizard'] ?? null ?: null,
                'items' => TcaUtility::migrateItemsFormat($dceConfiguration['items'] ?? null ?: []),
                'itemsProcFunc' => $dceConfiguration['itemsProcFunc'] ?? null ?: null,
                'readOnly' => $dceConfiguration['readOnly'] ?? null ?: null,
            ])
        );
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'radio';
    }
}
