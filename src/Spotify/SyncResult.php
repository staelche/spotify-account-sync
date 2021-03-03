<?php

declare(strict_types=1);

namespace Staelche\SpotifyAccountSync\Spotify;

use Staelche\SpotifyAccountSync\Persistence\LibraryItem;

/**
 * Represents the result of a sync
 *
 * @author Thomas Stähle <staelche@buxs.org>
 */
class SyncResult
{
    /**
     * @var LibraryItem[]
     */
    private $itemsAddedToLeft = [];

    /**
     * @var LibraryItem[]
     */
    private $itemsDeletedFromLeft = [];

    /**
     * @var LibraryItem[]
     */
    private $itemsAddedToRight = [];

    /**
     * @var LibraryItem[]
     */
    private $itemsDeletedToRight = [];

    /**
     * Returns the items which where added to the left side
     *
     * @return LibraryItem[]
     */
    public function getItemsAddedToLeft(): array
    {
        return $this->itemsAddedToLeft;
    }

    /**
     * Adds an item which was added to the left side
     *
     * @param LibraryItem $addedLeft
     */
    public function addItemAddedToLeft(LibraryItem $addedLeft): void
    {
        $this->itemsAddedToLeft[] = $addedLeft;
    }

    /**
     * Adds items which were added to the left side
     *
     * @param LibraryItem[] $addedLeft
     */
    public function addItemsAddedToLeft(array $addedLeft): void
    {
        $this->itemsAddedToLeft = array_merge($this->itemsAddedToLeft, $addedLeft);
    }

    /**
     * Returns the items which were deleted from the left side
     *
     * @return LibraryItem[]
     */
    public function getItemsDeletedFromLeft(): array
    {
        return $this->itemsDeletedFromLeft;
    }

    /**
     * Adds an item which was deleted to the left side
     *
     * @param LibraryItem $deletedLeft
     */
    public function addItemDeletedFromLeft(LibraryItem $deletedLeft): void
    {
        $this->itemsDeletedFromLeft[] = $deletedLeft;
    }

    /**
     * Adds items which were deleted to the left side
     *
     * @param LibraryItem[] $deletedLeft
     */
    public function addItemsDeletedFromLeft(array $deletedLeft): void
    {
        $this->itemsDeletedFromLeft = array_merge($this->itemsDeletedFromLeft, $deletedLeft);
    }

    /**
     * Returns the items which were added to the right side
     *
     * @return LibraryItem[]
     */
    public function getItemsAddedToRight(): array
    {
        return $this->itemsAddedToRight;
    }

    /**
     * Adds an item which was added to the right side
     *
     * @param LibraryItem $addedRight
     */
    public function addItemAddedToRight(LibraryItem $addedRight): void
    {
        $this->itemsAddedToRight[] = $addedRight;
    }

    /**
     * Adds items which were added to the right side
     *
     * @param LibraryItem[] $addedRight
     */
    public function addItemsAddedToRight(array $addedRight): void
    {
        $this->itemsAddedToRight = array_merge($this->itemsAddedToRight, $addedRight);
    }

    /**
     * Returns the items which were deleted from the right side
     *
     * @return LibraryItem[]
     */
    public function getItemsDeletedToRight(): array
    {
        return $this->itemsDeletedToRight;
    }

    /**
     * Adds an item which was deleted from the right side
     *
     * @param LibraryItem $deletedRight
     */
    public function addItemDeletedFromRight(LibraryItem $deletedRight): void
    {
        $this->itemsDeletedToRight[] = $deletedRight;
    }

    /**
     * Adds items which were deleted from the right side
     *
     * @param LibraryItem[] $deletedRight
     */
    public function addItemsDeletedFromRight(array $deletedRight): void
    {
        $this->itemsDeletedToRight = array_merge($this->itemsDeletedToRight, $deletedRight);
    }

}