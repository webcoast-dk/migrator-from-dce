<?php

declare(strict_types=1);


namespace WEBcoast\MigratorFromDce\Configuration;


use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use WEBcoast\Migrator\Migration\Field;

#[AutoconfigureTag('webcoast.migrator_from_dce.field_configuration_normalizer')]
interface FieldConfigurationNormalizerInterface
{
    public function supports(array $configuration): bool;
    public function normalize(Field $normalizedField, array $dceConfiguration): void;
}
