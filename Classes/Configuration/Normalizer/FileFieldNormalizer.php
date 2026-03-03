<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use WEBcoast\Migrator\Migration\Field;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\Migrator\Utility\ArrayUtility;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class FileFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(Field $normalizedField, array $dceConfiguration): void
    {
        if (
            ($dceConfiguration['type'] === 'group' && $dceConfiguration['internal_type'] === 'file')
            || ($dceConfiguration['type'] === 'group' && $dceConfiguration['internal_type'] === 'db' && ($dceConfiguration['appearance']['elementBrowserType'] ?? '') === 'file')) {
            $normalizedField->setType(FieldType::LEGACY_FILE);
        } elseif (
            $dceConfiguration['type'] === 'inline' && $dceConfiguration['foreign_table'] === 'sys_file_reference'
            || $dceConfiguration['type'] === 'file'
        ) {
            $normalizedField->setType(FieldType::FILE);
        }

        $normalizedConfiguration = [
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
            $normalizedConfiguration['allowed'] = $dceConfiguration['appearance']['elementBrowserAllowed'];
        } elseif ($dceConfiguration['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed'] ?? null) {
            $normalizedConfiguration['allowed'] = $dceConfiguration['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed'];
        } elseif ($dceConfiguration['foreign_selector_fieldTcaOverride']['config']['appearance']['elementBrowserAllowed'] ?? null) {
            $normalizedConfiguration['allowed'] = $dceConfiguration['foreign_selector_fieldTcaOverride']['config']['appearance']['elementBrowserAllowed'];
        }
        if ($dceConfiguration['foreign_types'] ?? null) {
            $normalizedConfiguration['overrideChildTca']['types'] = $dceConfiguration['foreign_types'];
        }
        unset ($normalizedConfiguration['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed'], $normalizedConfiguration['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserType']);

        $normalizedField->setConfiguration(
            ArrayUtility::removeEmptyValuesFromArray($normalizedConfiguration)
        );
    }

    public function supports(array $configuration): bool
    {
        return ($configuration['type'] === 'group' && $configuration['internal_type'] === 'file')
            || ($configuration['type'] === 'group' && $configuration['internal_type'] === 'db' && ($configuration['appearance']['elementBrowserType'] ?? '') === 'file')
            || ($configuration['type'] === 'inline' && $configuration['foreign_table'] === 'sys_file_reference')
            || ($configuration['type'] === 'file');
    }
}
