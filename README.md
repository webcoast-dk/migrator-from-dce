# Migrator: DCE content type provider

This TYPO3 extension extends the `migrator` extension by providing a content type provider for DCE content elements, helping you to migrate your existing DCE content elements
to other content types, e.g. Content Blocks and/or Container content elements.

## Installation

```bash
composer require webcoast/migrator-from-dce
```

The extension has a dependency to the `migrator` extension, which will be installed automatically through composer. The DCE is not required, as this extension directly fetches
the DCE content type and field configuration from the database.

If you want to migrate DCE elements to content blocks you need the following packages:
* `webcoast/migrator-from-dce` (this extension)
* `webcoast/migrator-to-content-blocks` (content type builder for content blocks)

If you want to migrate to container elements, because you have some DCE elements with enabled grid, you need the following packages:
* `webcoast/migrator-from-dce` (this extension)
* `webcoast/migrator-to-container` (content type builder for container elements)

## Compatibility

| Extension ↓ / TYPO3 → | 13.4 |
|-----------------------|:----:|
| 1.0.0                 |  ✅   |

## Content Type Providers

This extension provides a content type provider for DCE content elements, which fetches the content type configuration from the DCE database tables and provides the record data of
the content elements for data migration.

This extension supports most of the standard TYPO3 CMS field types. Explicitly not supported are `password`, `none`, `passthrough` and `flex`. Section fields (anonymous inline records
without a database table) are supported.

## Upgrade Wizard (Record data migration)

This extension provides data as described in the Migrator core documentation. Files (from legacy file fields) and file references (from modern file fields) are provided as objects of
type `TYPO3\CMS\Core\Resource\File` and `TYPO3\CMS\Core\Resource\FileReference`, respectively, which can be used in the record data migrator according to the migrator core documentation.

### Migrating container elements
If you have DCE elements with enabled grid configuration, you might want to migrate them to container elements and content blocks at the same time. For the first DCE content element in
a grid configuration (e.g. first of 3 consecutive DCE content elements with enabled grid configuration) you want to create a container element and move the current and the next 2 consecutive
content elements into the container element.

To help with that, this extension provides the `ContainerEnabledTrait`, which you can use in your record data migrator to check, when to create a container element and when to migrate to
a content block. The trait provides the methods `determinePreviousContentElementId()` and `isFirstOfConsecutiveRecords()`.

Use `determinePreviousContentElementId()` to determine the UID of the content before you current content element. This might return false, if your element is the first element on the page.
The UID is important as the target PID for the container element. Providing a negative PID for the new container element will put it after the content element with the provided UID (see
example below).

Use `isFirstOfConsecutiveRecords()` to check if the current content element is the first of a consecutive set of content elements with enabled grid configuration. This will help you to
determine, when to create a container element and when just to migrate the record and move it into the container element.

The `ContainerEnabledTrait` also implements `NewMappingAwareInterface::setNewIdMappings`, which is necessary to replace the `NEW...` IDs for the newly created container element with the
actual UID after the container element has been created.

### Example
The following example migrates DCE content elements with enabled container function to a container element for each set of consecutive DCE content elements and migrates and moves the actual
content elements into the container element. In this example the container limit was 3, so for each 3 consecutive content elements a new container element is created and the content elements
are moved into the container element.

The record migrator class extends the `ContainerAwareRecordMigrator` from the `migrator-to-container` extension, which provides a convenience method `moveIntoContainer()` to move the content
elements into the container element. It also uses the `determinePreviousContentElementId()` and `isFirstOfConsecutiveRecords()` methods from the `ContainerEnabledTrait`.
```php
<?php

declare(strict_types=1);

namespace MyVendor\Sitepackage\Upgrade\RecordDataMigrator;

use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\StringUtility;
use WEBcoast\Migrator\Attribute\SourceContentType;
use WEBcoast\Migrator\Update\NewIdMappingAwareInterface;
use WEBcoast\MigratorFromDce\Repository\DceRepository;
use WEBcoast\MigratorFromDce\Update\ContainerEnabledTrait;
use WEBcoast\MigratorToContainer\Update\ContainerAwareRecordMigrator;

#[SourceContentType('dce', 'dce_dceuid122')]
class ThreeImagesWithTextMigrator extends ContainerAwareRecordMigrator implements NewIdMappingAwareInterface
{
    use ContainerEnabledTrait;

    public function __construct(protected readonly DceRepository $dceRepository)
    {
    }

    public function migrate(array $incomingData, array $record): array
    {
        $dce = $this->dceRepository->getConfiguration(122);
        $previousContentElementId = $this->determinePreviousContentElementId($record);
        // Execute the creation of the container element only, if the current content element is the first of a consecutive set of content elements
        if ($this->isFirstOfConsecutiveRecords($record, $dce['container_item_limit'] ?? PHP_INT_MAX) || ($record['tx_dce_new_container'] ?? false)) {
            // Create the ID for the new container content element, because we need it to create the file reference for the background image and to move the content element into the container
            $newContainerId = StringUtility::getUniqueId('NEW');
            $backgroundImages = [];

            /** @var FileReference $fileReference */
            foreach ($incomingData['backgroundimage'] as $fileReference) {
                // The first content element of each consecutive set of content elements contains a background image, which needs to be added to the container element
                $backgroundImages[] = $this->addFileReference($fileReference->getOriginalFile(), 'tt_content', 'image', $newContainerId, $record['pid'], $record['sys_language_uid']);
            }

            // Add the new container element to the datamap to be created
            $this->addReference(
                'tt_content',
                [
                    'pid' => $previousContentElementId ? ('-' . $previousContentElementId) : $record['pid'], // A negative PID will put the new container element after the content element with the provided UID. The PID of the record, would place it as the first element on that page.
                    'colPos' => $record['colPos'],
                    'sys_language_uid' => $record['sys_language_uid'],
                    'l18n_parent' => self::$containerParents[$record['l18n_parent']] ?? 0, // Important when migrating localized content elements, to place the localized container element as a child of the original container element
                    'CType' => '3_images_with_text_container',
                    'bodytext' => $incomingData['teaser'],
                    'image' => implode(',', $backgroundImages), // Implode the file references to a comma-separated list, because that is the format the TYPO3 data handler expects
                    'space_before_class' => $record['space_before_class'] ?? '', // Copy the spacing classes from the original content element, to keep the spacing after migration.
                    'space_after_class' => $record['space_after_class'] ?? '', // Copy the spacing classes from the original content element, to keep the spacing after migration.
                    'backgroundx' => $incomingData['backgroundx'],
                    'backgroundy' => $incomingData['backgroundy'],
                    'backgroundsize' => $incomingData['backgroundsize'],
                    'backgroundwhitespacetop' => $incomingData['backgroundwhitespacetop'],
                ],
                $newContainerId // Use the generated ID as third parameter to addReference. If not provided, `addReference()` would return a new ID, which would mean, the references to the previously generated ID (e.g. file reference) would be lost
            );
            self::$lastContainerId = $newContainerId; // Store the new container ID in a static property, to be used for the next content element in the set of consecutive content elements
            self::$containerParents[$record['uid']] = $newContainerId; // Store the new container ID as the parent for the current content element, to be used for the localized content elements
            // Reset last record id, when starting a new container
            self::$lastRecordId = 0;
        }

        $this->moveIntoContainer(
            $record['uid'], // The UID of the current content element, which should be moved into the container element
            self::$lastContainerId, // The ID of the container element, which is stored in the static property and set when the first content element of a consecutive set of content elements is processed
            100, // The colPos value for the content element inside the container element, which is 100 for the given container element (depends on the configuration of your container element)
            self::$lastRecordId ?: null // Move the content element after the element with his UID. If `null`, the element will be moved as the first element inside the container element.
        );

        // Process the migration of the actual content element
        $images = [];
        foreach ($incomingData['image'] as $fileReference) {
            $images[] = $this->updateFileReference($fileReference, 'image');
        }

        // Set the last record ID to move the next content element in this set of consecutive content elements after the current content element
        self::$lastRecordId = $record['uid'];

        return [
            'CType' => '3_images_with_text', // Setting the CType is important. Without it, the migration will not be executed.
            'bodytext' => $incomingData['text'],
            'lineheight' => $incomingData['lineheight'],
            'image' => implode(',', $images), // Implode the file references to a comma-separated list, because that is the format the TYPO3 data handler expects
            'space_before_class' => '',
            'space_after_class' => '',
        ];
    }
}
```

## Sponsors

The development of this extension has been sponsored by
* [Aemka](https://aemka.de/)
* [apart](https://apart.lu/)
* [Homepage Helden](https://www.homepage-helden.de/)
* [HZ Internet Services](https://www.hziegenhain.de/)

Thanks to all sponsors for their support and contributions to the development of this extension!

If you are interested in sponsoring the development of this extension, please contact me via email to [thorben@webcoast.dk](mailto:thorben@webcoast.dk) or in the TYPO3 Slack channel
(#ext-migrator).

## Contributing
Contributions to this extension are always welcome, both in form of pull requests, bug reports and feature requests and ideas.

If you have questions, reach out to me via email to [thorben@webcoast.dk](mailto:thorben@webcoast.dk), the discussion section of this repository or the TYPO3 Slack channel (#ext-migrator).

## License
This extension is licensed under the GPL-3.0 License. See the [LICENSE](LICENSE) file for more details.
