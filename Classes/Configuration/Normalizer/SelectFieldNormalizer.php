<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\Migrator\Utility\TcaUtility;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class SelectFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array
    {
        $fieldConfiguration['type'] = FieldType::SELECT;
        $fieldConfiguration['config'] = [
            'renderType' => $dceConfiguration['renderType'] ?? null ?: 'selectSingle',
            'allowNonIdValues' => $dceConfiguration['allowNonIdValues'] ?? null ?: null,
            'appearance' => [
                'selectCheckBox' => $dceConfiguration['appearance']['selectCheckBox'] ?? null ?: null,
            ],
            'authMode' => $dceConfiguration['authMode'] ?? null ?: null,
            'autoSizeMax' => $dceConfiguration['autoSizeMax'] ?? null ?: null,
            'behaviour' => [
                'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
            ],
            'default' => $dceConfiguration['default'] ?? null,
            'disableNoMatchingValueElement' => $dceConfiguration['disableNoMatchingValueElement'] ?? null ?: null,
            'dontRemapTablesOnCopy' => $dceConfiguration['dontRemapTablesOnCopy'] ?? null ?: null,
            'exclusiveKeys' => $dceConfiguration['exclusiveKeys'] ?? null ?: null,
            'fieldControl' => $dceConfiguration['fieldControl'] ?? null ?: null,
            'fieldInformation' => $dceConfiguration['fieldInformation'] ?? null ?: null,
            'fieldWizard' => $dceConfiguration['fieldWizard'] ?? null ?: null,
            'fileFolderConfig' => [
                'allowedExtensions' => $dceConfiguration['fileFolderConfig']['allowedExtensions'] ?? null ?: null,
                'depth' => $dceConfiguration['fileFolderConfig']['depth'] ?? null ?: null,
                'folder' => $dceConfiguration['fileFolderConfig']['folder'] ?? null ?: null,
            ],
            'foreign_table' => $dceConfiguration['foreign_table'] ?? null ?: null,
            'foreign_table_item_group' => $dceConfiguration['foreign_table_item_group'] ?? null ?: null,
            'foreign_table_prefix' => $dceConfiguration['foreign_table_prefix'] ?? null ?: null,
            'foreign_table_where' => $dceConfiguration['foreign_table_where'] ?? null ?: null,
            'itemGroups' => $dceConfiguration['itemGroups'] ?? null ?: null,
            'items' => TcaUtility::migrateItemsFormat($dceConfiguration['items'] ?? null ?: []),
            'itemsProcFunc' => $dceConfiguration['itemsProcFunc'] ?? null ?: null,
            'localizeReferencesAtParentLocalization' => $dceConfiguration['localizeReferencesAtParentLocalization'] ?? null ?: null,
            'maxitems' => $dceConfiguration['maxitems'] ?? null ?: null,
            'minitems' => $dceConfiguration['minitems'] ?? null ?: null,
            'MM' => $dceConfiguration['MM'] ?? null ?: null,
            'MM_match_fields' => $dceConfiguration['MM_match_fields'] ?? null ?: null,
            'MM_opposite_field' => $dceConfiguration['MM_opposite_field'] ?? null ?: null,
            'MM_oppositeUsage' => $dceConfiguration['MM_oppositeUsage'] ?? null ?: null,
            'MM_table_where' => $dceConfiguration['MM_table_where'] ?? null ?: null,
            'MM_hasUidField' => $dceConfiguration['MM_hasUidField'] ?? null ?: null,
            'multiple' => $dceConfiguration['multiple'] ?? null ?: null,
            'multiSelectFilterItems' => $dceConfiguration['multiSelectFilterItems'] ?? null ?: null,
            'readOnly' => $dceConfiguration['readOnly'] ?? null ?: null,
            'size' => $dceConfiguration['size'] ?? null ?: null,
            'sortItems' => $dceConfiguration['sortItems'] ?? null ?: null,
        ];

        return $fieldConfiguration;
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'select' && ($configuration['renderType'] ?? '') !== 'selectTree';
    }
}
