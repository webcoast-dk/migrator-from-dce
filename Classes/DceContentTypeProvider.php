<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\Migrator\Configuration\ContentTypeProviderInterface;
use WEBcoast\Migrator\Migration\FieldType;
use WEBcoast\Migrator\Utility\ArrayUtility;
use WEBcoast\MigratorFromDce\Repository\DceRepository;

readonly class DceContentTypeProvider implements ContentTypeProviderInterface
{
    public function __construct(protected DceRepository $dceRepository, #[Autowire('webcoast.migrator_from_dce.field_configuration_migrators')] protected array $fieldConfigurationMigrators)
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
                'identifier' => $dce['identifier'] ?? null ?: 'dceuid' . $dce['uid'],
                'title' => $dce['title'],
                'description' => $dce['wizard_description'] ?? '',
            ];
        }

        usort($contentTypes, function ($a, $b) {
            return strcmp($a['title'], $b['title']);
        });

        return $contentTypes;
    }

    public function getConfiguration(string $contentType): array
    {
        $dceConfiguration = $this->dceRepository->getConfiguration($contentType);

        return [
            'title' => $dceConfiguration['title'],
            'description' => $dceConfiguration['wizard_description'] ?? '',
            'iconIdentifier' => $dceConfiguration['wizard_icon'] ?? '',
            'group' => $dceConfiguration['wizard_group'] ?? 'dce',
            'fields' => $this->getNormalizedFieldsConfiguration($this->dceRepository->fetchFieldsByParentDce((int) $dceConfiguration['uid'])),
            'grid' => $this->getNormalizedGridConfiguration($dceConfiguration),
        ];
    }

    public function getTemplate(string $contentType): ?string
    {
        $templateContent = null;
        $dceConfiguration = $this->dceRepository->getConfiguration($contentType);
        if ($dceConfiguration['template_type'] === 'inline') {
            $templateContent = $dceConfiguration['template_content'] ?? '';
        } elseif ($dceConfiguration['template_type'] === 'file') {
            $templatePath = $dceConfiguration['template_file'] ?? '';
            if (str_starts_with($templatePath, 'EXT:')) {
                $templateContent = file_get_contents(GeneralUtility::getFileAbsFileName($templatePath));
            } elseif (str_starts_with($templatePath, 't3://file')) {
                $fileUid = (int) substr($templatePath, 14);

                /** @var FileRepository $fileRepository */
                $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
                $file = $fileRepository->findByUid($fileUid);
                $templateContent = $file->getContents();
            } elseif (file_exists(Environment::getPublicPath() . '/' . $templatePath)) {
                $templateContent = file_get_contents(Environment::getPublicPath() . '/' . $templatePath);
            }
        }

        return $templateContent;
    }

    public function getIcon(string $contentType): ?string
    {
        $dceConfiguration = $this->dceRepository->getConfiguration($contentType);
        if ($dceConfiguration['wizard_icon']) {
            /** @var IconRegistry $iconRegistry */
            $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
            $iconConfiguration = $iconRegistry->getIconConfigurationByIdentifier($dceConfiguration['wizard_icon']);
            return GeneralUtility::getFileAbsFileName($iconConfiguration['options']['source']);
        }

        return null;
    }

    protected function getNormalizedFieldsConfiguration(array $dceFields): array
    {
        $fields = [];

        foreach ($dceFields as $dceField) {
            if ((int) $dceField['type'] === 1) {
                $fields[] = [
                    'identifier' => $dceField['variable'],
                    'type' => FieldType::TAB,
                    'title' => $dceField['title'],
                ];
            } else {
                $fieldConfiguration = [
                    'identifier' => $dceField['variable'],
                    'label' => $dceField['title'],
                ];


                if ((int) $dceField['type'] === 0) {
                    if ($dceField['map_to']) {
                        $fieldConfiguration['db_field'] = $dceField['map_to'];
                    }

                    $dceConfiguration = GeneralUtility::xml2array($dceField['configuration']);

                    foreach ($this->fieldConfigurationMigrators as $fieldConfigurationMigrator) {
                        $dceConfiguration = $fieldConfigurationMigrator->process($dceConfiguration);
                    }

                    $fieldConfiguration['type'] = match ($dceConfiguration['type']) {
                        'category' => FieldType::CATEGORY,
                        'check' => FieldType::CHECKBOX,
                        'color' => FieldType::COLOR,
                        'datetime' => FieldType::DATETIME,
                        'email' => FieldType::EMAIL,
                        'file' => FieldType::FILE,
                        'flex' => FieldType::FLEXFORM,
                        'folder' => FieldType::FOLDER,
                        'group' => FieldType::GROUP,
                        'imageManipulation' => FieldType::IMAGE_MANIPULATION,
                        'inline' => FieldType::INLINE,
                        'input' => FieldType::TEXT,
                        'json' => FieldType::JSON,
                        'language' => FieldType::LANGUAGE,
                        'link' => FieldType::LINK,
                        'number' => FieldType::NUMBER,
                        'password' => FieldType::PASSWORD,
                        'radio' => FieldType::RADIO,
                        'select' => FieldType::SELECT,
                        'slug' => FieldType::SLUG,
                        'text' => FieldType::TEXTAREA,
                        'uuid' => FieldType::UUID,
                        default => $dceConfiguration['type'],
                    };

                    $fieldConfiguration = array_replace_recursive($fieldConfiguration, $dceConfiguration);

                    $fields[] = $fieldConfiguration;
                } elseif ((int) $dceField['type'] === 2) {
                    $fields[] = [
                        'identifier' => $dceField['variable'],
                        'type' => FieldType::SECTION,
                        'title' => $dceField['title'],
                        'fields' => $this->getNormalizedFieldsConfiguration($this->dceRepository->fetchFieldsByParentField((int) $dceField['uid'])),
                    ];
                }
            }
        }

        return $fields;
    }

    protected function getNormalizedGridConfiguration(array $dceConfiguration): array
    {
        if (!$dceConfiguration['enable_container']) {
            return [];
        }

        return ArrayUtility::removeEmptyValuesFromArray([
            [
                [
                    'name' => 'Content',
                    'colPos' => 100,
                    'allowed' => [
                        'CType' => 'dce_' . ($dceConfiguration['identifier'] ?? 'dceuid' . $dceConfiguration['uid'])
                    ],
                    'maxitems' => $dceConfiguration['container_item_limit'] ?? null ?: null,
                ]
            ]
        ]);
    }
}
