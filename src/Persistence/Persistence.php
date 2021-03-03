<?php

declare(strict_types=1);

namespace Staelche\SpotifyAccountSync\Persistence;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\ParameterType;
use Staelche\SpotifyAccountSync\Spotify\ItemType;

/**
 * Contains all SQL queries to manage the persistence
 *
 * @author Thomas Stähle <staelche@buxs.org>
 */
class Persistence
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $connectionParams = array(
            'path' => $config['path'],
            'user' => $config['user'],
            'password' => $config['password'],
            'driver' => $config['driver']
        );

        $this->connection = DriverManager::getConnection($connectionParams);

        // create the schema running for the very first time
        if(count($this->connection->getSchemaManager()->listTables()) === 0) {
            $this->initializeSchema();
        }
    }

    /**
     * Initializes and updates the schema on the fly before an application runs
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    private function initializeSchema(): void
    {
        $definition = AccountSyncSchema::getSchemaDefinition();
        $queries = $definition->toSql($this->connection->getDatabasePlatform());

        foreach ($queries as $query) {
            $this->connection->executeQuery($query);
        }
    }

    /**
     * Insderts multiple rows
     *
     * @param LibraryItem[] $items
     *
     * @return int The number of inserted rows
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function insertBulk(array $items): int
    {
        $now = new \DateTimeImmutable('now');

        $sql = 'INSERT INTO library (id, type, name, created, updated, synced) VALUES (:id, :type, :name, :created, :updated, :synced)';

        $statement = $this->connection->prepare($sql);

        $insertedRows = 0;
        foreach ($items as $item) {

            $statement->bindValue('id', $item->getId(), ParameterType::STRING);
            $statement->bindValue('type', $item->getType(), ParameterType::STRING);
            $statement->bindValue('name', $item->getName(), ParameterType::STRING);
            $statement->bindValue('created', $now, 'datetime');
            $statement->bindValue('updated', $now, 'datetime');
            $statement->bindValue('synced', $now, 'datetime');

            $statement->execute();

            $insertedRows++;
        }

        return $insertedRows;
    }

    /**
     * Selects all rows of a given type
     *
     * @param ItemType $type
     *
     * @return LibraryItem[]
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function selectAll(ItemType $type): array
    {
        $sql = 'SELECT * FROM library WHERE type = :type';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue('type', $type->getValue(), ParameterType::STRING);
        $result = $statement->execute();

        $list = [];
        foreach ($result->fetchAllAssociative() as $row) {
            $list[] = LibraryItem::createFromDatabaseArray($row);
        }

        return $list;
    }

    /**
     * Deletes multiple rows
     *
     * @param LibraryItem[] $items
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteBulk(array $items): void
    {
        $sql = 'DELETE FROM library WHERE id = :id AND type = :type';

        $statement = $this->connection->prepare($sql);

        foreach ($items as $item) {

            $statement->bindValue('id', $item->getId(), ParameterType::STRING);
            $statement->bindValue('type', $item->getType(), ParameterType::STRING);

            $statement->execute();
        }
    }

}