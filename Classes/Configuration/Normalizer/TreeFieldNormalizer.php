<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use WEBcoast\Migrator\Migration\Field;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\Migrator\Utility\ArrayUtility;
use WEBcoast\Migrator\Utility\TcaUtility;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class TreeFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(Field $normalizedField, array $dceConfiguration): void
    {
        $normalizedField->setType(FieldType::TREE);
        $normalizedField->setConfiguration(
            ArrayUtility::removeEmptyValuesFromArray([
                'allowNonIdValues' => $dceConfiguration['allowNonIdValues'] ?? null ?: null,
                'authMode' => $dceConfiguration['authMode'] ?? null ?: null,
                'behaviour' => [
                    'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
                ],
                'dbFieldLength' => $dceConfiguration['dbFieldLength'] ?? null ?: null,
                'default' => $dceConfiguration['default'] ?? null,
                'disableNoMatchingValueElement' => $dceConfiguration['disableNoMatchingValueElement'] ?? null ?: null,
                'exclusiveKeys' => $dceConfiguration['exclusiveKeys'] ?? null ?: null,
                'fieldInformation' => $dceConfiguration['fieldInformation'] ?? null ?: null,
                'fieldWizard' => $dceConfiguration['fieldWizard'] ?? null ?: null,
                'fileFolderConfig' => [
                    'allowedExtensions' => $dceConfiguration['fileFolderConfig']['allowedExtensions'] ?? null ?: null,
                    'depth' => $dceConfiguration['fileFolderConfig']['depth'] ?? null ?: null,
                    'folder' => $dceConfiguration['fileFolderConfig']['folder'] ?? null ?: null,
                ],
                'foreign_table' => $dceConfiguration['foreign_table'] ?? null,
                'foreign_table_item_group' => $dceConfiguration['foreign_table_item_group'] ?? null ?: null,
                'foreign_table_prefix' => $dceConfiguration['foreign_table_prefix'] ?? null ?: null,
                'foreign_table_where' => $dceConfiguration['foreign_table_where'] ?? null ?: null,
                'items' => TcaUtility::migrateItemsFormat($dceConfiguration['items'] ?? null ?: []),
                'itemsProcFunc' => $dceConfiguration['itemsProcFunc'] ?? null,
                'maxitems' => $dceConfiguration['maxitems'] ?? null,
                'minitems' => $dceConfiguration['minitems'] ?? null,
                'MM' => $dceConfiguration['MM'] ?? null,
                'MM_match_fields' => $dceConfiguration['MM_match_fields'] ?? null,
                'MM_opposite_field' => $dceConfiguration['MM_opposite_field'] ?? null,
                'MM_oppositeUsage' => $dceConfiguration['MM_oppositeUsage'] ?? null ?: null,
                'MM_table_where' => $dceConfiguration['MM_table_where'] ?? null ?: null,
                'MM_hasUidField' => $dceConfiguration['MM_hasUidField'] ?? null ?: null,
                'multiple' => $dceConfiguration['multiple'] ?? null ?: null,
                'readOnly' => $dceConfiguration['readOnly'] ?? null ?: null,
                'size' => $dceConfiguration['size'] ?? null,
                'treeConfig' => [
                    'dataProvider' => $dceConfiguration['treeConfig']['dataProvider'] ?? null ?: null,
                    'childrenField' => $dceConfiguration['treeConfig']['childrenField'] ?? null ?: null,
                    'parentField' => $dceConfiguration['treeConfig']['parentField'] ?? null ?: null,
                    'startingPoints' => $dceConfiguration['treeConfig']['startingPoints'] ?? null ?: null,
                    'appearance' => [
                        'showHeader' => $dceConfiguration['treeConfig']['appearance']['showHeader'] ?? null,
                        'expandAll' => $dceConfiguration['treeConfig']['appearance']['expandAll'] ?? null,
                        'maxLevels' => $dceConfiguration['treeConfig']['appearance']['maxLevels'] ?? null,
                        'nonSelectableLevels' => $dceConfiguration['treeConfig']['appearance']['nonSelectableLevels'] ?? null,
                    ]
                ]
            ])
        );
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'select' && ($configuration['renderType'] ?? '') === 'selectTree';
    }
}
