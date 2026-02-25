<?php

declare(strict_types=1);


namespace WEBcoast\MigratorFromDce\Migration\FieldMigrator;


use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\MigratorFromDce\Migration\FieldConfigurationMigratorInterface;

class FileFieldMigrator implements FieldConfigurationMigratorInterface
{

    public function process(array $fieldConfiguration): array
    {
        if (
            ($fieldConfiguration['type'] === 'group' && $fieldConfiguration['internal_type'] === 'file')
            || ($fieldConfiguration['type'] === 'group' && $fieldConfiguration['internal_type'] === 'db' && ($fieldConfiguration['appearance']['elementBrowserType'] ?? '') === 'file')
            || ($fieldConfiguration['type'] === 'inline' && $fieldConfiguration['foreign_table'] === 'sys_file_reference')
        ) {
            $fieldConfiguration['type'] = FieldType::LEGACY_FILE;
            unset($fieldConfiguration['internal_type']);

            if ($fieldConfiguration['appearance']['elementBrowserAllowed'] ?? null) {
                $fieldConfiguration['allowed'] = $fieldConfiguration['appearance']['elementBrowserAllowed'];
                unset($fieldConfiguration['appearance']['elementBrowserAllowed'], $fieldConfiguration['appearance']['elementBrowserType']);
            } elseif ($fieldConfiguration['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed'] ?? null) {
                $fieldConfiguration['allowed'] = $fieldConfiguration['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed'];
                unset($fieldConfiguration['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserType'], $fieldConfiguration['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed']);
            } elseif ($fieldConfiguration['foreign_selector_fieldTcaOverride']['config']['appearance']['elementBrowserAllowed'] ?? null) {
                $fieldConfiguration['allowed'] = $fieldConfiguration['foreign_selector_fieldTcaOverride']['config']['appearance']['elementBrowserAllowed'];
                unset($fieldConfiguration['foreign_selector_fieldTcaOverride']['config']['appearance']['elementBrowserType'], $fieldConfiguration['foreign_selector_fieldTcaOverride']['config']['appearance']['elementBrowserAllowed']);
            }
            if ($fieldConfiguration['foreign_types'] ?? null) {
                $fieldConfiguration['overrideChildTca']['types'] = $fieldConfiguration['foreign_types'];
                unset($fieldConfiguration['foreign_types']);
            }
            if ((string) ($fieldConfiguration['appearance']['useSortable'] ?? 0) === '1') {
                unset($fieldConfiguration['appearance']['useSortable']);
            }

            if (empty($fieldConfiguration['appearance'])) {
                unset($fieldConfiguration['appearance']);
            }

            unset(
                $fieldConfiguration['size'],
                $fieldConfiguration['show_thumbs'],
                $fieldConfiguration['foreign_table'],
                $fieldConfiguration['foreign_field'],
                $fieldConfiguration['foreign_sortby'],
                $fieldConfiguration['foreign_table_field'],
                $fieldConfiguration['foreign_match_fields'],
                $fieldConfiguration['foreign_label'],
                $fieldConfiguration['foreign_selector']
            );
        }

        return $fieldConfiguration;
    }

    public function getDependencies(): array
    {
        return [];
    }
}
