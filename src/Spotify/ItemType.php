<?php

declare(strict_types=1);

namespace Staelche\SpotifyAccountSync\Spotify;

use MyCLabs\Enum\Enum;

/**
 * Enum to represent the item types
 *
 * @author Thomas Stähle <staelche@buxs.org>
 *
 * @method static ItemType ALBUM()
 * @method static ItemType ARTIST()
 * @method static ItemType PLAYLIST()
 * @method static ItemType SHOW()
 */
class ItemType extends Enum
{
    /**
     * Represents an album item
     *
     * @const string
     */
    const ALBUM = 'album';

    /**
     * Represents an artist item
     *
     * @const string
     */
    const ARTIST = 'artist';

    /**
     * Represents an playlist item
     *
     * @const string
     */
    const PLAYLIST = 'playlist';

    /**
     * Represents a show item
     *
     * @const string
     */
    const SHOW = 'show';

}