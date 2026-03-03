<?php

declare(strict_types=1);

namespace WEBcoast\MigratorFromDce\Repository;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class DceRepository
{
    public function fetchAll(): Result
    {
        $queryBuilder = $this->createQueryBuilder('tx_dce_domain_model_dce');
        $queryBuilder
            ->select('uid', 'title', 'identifier', 'wizard_description')
            ->from('tx_dce_domain_model_dce');

        return $queryBuilder->executeQuery();
    }

    public function createQueryBuilder(string $table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }

    public function getConfiguration(int|string $dceIdentifier): array|false
    {
        $queryBuilder = $this->createQueryBuilder('tx_dce_domain_model_dce');
        $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dce');

        if (str_starts_with((string) $dceIdentifier, 'dce_dceuid')) {
            // Remove the `dceuid` prefix to get the actual UID value
            $dceIdentifier = substr((string) $dceIdentifier, strlen('dce_dceuid'));
        }

        if (is_int($dceIdentifier) || MathUtility::canBeInterpretedAsInteger($dceIdentifier)) {
            $queryBuilder
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($dceIdentifier))
                );
        } else {
            $queryBuilder
                ->where(
                    $queryBuilder->expr()->eq('identifier', $queryBuilder->createNamedParameter($dceIdentifier))
                );
        }

        return $queryBuilder->executeQuery()->fetchAssociative();
    }

    public function fetchFieldsByParentDce(int $uid): array
    {
        $queryBuilder = $this->createQueryBuilder('tx_dce_domain_model_dcefield');
        $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where(
                $queryBuilder->expr()->eq('parent_dce', $queryBuilder->createNamedParameter($uid, ParameterType::INTEGER))
            )
            ->orderBy('sorting', 'ASC');

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public function fetchFieldsByParentField(int $uid): array
    {
        $queryBuilder = $this->createQueryBuilder('tx_dce_domain_model_dcefield');
        $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where(
                $queryBuilder->expr()->eq('parent_field', $queryBuilder->createNamedParameter($uid, ParameterType::INTEGER))
            )
            ->orderBy('sorting', 'ASC');

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }
}
