<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\Migrator\Utility\TcaUtility;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class RadiobuttonFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array
    {
        $fieldConfiguration['type'] = FieldType::RADIO;
        $fieldConfiguration['config'] = [
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
        ];

        return $fieldConfiguration;
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'radio';
    }
}
