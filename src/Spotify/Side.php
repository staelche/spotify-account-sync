<?php

declare(strict_types=1);

namespace Staelche\SpotifyAccountSync\Spotify;

use MyCLabs\Enum\Enum;

/**
 * Enum to represent the both sides of a sync
 *
 * @author Thomas Stähle <staelche@buxs.org>
 *
 * @method static Side LEFT()
 * @method static Side RIGHT()
 */
class Side extends Enum
{
    /**
     * Represents the left side of the two accounts which are part of the sync
     *
     * @const string
     */
    const LEFT = 'left';

    /**
     * Represents the left side of the two accounts which are part of the sync
     *
     * @const string
     */
    const RIGHT = 'right';

}