<?php
declare(strict_types=1);

namespace Staelche\SpotifyAccountSync\Command;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;
use Staelche\SpotifyAccountSync\Spotify\Side;
use Staelche\SpotifyAccountSync\Spotify\Spotify;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Thomas Stähle <staelche@buxs.org>
 */
class SpotifySyncArtistsFromAlbumsCommand extends Command
{

    const ARGUMENT_SIDE = 'side';

    protected static $defaultName = 'spotify:sync:artists-from-albums';

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
            ->addArgument(self::ARGUMENT_SIDE, InputArgument::REQUIRED, 'The side to sync')
            ->setDescription('Syncs all artists found in the albums library to the artist library');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $side = new Side($input->getArgument('side'));

        $result = $this->spotify->syncArtistsFromAlbums($side);

        $output->writeln(sprintf('Artists added:'));
        if (count($result->getItemsAddedToRight()) > 0) {
            foreach ($result->getItemsAddedToRight() as $item) {
                $output->writeln($item->getName());
            }
        } else {
            $output->writeln('-');
        }

        $output->writeln(sprintf('Artists deleted:'));
        if (count($result->getItemsDeletedToRight()) > 0) {
            foreach ($result->getItemsDeletedToRight() as $item) {
                $output->writeln($item->getName());
            }
        } else {
            $output->writeln('-');
        }

        return Command::SUCCESS;

    }

}