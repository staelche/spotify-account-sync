<?php
declare(strict_types=1);

namespace Staelche\SpotifyAccountSync\Command;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Staelche\SpotifyAccountSync\Spotify\Spotify;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Thomas Stähle <staelche@buxs.org>
 */
class SpotifyGetRefreshTokenCommand extends Command
{

    protected static $defaultName = 'spotify:get:refresh-token';

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
            // get the code from running trough the Oauth process here:
            // https://accounts.spotify.com/authorize?response_type=code&client_id=[id]]&scope=user-follow-modify,user-follow-read,playlist-modify-public,playlist-modify-private,playlist-read-private,playlist-read-collaborative,user-library-modify,user-library-read&redirect_uri=http%3A%2F%2Flocalhost%2Ftesta
            ->addArgument('code', InputArgument::REQUIRED, 'The authorization code')
            ->setDescription('Requests a refresh token with the help of the specified access code');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $refreshToken = $this->spotify->requestRefreshToken((string) $input->getArgument('code'));

        $output->write('Refresh Token: ');
        $output->writeln($refreshToken);

        return Command::SUCCESS;

    }

}