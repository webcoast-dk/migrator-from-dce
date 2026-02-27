<?php

declare(strict_types=1);


namespace WEBcoast\MigratorFromDce\Configuration;


use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('webcoast.migrator_from_dce.field_configuration_normalizer')]
interface FieldConfigurationNormalizerInterface
{
    public function supports(array $configuration): bool;
    public function normalize(array $fieldConfiguration, array $dceConfiguration): array;
}
