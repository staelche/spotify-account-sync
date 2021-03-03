<?php

declare(strict_types=1);

namespace Staelche\SpotifyAccountSync\Persistence;

use Doctrine\DBAL\Schema\Schema;

/**
 * Represents the schema needed to make the sync work
 *
 * @author Thomas Stähle <staelche@buxs.org>
 */
class AccountSyncSchema
{
    /**
     * Constructor
     */
    private function __construct()
    {
    }

    /**
     * Returns the schema
     *
     * @return Schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public static function getSchemaDefinition(): Schema
    {
        $schema = new Schema();
        $libraryTable = $schema->createTable('library_item');
        $libraryTable->addColumn('id', 'string', [
            'notnull' => true,
            'customSchemaOptions' =>
                ['unique' => true],
            'comment' => 'Spotifies unique ID string'
        ]);
        $libraryTable->addColumn('type', 'string', [
            "length" => 20,
            'comment' => 'The type of the entry (e.g. album, playlist, ...)'
        ]);
        $libraryTable->addColumn('name', 'string', [
            "length" => 255,
            'comment' => 'A displayable name of the "item" (e.g. the albums name, the artists name, ...)'
        ]);
        $libraryTable->addColumn('created', 'datetime_immutable', [
            "length" => 255,
            'comment' => 'The entries creation date'
        ]);
        $libraryTable->addColumn('updated', 'datetime_immutable', [
            "length" => 255,
            'comment' => 'The entries last change date'
        ]);
        $libraryTable->addColumn('synced', 'datetime_immutable', [
            "length" => 255,
            'comment' => 'The entries last synced date'
        ]);
        $libraryTable->addIndex(['id'], 'id');

        return $schema;
    }

}