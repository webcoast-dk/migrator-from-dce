<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class GroupFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array
    {
        $fieldConfiguration['type'] = FieldType::GROUP;
        $fieldConfiguration['config'] = [
            'behaviour' => [
                'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
            ],
            'autoSizeMax' => $dceConfiguration['autoSizeMax'] ?? null ?: null,
            'elementBrowserEntryPoints' => $dceConfiguration['elementBrowserEntryPoints'] ?? null ?: null,
            'fieldControl' => $dceConfiguration['fieldControl'] ?? null ?: null,
            'fieldInformation' => $dceConfiguration['fieldInformation'] ?? null ?: null,
            'fieldWizard' => $dceConfiguration['fieldWizard'] ?? null ?: null,
            'hideDeleteIcon' => $dceConfiguration['hideDeleteIcon'] ?? null ?: null,
            'hideMoveIcons' => $dceConfiguration['hideMoveIcons'] ?? null ?: null,
            'internal_type' => $dceConfiguration['internal_type'] ?? null ?: null,
            'maxitems' => $dceConfiguration['maxitems'] ?? null ?: null,
            'minitems' => $dceConfiguration['minitems'] ?? null ?: null,
            'multiple' => $dceConfiguration['multiple'] ?? null ?: null,
            'readOnly' => $dceConfiguration['readOnly'] ?? null ?: null,
            'size' => $dceConfiguration['size'] ?? null ?: null,
        ];

        return $fieldConfiguration;
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'group' && $configuration['internal_type'] === 'db' && ($dceConfiguration['appearance']['elementBrowserType'] ?? '') !== 'file';
    }
}
