<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Configuration\Normalizer;

use WEBcoast\Migrator\Migration\Field;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\Migrator\Utility\ArrayUtility;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;

class GroupFieldNormalizer implements FieldConfigurationNormalizerInterface
{
    public function normalize(Field $normalizedField, array $dceConfiguration): void
    {
        $normalizedField->setType(FieldType::GROUP);
        $normalizedField->setConfiguration(
            ArrayUtility::removeEmptyValuesFromArray([
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
            ])
        );
    }

    public function supports(array $configuration): bool
    {
        return $configuration['type'] === 'group' && $configuration['internal_type'] === 'db' && ($dceConfiguration['appearance']['elementBrowserType'] ?? '') !== 'file';
    }
}
