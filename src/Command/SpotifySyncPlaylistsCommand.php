<?php
declare(strict_types=1);

namespace Staelche\SpotifyAccountSync\Command;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Staelche\SpotifyAccountSync\Spotify\Spotify;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Thomas Stähle <staelche@buxs.org>
 */
class SpotifySyncPlaylistsCommand extends Command
{

    protected static $defaultName = 'spotify:sync:playlists';

    /**
     * @var Spotify
     */
    private $spotify;

    public function __construct(string $name = null, Spotify $spotify)
    {
        $this->spotify = $spotify;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Syncs all playlists between the left account to the right account');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $result = $this->spotify->syncPlaylists();

        $output->writeln(sprintf('Playlists added to the left side:'));
        if(count($result->getItemsAddedToLeft()) > 0) {
            foreach ($result->getItemsAddedToLeft() as $item) {
                $output->writeln($item->getName());
            }
        } else {
            $output->writeln('-');
        }

        $output->writeln('');

        $output->writeln(sprintf('Playlists deleted from the left side:'));
        if(count($result->getItemsDeletedFromLeft()) > 0) {
            foreach ($result->getItemsDeletedFromLeft() as $item) {
                $output->writeln($item->getName());
            }
        } else {
            $output->writeln('-');
        }

        $output->writeln('');

        $output->writeln(sprintf('Playlists added to the right side:'));
        if(count($result->getItemsAddedToRight()) > 0) {
            foreach ($result->getItemsAddedToRight() as $item) {
                $output->writeln($item->getName());
            }
        } else {
            $output->writeln('-');
        }

        $output->writeln('');

        $output->writeln(sprintf('Playlists deleted from the right side:'));
        if(count($result->getItemsDeletedToRight()) > 0) {
            foreach ($result->getItemsDeletedToRight() as $item) {
                $output->writeln($item->getName());
            }
        } else {
            $output->writeln('-');
        }

        return Command::SUCCESS;

    }

}