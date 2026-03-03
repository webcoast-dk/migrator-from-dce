<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use WEBcoast\Migrator\Migration\Column;
use WEBcoast\Migrator\Migration\ContentType;
use WEBcoast\Migrator\Migration\Field;
use WEBcoast\Migrator\Migration\FieldCollection;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\Migrator\Migration\Grid;
use WEBcoast\Migrator\Migration\Row;
use WEBcoast\Migrator\Migration\Section;
use WEBcoast\Migrator\Migration\Tab;
use WEBcoast\Migrator\Provider\ContainerTemplateProviderInterface;
use WEBcoast\Migrator\Provider\ContentTypeProviderInterface;
use WEBcoast\MigratorFromDce\Configuration\FieldConfigurationNormalizerInterface;
use WEBcoast\MigratorFromDce\Repository\DceRepository;

class DceContentTypeProvider implements ContentTypeProviderInterface, ContainerTemplateProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param DceRepository $dceRepository
     * @param iterable|FieldConfigurationNormalizerInterface[] $fieldConfigurationNormalizers
     * @param FlexFormService $flexFormService
     */
    public function __construct(readonly protected DceRepository $dceRepository, #[AutowireIterator('webcoast.migrator_from_dce.field_configuration_normalizer')] readonly protected iterable $fieldConfigurationNormalizers, readonly protected FlexFormService $flexFormService)
    {
    }

    public function getIdentifier(): string
    {
        return 'dce';
    }

    public function getDescription(): string
    {
        return 'Provides content types from the DCE extension';
    }

    public function getAvailableContentTypes(): iterable
    {
        $contentTypes = [];

        $allContentTypes = $this->dceRepository->fetchAll();
        while ($dce = $allContentTypes->fetchAssociative()) {
            $contentTypes[] = [
                'identifier' => $dce['identifier'] ?? null ?: 'dce_dceuid' . $dce['uid'],
                'title' => $dce['title'],
                'description' => $dce['wizard_description'] ?? '',
            ];
        }

        usort($contentTypes, function ($a, $b) {
            return strcmp($a['title'], $b['title']);
        });

        return $contentTypes;
    }

    public function getConfiguration(string $contentType): ContentType
    {
        $dceConfiguration = $this->dceRepository->getConfiguration($contentType);
        if (!$dceConfiguration) {
            throw new \InvalidArgumentException(sprintf('No DCE configuration found for content type "%s"', $contentType));
        }

        return new ContentType(
            $dceConfiguration['identifier'] ?: 'dceuid' . $dceConfiguration['uid'],
            $dceConfiguration['title'],
            $dceConfiguration['wizard_description'] ?? '',
            $this->getNormalizedFieldsConfiguration($this->dceRepository->fetchFieldsByParentDce((int) $dceConfiguration['uid'])),
            $this->getNormalizedGridConfiguration($dceConfiguration),
            $dceConfiguration['wizard_icon'] ?? '',
            $dceConfiguration['wizard_group'] ?? 'dce',
        );
    }

    public function getFrontendTemplate(string $contentType): ?string
    {
        $dceConfiguration = $this->dceRepository->getConfiguration($contentType);
        if ($dceConfiguration['template_type'] === 'inline') {
            return $dceConfiguration['template_content'] ?? '';
        } elseif ($dceConfiguration['template_type'] === 'file') {
            $templatePath = $dceConfiguration['template_file'] ?? '';

            return $this->getTemplateContentFromFile($templatePath);
        }

        return null;
    }

    public function getContainerTemplate(string $contentType): ?string
    {
        $dceConfiguration = $this->dceRepository->getConfiguration($contentType);
        if ($dceConfiguration['container_template_type'] === 'inline') {
            return $dceConfiguration['container_template'] ?? '';
        } elseif ($dceConfiguration['container_template_type'] === 'file') {
            $templatePath = $dceConfiguration['container_template_file'] ?? '';

            return $this->getTemplateContentFromFile($templatePath);
        }

        return null;
    }

    protected function getTemplateContentFromFile(string $templatePath): ?string
    {
        if (str_starts_with($templatePath, 'EXT:')) {
            return file_get_contents(GeneralUtility::getFileAbsFileName($templatePath));
        } elseif (str_starts_with($templatePath, 't3://file')) {
            $fileUid = (int) substr($templatePath, 14);

            /** @var FileRepository $fileRepository */
            $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
            $file = $fileRepository->findByUid($fileUid);

            return $file->getContents();
        } elseif (file_exists(Environment::getPublicPath() . '/' . $templatePath)) {
            return file_get_contents(Environment::getPublicPath() . '/' . $templatePath);
        }

        return null;
    }

    public function getBackendPreviewTemplate(string $contentType): ?string
    {
        return null;
    }

    public function getIcon(string $contentType): ?string
    {
        $dceConfiguration = $this->dceRepository->getConfiguration($contentType);
        if ($dceConfiguration['wizard_icon']) {
            /** @var IconRegistry $iconRegistry */
            $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
            $iconConfiguration = $iconRegistry->getIconConfigurationByIdentifier($dceConfiguration['wizard_icon']);

            if (!$iconConfiguration['options']['source'] ?? null) {
                return null;
            }

            return GeneralUtility::getFileAbsFileName($iconConfiguration['options']['source']);
        }

        return null;
    }

    public function getRecordData(array $rawRecord): array
    {
        $rawFlexFormData = $this->flexFormService->convertFlexFormContentToArray($rawRecord['pi_flexform'] ?? '')['settings'] ?? [];
        $data = [];
        $fields = $this->getConfiguration($rawRecord['CType'])->getFields() ?? [];

        foreach ($fields as $field) {
            if ($field->getType() === FieldType::TAB) {
                // Skip tab fields, as they hold no data
                continue;
            }

            $this->addData($data, $rawFlexFormData, $rawRecord, $field);
        }

        return $data;
    }

    public function addData(array &$data, array $rawFlexFormData, array $record, Field $field): void
    {

        if ($field instanceof Section) {
            $this->addDataForSection($data, $rawFlexFormData, $record, $field);
        } else {
            $this->addDataForField($data, $rawFlexFormData, $record, $field);
        }
    }

    protected function addDataForField(array &$data, array $rawFlexFormData, array $record, Field $field): void
    {
        if ($field->getDbField()) {
            return;
        }

        $dceFieldConfiguration = $field->getConfiguration() ?? [];
        if ($field->getType() === FieldType::LEGACY_FILE) {
            $fileNames = GeneralUtility::trimExplode(',', $rawFlexFormData[$field->getIdentifier()] ?? '', true);
            if (empty($fileNames)) {
                $data[$field->getIdentifier()] = [];
                return;
            }

            $data[$field->getIdentifier()] = [];
            // Check if filenames are actually integer file ids, if so, fetch the file objects via the file repository
            if (MathUtility::canBeInterpretedAsInteger($fileNames[0])) {
                foreach ($fileNames as $fileId) {
                    $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
                    $file = $fileRepository->findByUid((int) $fileId);

                    $data[$field->getIdentifier()][] = $file;
                }
            } else {
                // Otherwise, assume they are file names and try to fetch the file objects via the storage repository
                /** @var StorageRepository $storageRepository */
                $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
                foreach ($fileNames as $fileName) {
                    $fileIdentifier = ltrim(($dceFieldConfiguration['uploadfolder'] ?? '') . '/' . $fileName, '/');
                    $storage = $storageRepository->getStorageObject(0, [], $fileIdentifier);

                    try {
                        $file = $storage->getFile($fileIdentifier);
                        if ($storage->getUid() === 0) {
                            $defaultStorage = $storageRepository->getDefaultStorage();
                            if (!$defaultStorage->hasFolder(dirname($fileIdentifier))) {
                                $defaultStorage->createFolder(dirname($fileIdentifier));
                            }
                            $targetFolder = $defaultStorage->getFolder(dirname($fileIdentifier));
                            if (!$targetFolder->hasFile($fileName)) {
                                $newFile = $file->copyTo($targetFolder);
                            } elseif ($targetFolder->getFile($fileName)->getSha1() === $file->getSha1()) {
                                $newFile = $targetFolder->getFile($fileName);
                            } else {
                                $newFile = $file->copyTo($targetFolder);
                            }
                        }
                        $data[$field->getIdentifier()][] = $newFile ?? $file;
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                }
            }
        } elseif ($field->getType() === FieldType::FILE) {
            $data[$field->getIdentifier()] = [];

            /** @var RelationHandler $relationHandler */
            $relationHandler = GeneralUtility::makeInstance(RelationHandler::class);
            $possibleFieldNames = [
                'settings.' . $field->getIdentifier(),
                $field->getIdentifier(),
            ];
            foreach ($possibleFieldNames as $possibleFieldName) {
                $fieldConfigurationForRelationHandler = $dceFieldConfiguration;
                if (empty($fieldConfigurationForRelationHandler['type'])) {
                    // Fallback to "file" type. The FieldType::FILE would either be "file" or "inline". "inline" would be set as type in the field configuration
                    $fieldConfigurationForRelationHandler['type'] = 'file';
                    $fieldConfigurationForRelationHandler['foreign_table'] = 'sys_file_reference';
                    $fieldConfigurationForRelationHandler['foreign_field'] = 'uid_foreign';
                    $fieldConfigurationForRelationHandler['foreign_sortby'] = 'sorting_foreign';
                    $fieldConfigurationForRelationHandler['foreign_table_field'] = 'tablenames';
                    $fieldConfigurationForRelationHandler['foreign_match_fields'] = [
                        'tablenames' => 'tt_content',
                        'fieldname' => $possibleFieldName,
                    ];
                } elseif (!empty($fieldConfigurationForRelationHandler['foreign_match_fields']['fieldname'] ?? null)) {
                    $fieldConfigurationForRelationHandler['foreign_match_fields']['fieldname'] = str_replace('{$variable}', $possibleFieldName, $dceFieldConfiguration['foreign_match_fields']['fieldname']);
                }

                $relationHandler->initializeForField('tt_content', $fieldConfigurationForRelationHandler, $record['uid']);
                if (!empty($relationHandler->tableArray['sys_file_reference'])) {
                    $relationHandler->processDeletePlaceholder();
                    $referenceUids = $relationHandler->tableArray['sys_file_reference'];

                    /** @var ResourceFactory $resourceFactory */
                    $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
                    foreach ($referenceUids as $referenceUid) {
                        $data[$field->getIdentifier()][] = $resourceFactory->getFileReferenceObject($referenceUid);
                    }
                }
            }
        } else {
            $data[$field->getIdentifier()] = $rawFlexFormData[$field->getIdentifier()] ?? '';
        }
    }

    protected function addDataForSection(array &$data, array $rawFlexFormData, array $record, Section $field): void
    {
        $sections = $rawFlexFormData[$field->getIdentifier()] ?? [] ?: [];
        $data[$field->getIdentifier()] = [];

        foreach ($sections as $section) {
            $childData = [];
            foreach ($field as $childField) {
                if ($childField->getType() === FieldType::TAB) {
                    // Skip tab fields, as they hold no data
                    continue;
                }
                $childFlexFormData = $section[$field->getObjectIdentifier()] ?? [];
                $this->addData($childData, $childFlexFormData, $record, $childField);
            }

            $data[$field->getIdentifier()][] = $childData;
        }
    }

    protected function getNormalizedFieldsConfiguration(array $dceFields): FieldCollection
    {
        $fields = new FieldCollection();

        foreach ($dceFields as $dceField) {
            if ((int) $dceField['type'] === 1) {
                $fields->addField(
                    new Tab(
                        $dceField['variable'],
                        $dceField['title']
                    )
                );
            } elseif ((int) $dceField['type'] === 2) {
                $fields->addField(
                    new Section(
                        $dceField['variable'],
                        'container_' . $dceField['variable'],
                        $dceField['title'],
                        fields: $this->getNormalizedFieldsConfiguration($this->dceRepository->fetchFieldsByParentField((int) $dceField['uid']))
                    )
                );
            } else {
                $normalizedField = new Field(
                    $dceField['variable'],
                    null,
                    $dceField['title']
                );

                if ($dceField['map_to']) {
                    $normalizedField->setDbField($dceField['map_to']);
                }

                $dceConfiguration = GeneralUtility::xml2array($dceField['configuration']);

                foreach ($this->fieldConfigurationNormalizers as $fieldConfigurationNormalizer) {
                    if ($fieldConfigurationNormalizer->supports($dceConfiguration)) {
                        $fieldConfigurationNormalizer->normalize($normalizedField, $dceConfiguration);
                    }
                }

                if (!($normalizedField->getType() ?? null)) {
                    // Do not include fields that do not have a type after normalization
                    continue;
                }

                $fields->addField($normalizedField);
            }
        }

        return $fields;
    }

    protected function getNormalizedGridConfiguration(array $dceConfiguration): ?Grid
    {
        if (!$dceConfiguration['enable_container']) {
            return null;
        }

        $grid = new Grid();
        $row = new Row();
        $row->attach(new Column(
            'Content',
            100,
            allowed: [
                'CType' => 'dce_' . ($dceConfiguration['identifier'] ?: 'dceuid' . $dceConfiguration['uid'])
            ],
            maxitems: $dceConfiguration['container_item_limit'] ?? null ?: null
        ));

        $grid->attach($row);

        return $grid;
    }
}
