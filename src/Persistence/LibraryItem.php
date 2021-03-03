<?php

declare(strict_types=1);

namespace Staelche\SpotifyAccountSync\Persistence;

use Staelche\SpotifyAccountSync\Spotify\ItemType;

/**
 * Entity of a library item
 *
 * @author Thomas Stähle <staelche@buxs.org>
 */
class LibraryItem
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var ItemType
     */
    private $type;

    /**
     * Creates an item
     *
     * @param ItemType $type The item type
     * @param string $id Spotifys Id
     * @param string $name Spotifys name
     *
     * @return LibraryItem The entity
     */
    public static function create(ItemType $type, string $id, string $name): LibraryItem
    {
        $item = new static();

        $item->type = $type;
        $item->id = $id;
        $item->name = $name;

        return $item;
    }

    /**
     * Creates an item based on the database representation
     *
     * @param array $row The databases row with all columns
     *
     * @return LibraryItem The entity
     */
    public static function createFromDatabaseArray(array $row): LibraryItem
    {
        $item = new static();

        $item->id = $row['id'];
        $item->type = new ItemType($row['type']);
        $item->name = $row['name'];

        return $item;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
    }

    /**
     * Returns the entities string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->id;
    }

    /**
     * Returns Spotifys Id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Sets Spotifys Id
     *
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns Spotifys name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets Spotifys name
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns the item type
     *
     * @return ItemType
     */
    public function getType(): ItemType
    {
        return $this->type;
    }

    /**
     * Sets the item type
     *
     * @param ItemType $type
     */
    public function setType(ItemType $type): void
    {
        $this->type = $type;
    }

}