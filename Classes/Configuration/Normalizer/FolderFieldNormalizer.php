<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use WEBcoast\Migrator\Migration\Field;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class FolderFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(Field $normalizedField, array $dceConfiguration): void
    {
        $normalizedField->setType(FieldType::FOLDER);
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'group' && $configuration['internal_type'] === 'folder'
            || $configuration['type'] === 'folder';
    }
}
