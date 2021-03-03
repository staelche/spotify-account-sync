<?php

declare(strict_types=1);

namespace Staelche\SpotifyAccountSync\Spotify;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Staelche\SpotifyAccountSync\Persistence\LibraryItem;
use Staelche\SpotifyAccountSync\Persistence\Persistence;

/**
 * Implements the sync logic and other utility methods
 *
 * @author Thomas Stähle <staelche@buxs.org>
 */
class Spotify
{

    /**
     * @var SpotifyWebAPI[]
     */
    private $sides = [];

    /**
     * @var array
     */
    private $apiOptions = [
        'auto_retry' => true
    ];

    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var array
     */
    private $config;

    /**
     * Constructor
     *
     * @param array $config
     * @param Persistence $persistence
     */
    public function __construct(array $config, Persistence $persistence)
    {
        $session = new Session(
            $config['api']['clientId'],
            $config['api']['clientSecret'],
            $config['api']['redirectionUrl']
        );
        foreach ($config['account'] as $side => $sideConfiguration) {

            $session->refreshAccessToken($sideConfiguration['token']);

            $this->sides[$side] = new SpotifyWebAPI();
            $this->sides[$side]->setOptions($this->apiOptions);
            $this->sides[$side]->setAccessToken($session->getAccessToken());
        }

        $this->persistence = $persistence;
        $this->config = $config;
    }

    /**
     * Sync albums between both sides
     *
     * @return SyncResult
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function syncAlbums(): SyncResult {

        $currentAlbums = $this->persistence->selectAll(ItemType::ALBUM());

        $leftAlbums = $this->getAlbums($this->sides[Side::LEFT]);
        $leftAlbumsToDelete = $this->calculateDifference($currentAlbums, $leftAlbums);
        $leftAlbumsToAdd = $this->calculateDifference($leftAlbums, $currentAlbums);

        $rightAlbums = $this->getAlbums($this->sides[Side::RIGHT]);
        $rightAlbumsToDelete = $this->calculateDifference($currentAlbums, $rightAlbums);
        $rightAlbumsToAdd = $this->calculateDifference($rightAlbums, $currentAlbums);

        // do changes on right side
        $this->addAlbums($this->sides[Side::RIGHT], $leftAlbumsToAdd);
        $this->deleteAlbums($this->sides[Side::RIGHT], $leftAlbumsToDelete);

        // do changes on left side
        $this->addAlbums($this->sides[Side::LEFT], $rightAlbumsToAdd);
        $this->deleteAlbums($this->sides[Side::LEFT], $rightAlbumsToDelete);

        // calculate difference so that we do not add one album multiple times (e.g. one album got added on both sides betweens syncs)
        $differenceAlbumsNewCurrentState = array_unique(
            array_merge(
                $leftAlbumsToAdd,
                $rightAlbumsToAdd,
                $rightAlbumsToAdd,
                $leftAlbumsToAdd
            )
        );
        $this->persistence->insertBulk($differenceAlbumsNewCurrentState);

        $differenceAlbumsOldCurrentState = array_unique(
            array_merge(
                $leftAlbumsToDelete,
                $rightAlbumsToDelete,
                $rightAlbumsToDelete,
                $leftAlbumsToDelete
            )
        );
        $this->persistence->deleteBulk($differenceAlbumsOldCurrentState);

        $syncResult = new SyncResult();
        $syncResult->addItemsAddedToLeft($rightAlbumsToAdd);
        $syncResult->addItemsAddedToRight($leftAlbumsToAdd);
        $syncResult->addItemsDeletedFromLeft($rightAlbumsToDelete);
        $syncResult->addItemsDeletedFromRight($leftAlbumsToDelete);

        return $syncResult;

    }

    /**
     * Returns albums from the Spotify API
     *
     * @param SpotifyWebAPI $side The API object
     *
     * @return LibraryItem[]
     */
    private function getAlbums(SpotifyWebAPI $side): array
    {
        $limit = 50;
        $offset = 0;
        $albums = [];
        do {

            $spotifyAlbums = $side->getMySavedAlbums(['limit' => $limit, 'offset' => $offset]);

            foreach ($spotifyAlbums->items as $spotifyAlbum) {
                $albums[] = LibraryItem::create(
                    ItemType::ALBUM(),
                    $spotifyAlbum->album->id,
                    $spotifyAlbum->album->name);
            }

            $offset += $limit;

        } while (!empty($spotifyAlbums->next));

        return $albums;

    }

    /**
     * Adds albums via the Spotify API
     *
     * @param SpotifyWebAPI $side
     * @param LibraryItem[] $albums
     */
    private function addAlbums(SpotifyWebAPI $side, array $albums): void
    {
        $chunkedAlbumsToAdd = array_chunk($albums, 50);

        foreach ($chunkedAlbumsToAdd as $chunk) {
            $side->addMyAlbums($chunk);
        }
    }

    /**
     * Deletes albums via the Spotify API
     *
     * @param SpotifyWebAPI $side
     * @param LibraryItem[] $albums
     */
    private function deleteAlbums(SpotifyWebAPI $side, array $albums): void
    {
        $chunkedAlbumsToDelete = array_chunk($albums, 50);

        foreach ($chunkedAlbumsToDelete as $chunk) {
            $side->deleteMyAlbums($chunk);
        }
    }

    /**
     * Syncs playlists between both sides
     *
     * @return SyncResult
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function syncPlaylists(): SyncResult
    {

        $currentPlaylists = $this->persistence->selectAll(ItemType::PLAYLIST());

        $leftPlaylists = $this->getPlaylists($this->sides[Side::LEFT]);
        $leftPlaylistsToDelete = $this->calculateDifference($currentPlaylists, $leftPlaylists);
        $leftPlaylistsToAdd = $this->calculateDifference($leftPlaylists, $currentPlaylists);

        $rightPlaylists = $this->getPlaylists($this->sides[Side::RIGHT]);
        $rightPlaylistsToDelete = $this->calculateDifference($currentPlaylists, $rightPlaylists);
        $rightPlaylistsToAdd = $this->calculateDifference($rightPlaylists, $currentPlaylists);

        // do changes on right side
        foreach ($leftPlaylistsToAdd as $playlist) {
            $this->sides[Side::RIGHT]->followPlaylist($playlist->getId());
        }
        foreach ($leftPlaylistsToDelete as $playlist) {
            $this->sides[Side::RIGHT]->unfollowPlaylist($playlist->getId());
        }

        // do changes on left side
        foreach ($rightPlaylistsToAdd as $playlist) {
            $this->sides[Side::LEFT]->followPlaylist($playlist->getId());
        }
        foreach ($rightPlaylistsToDelete as $playlist) {
            $this->sides[Side::LEFT]->unfollowPlaylist($playlist->getId());
        }

        // calculate difference so that we do not add one album multiple times (e.g. one album got added on both sides betweens syncs)
        $differencePlaylistsNewCurrentState = array_unique(
            array_merge(
                $leftPlaylistsToAdd,
                $rightPlaylistsToAdd,
                $rightPlaylistsToAdd,
                $leftPlaylistsToAdd
            )
        );
        $this->persistence->insertBulk($differencePlaylistsNewCurrentState);

        $differencePlaylistsOldCurrentState = array_unique(
            array_merge(
                $leftPlaylistsToDelete,
                $rightPlaylistsToDelete,
                $rightPlaylistsToDelete,
                $leftPlaylistsToDelete
            )
        );
        $this->persistence->deleteBulk($differencePlaylistsOldCurrentState);

        $syncResult = new SyncResult();
        $syncResult->addItemsAddedToLeft($rightPlaylistsToAdd);
        $syncResult->addItemsAddedToRight($leftPlaylistsToAdd);
        $syncResult->addItemsDeletedFromLeft($rightPlaylistsToDelete);
        $syncResult->addItemsDeletedFromRight($leftPlaylistsToDelete);

        return $syncResult;

    }

    /**
     * Returns playlists from the Spotify API
     *
     * @param SpotifyWebAPI $side The API object
     *
     * @return LibraryItem[]
     */
    private function getPlaylists(SpotifyWebAPI $side): array
    {
        $limit = 50;
        $offset = 0;
        $playlists = [];
        do {

            $spotifyPlaylists = $side->getMyPlaylists(['limit' => $limit, 'offset' => $offset]);

            foreach ($spotifyPlaylists->items as $spotifyPlaylist) {
                $playlists[] = LibraryItem::create(
                        ItemType::PLAYLIST(),
                        $spotifyPlaylist->id,
                        $spotifyPlaylist->name);
            }

            $offset += $limit;

        } while (!empty($spotifyPlaylists->next));

        return $playlists;

    }

    /**
     * Syncs shows between both sides
     *
     * @return SyncResult
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function syncShows(): SyncResult
    {
        $currentShows = $this->persistence->selectAll(ItemType::SHOW());

        $leftShows = $this->getShows($this->sides[Side::LEFT]);
        $leftShowsToDelete = $this->calculateDifference($currentShows, $leftShows);
        $leftShowsToAdd = $this->calculateDifference($leftShows, $currentShows);

        $rightShows = $this->getShows($this->sides[Side::RIGHT]);
        $rightShowsToDelete = $this->calculateDifference($currentShows, $rightShows);
        $rightShowsToAdd = $this->calculateDifference($rightShows, $currentShows);

        // do changes on right side
        foreach ($leftShowsToAdd as $show) {
            $this->sides[Side::RIGHT]->addMyShows($show->getId());
        }
        foreach ($leftShowsToDelete as $show) {
            $this->sides[Side::RIGHT]->deleteMyShows($show->getId());
        }

        // do changes on left side
        foreach ($rightShowsToAdd as $show) {
            $this->sides[Side::LEFT]->addMyShows($show->getId());
        }
        foreach ($rightShowsToDelete as $show) {
            $this->sides[Side::LEFT]->deleteMyShows($show->getId());
        }

        // calculate difference so that we do not add one album multiple times (e.g. one album got added on both sides betweens syncs)
        $differenceShowsNewCurrentState = array_unique(
            array_merge(
                $leftShowsToAdd,
                $rightShowsToAdd,
                $rightShowsToAdd,
                $leftShowsToAdd
            )
        );
        $this->persistence->insertBulk($differenceShowsNewCurrentState);

        $differenceShowsOldCurrentState = array_unique(
            array_merge(
                $leftShowsToDelete,
                $rightShowsToDelete,
                $rightShowsToDelete,
                $leftShowsToDelete
            )
        );
        $this->persistence->deleteBulk($differenceShowsOldCurrentState);

        $syncResult = new SyncResult();
        $syncResult->addItemsAddedToLeft($rightShowsToAdd);
        $syncResult->addItemsAddedToRight($leftShowsToAdd);
        $syncResult->addItemsDeletedFromLeft($rightShowsToDelete);
        $syncResult->addItemsDeletedFromRight($leftShowsToDelete);

        return $syncResult;

    }

    /**
     * Returns shows from the Spotify API
     *
     * @param SpotifyWebAPI $side The API object
     *
     * @return LibraryItem[]
     */
    private function getShows(SpotifyWebAPI $side): array
    {
        $limit = 50;
        $offset = 0;
        $shows = [];
        do {

            $spotifyShows = $side->getMySavedShows(['limit' => $limit, 'offset' => $offset]);

            foreach ($spotifyShows->items as $spotifyShow) {
                $shows[] = LibraryItem::create(
                    ItemType::SHOW(),
                    $spotifyShow->show->id,
                    $spotifyShow->show->name);
            }

            $offset += $limit;

        } while (!empty($spotifyShows->next));

        return $shows;

    }

    /**
     * Syncs (Follows) the artists found in the albums collection
     *
     * @param Side $side The side to sync
     *
     * @return SyncResult
     */
    public function syncArtistsFromAlbums(Side $side): SyncResult
    {
        $foundArtists = $this->getAlbumArtists($this->sides[$side->getValue()]);
        $existingArtists = $this->getArtists($this->sides[$side->getValue()]);

        $missingArtists = $this->calculateDifference($foundArtists, $existingArtists);
        $removedArtists = $this->calculateDifference($existingArtists, $foundArtists);

        $chunkedMissingArtists = array_chunk($missingArtists, 50);
        foreach ($chunkedMissingArtists as $key => $chunk) {
            $this->sides[$side->getValue()]->followArtistsOrUsers('artist', $chunk);
        }

        $chunkedRemovedArtists = array_chunk($removedArtists, 50);
        foreach ($chunkedRemovedArtists as $key => $chunk) {
            $this->sides[$side->getValue()]->unfollowArtistsOrUsers('artist', $chunk);
        }

        // left is the source and here the albums artists, right the already follows artists
        $syncResult = new SyncResult();
        $syncResult->addItemsAddedToRight($missingArtists);
        $syncResult->addItemsDeletedFromRight($removedArtists);

        return $syncResult;

    }

    /**
     * Returns artists of albums from the Spotify API
     *
     * @param SpotifyWebAPI $side The API object
     *
     * @return LibraryItem[]
     */
    private function getAlbumArtists(SpotifyWebAPI $side): array
    {
        $limit = 50;
        $offset = 0;
        $artists = [];

        do {

            $spotifyAlbums = $side->getMySavedAlbums(['limit' => $limit, 'offset' => $offset]);

            foreach ($spotifyAlbums->items as $spotifyAlbum) {

                if (in_array($spotifyAlbum->album->artists[0]->id, $artists) === false) {
                    $artists[] = LibraryItem::create(
                        ItemType::ARTIST(),
                        $spotifyAlbum->album->artists[0]->id,
                        $spotifyAlbum->album->artists[0]->name);
                }

            }

            $offset += $limit;

        } while (!empty($spotifyAlbums->next));

        return $artists;

    }

    /**
     * Returns follows artists from the Spotify API
     *
     * @param SpotifyWebAPI $side The API object
     *
     * @return LibraryItem[]
     */
    private function getArtists(SpotifyWebAPI $side): array
    {
        $limit = 50;
        $after = '';
        $artists = [];
        do {

            $options = ['limit' => $limit];
            if (!empty($after)) {
                $options['after'] = $after;
            }

            $spotifyArtists = $side->getUserFollowedArtists($options);

            foreach ($spotifyArtists->artists->items as $spotifyArtist) {
                $artists[] = LibraryItem::create(
                    ItemType::ARTIST(),
                    $spotifyArtist->id,
                    $spotifyArtist->name);
            }

            $after = $spotifyArtists->artists->cursors->after;

        } while (!empty($spotifyArtists->artists->cursors->after));

        return $artists;
    }

    /**
     * Returns all items which are in $a but not in $b
     *
     * @param LibraryItem[] $a
     * @param LibraryItem[] $b
     *
     * @return LibraryItem[]
     */
    private function calculateDifference(array $a, array $b)
    {
        return array_diff($a, $b);
    }

    /**
     * Returns a refresh token for a given authorization token
     *
     * @param string $token
     *
     * @return string
     */
    public function requestRefreshToken(string $token): string
    {
        $session = new Session(
            $this->config['api']['clientId'],
            $this->config['api']['clientSecret'],
            $this->config['api']['redirectionUrl']
        );

        $session->requestAccessToken($token);

        return $session->getRefreshToken();
    }

}