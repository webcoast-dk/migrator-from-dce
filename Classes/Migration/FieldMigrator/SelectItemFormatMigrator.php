<?php

declare(strict_types=1);


namespace WEBcoast\MigratorFromDce\Migration\FieldMigrator;


use WEBcoast\Migrator\Utility\TcaUtility;
use WEBcoast\MigratorFromDce\Migration\FieldConfigurationMigratorInterface;

class SelectItemFormatMigrator implements FieldConfigurationMigratorInterface
{

    public function process(array $fieldConfiguration): array
    {
        if ($fieldConfiguration['type'] === 'select' && $fieldConfiguration['items'] ?? null) {
            $fieldConfiguration['items'] = TcaUtility::migrateItemsFormat($fieldConfiguration['items']);
        }

        return $fieldConfiguration;
    }

    public function getDependencies(): array
    {
        return [];
    }
}
