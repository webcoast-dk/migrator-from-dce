<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class FolderFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array
    {
        $fieldConfiguration['type'] = FieldType::FOLDER;

        return $fieldConfiguration;
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'group' && $configuration['internal_type'] === 'folder'
            || $configuration['type'] === 'folder';
    }
}
