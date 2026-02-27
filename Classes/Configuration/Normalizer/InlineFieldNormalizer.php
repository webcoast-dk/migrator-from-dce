<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class InlineFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array
    {
        $fieldConfiguration['type'] = FieldType::INLINE;
        $fieldConfiguration['config'] = [
            'appearance' => [
                'collapseAll' => $dceConfiguration['collapseAll'],
                'expandSingle' => $dceConfiguration['expandSingle'],
                'showNewRecordLink' => $dceConfiguration['appearance']['showNewRecordLink'] ?? null ?: null,
                'newRecordLinkAddTitle' => $dceConfiguration['appearance']['newRecordLinkAddTitle'] ?? null,
                'newRecordLinkTitle' => $dceConfiguration['appearance']['newRecordLinkTitle'] ?? null ?: null,
                'levelLinksPosition' => $dceConfiguration['appearance']['levelLinksPosition'] ?? null,
                'useCombination' => $dceConfiguration['appearance']['useCombination'] ?? null,
                'suppressCombinationWarning' => $dceConfiguration['appearance']['suppressCombinationWarning'] ?? null ?: null,
                'useSortable' => $dceConfiguration['appearance']['useSortable'] ?? null,
                'showPossibleLocalizationRecords' => $dceConfiguration['appearance']['showPossibleLocalizationRecords'] ?? null,
                'showAllLocalizationLink' => $dceConfiguration['appearance']['showAllLocalizationLink'] ?? null,
                'showSynchronizationLink' => $dceConfiguration['appearance']['showSynchronizationLink'] ?? null,
                'enabledControls' => $dceConfiguration['appearance']['enabledControls'] ?? null,
                'showPossibleRecordSelector' => $dceConfiguration['appearance']['showPossibleRecordSelector'] ?? null ?: null,
                'elementBrowserEnabled' => $dceConfiguration['appearance']['elementBrowserEnabled'] ?? null ?: null,
            ],
            'behaviour' => [
                'allowLanguageSynchronization' => $dceConfiguration['behaviour']['allowLanguageSynchronization'] ?? null ?: null,
                'disableMovingChildrenWithParent' => $dceConfiguration['behaviour']['disableMovingChildrenWithParent'] ?? null,
                'enableCascadingDelete' => $dceConfiguration['behaviour']['enableCascadingDelete'] ?? null ?: null,
            ],
            'customControls' => $dceConfiguration['customControls'] ?? null ?: null,
            'filter' => $dceConfiguration['filter'] ?? null ?: null,
            'foreign_default_sortby' => $dceConfiguration['foreign_default_sortby'] ?? null,
            'foreign_field' => $dceConfiguration['foreign_field'] ?? null,
            'foreign_label' => $dceConfiguration['foreign_label'] ?? null,
            'foreign_match_fields' => $dceConfiguration['foreign_match_fields'] ?? null,
            'foreign_selector' => $dceConfiguration['foreign_selector'] ?? null,
            'foreign_sortby' => $dceConfiguration['foreign_sortby'] ?? null,
            'foreign_table' => $dceConfiguration['foreign_table'] ?? null,
            'foreign_table_where' => $dceConfiguration['foreign_table_where'] ?? null,
            'foreign_table_field' => $dceConfiguration['foreign_table_field'] ?? null,
            'foreign_unique' => $dceConfiguration['foreign_unique'] ?? null,
            'maxitems' => $dceConfiguration['maxitems'] ?? null,
            'minitems' => $dceConfiguration['minitems'] ?? null,
            'MM' => $dceConfiguration['MM'] ?? null,
            'MM_opposite_field' => $dceConfiguration['MM_opposite_field'] ?? null,
            'MM_hasUidField' => $dceConfiguration['MM_hasUidField'] ?? null ?: null,
            'overrideChildTca' => $dceConfiguration['overrideChildTca'] ?? null,
            'size' => $dceConfiguration['size'] ?? null,
            'symmetric_field' => $dceConfiguration['symmetric_field'] ?? null,
            'symmetric_label' => $dceConfiguration['symmetric_label'] ?? null,
            'symmetric_sortby' => $dceConfiguration['symmetric_sortby'] ?? null,
        ];

        return $fieldConfiguration;
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'inline' && $configuration['foreign_table'] !== 'sys_file_reference';
    }
}
