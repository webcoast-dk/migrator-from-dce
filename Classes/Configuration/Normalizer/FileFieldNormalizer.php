<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class FileFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array
    {
        if (
            ($dceConfiguration['type'] === 'group' && $dceConfiguration['internal_type'] === 'file')
            || ($dceConfiguration['type'] === 'group' && $dceConfiguration['internal_type'] === 'db' && ($dceConfiguration['appearance']['elementBrowserType'] ?? '') === 'file')) {
            $fieldConfiguration['type'] = FieldType::LEGACY_FILE;
            // Keep upload folder for legacy file fields, as it is needed for resolving the file when preparing the data for migration
            $fieldConfiguration['uploadFolder'] = $dceConfiguration['uploadfolder'] ?? null;
        } elseif (
            $dceConfiguration['type'] === 'inline' && $dceConfiguration['foreign_table'] === 'sys_file_reference'
            || $dceConfiguration['type'] === 'file'
        ) {
            $fieldConfiguration['type'] = FieldType::FILE;
        }

        $fieldConfiguration['config'] = [
            'appearance' => [
                'collapseAll' => $dceConfiguration['collapseAll'] ?? null,
                'expandSingle' => $dceConfiguration['expandSingle'] ?? null,
                'createNewRelationLinkTitle' => $dceConfiguration['createNewRelationLinkTitle'] ?? null,
                'showPossibleLocalizationRecords' => $dceConfiguration['showPossibleLocalizationRecords'] ?? null,
                'showAllLocalizationLink' => $dceConfiguration['showAllLocalizationLink'] ?? null,
                'showSynchronizationLink' => $dceConfiguration['showSynchronizationLink'] ?? null,
                'enabledControls' => $dceConfiguration['enabledControls'] ?? null,
                'headerThumbnail' => $dceConfiguration['headerThumbnail'] ?? null,
                'fileUploadAllowed' => $dceConfiguration['appearance']['fileUploadAllowed'] ?? null,
                'fileByUrlAllowed' => $dceConfiguration['appearance']['fileByUrlAllowed'] ?? null,
                'elementBrowserEnabled' => $dceConfiguration['appearance']['elementBrowserEnabled'] ?? null,
            ],
            'behaviour' => [
                'localizationMode' => $dceConfiguration['behaviour']['localizationMode'] ?? null,
                'disableMovingChildrenWithParent' => $dceConfiguration['behaviour']['disableMovingChildrenWithParent'] ?? null,
            ],
            'maxitems' => $dceConfiguration['maxitems'] ?? null,
            'minitems' => $dceConfiguration['minitems'] ?? null,
            'overrideChildTca' => $dceConfiguration['overrideChildTca'] ?? null,
        ];

        if ($dceConfiguration['appearance']['elementBrowserAllowed'] ?? null) {
            $fieldConfiguration['config']['allowed'] = $dceConfiguration['appearance']['elementBrowserAllowed'];
        } elseif ($dceConfiguration['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed'] ?? null) {
            $fieldConfiguration['config']['allowed'] = $dceConfiguration['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed'];
        } elseif ($dceConfiguration['foreign_selector_fieldTcaOverride']['config']['appearance']['elementBrowserAllowed'] ?? null) {
            $fieldConfiguration['config']['allowed'] = $dceConfiguration['foreign_selector_fieldTcaOverride']['config']['appearance']['elementBrowserAllowed'];
        }
        if ($dceConfiguration['foreign_types'] ?? null) {
            $fieldConfiguration['config']['overrideChildTca']['types'] = $dceConfiguration['foreign_types'];
        }
        unset ($fieldConfiguration['config']['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed'], $fieldConfiguration['config']['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserType']);

        return $fieldConfiguration;
    }

    public function supports(array $configuration): bool
    {
        return ($configuration['type'] === 'group' && $configuration['internal_type'] === 'file')
            || ($configuration['type'] === 'group' && $configuration['internal_type'] === 'db' && ($configuration['appearance']['elementBrowserType'] ?? '') === 'file')
            || ($configuration['type'] === 'inline' && $configuration['foreign_table'] === 'sys_file_reference')
            || ($configuration['type'] === 'file');
    }
}
