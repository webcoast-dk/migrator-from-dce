<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\Migrator\Utility\TcaUtility;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class CheckboxFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array
    {
        $fieldConfiguration['type'] = FieldType::CHECKBOX;
        $fieldConfiguration['config'] = [
            'behaviour' => [
                'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
            ],
            'cols' => $dceConfiguration['cols'] ?? null ?: null,
            'default' => $dceConfiguration['default'] ?? null ?: null,
            'eval' => $dceConfiguration['eval'] ?? null ?: null,
            'fieldInformation' => $dceConfiguration['fieldInformation'] ?? null ?: null,
            'fieldWizard' => $dceConfiguration['fieldWizard'] ?? null ?: null,
            'invertStateDisplay' => $dceConfiguration['invertStateDisplay'] ?? null ?: null,
            'items' => TcaUtility::migrateItemsFormat($dceConfiguration['items'] ?? null ?: []),
            'itemsProcFunc' => $dceConfiguration['itemsProcFunc'] ?? null ?: null,
            'readOnly' => $dceConfiguration['readOnly'] ?? null ?: null,
            'renderType' => $dceConfiguration['renderType'] ?? null ?: null,
            'validation' => $dceConfiguration['validation'] ?? null ?: null,
        ];

        return $fieldConfiguration;
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'check';
    }
}
