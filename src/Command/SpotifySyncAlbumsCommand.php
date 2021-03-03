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
class SpotifySyncAlbumsCommand extends Command
{

    protected static $defaultName = 'spotify:sync:albums';

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
            ->setDescription('Syncs all albums between the left account to the right account');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $result = $this->spotify->syncAlbums();

        $output->writeln(sprintf('Albums added to the left side:'));
        if(count($result->getItemsAddedToLeft()) > 0) {
            foreach ($result->getItemsAddedToLeft() as $item) {
                $output->writeln($item->getName());
            }
        } else {
            $output->writeln('-');
        }

        $output->writeln('');

        $output->writeln(sprintf('Albums deleted from the left side:'));
        if(count($result->getItemsDeletedFromLeft()) > 0) {
            foreach ($result->getItemsDeletedFromLeft() as $item) {
                $output->writeln($item->getName());
            }
        } else {
            $output->writeln('-');
        }

        $output->writeln('');

        $output->writeln(sprintf('Albums added to the right side:'));
        if(count($result->getItemsAddedToRight()) > 0) {
            foreach ($result->getItemsAddedToRight() as $item) {
                $output->writeln($item->getName());
            }
        } else {
            $output->writeln('-');
        }

        $output->writeln('');

        $output->writeln(sprintf('Albums deleted from the right side:'));
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