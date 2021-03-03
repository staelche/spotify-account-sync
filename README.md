# spotify-account-sync
Spotify Account Sync is a simple application syncing albums, shows and playlists between two Spotify accounts. You can simply maintain one common library between 2 accounts 
while adding and removing on both accounts. As well, there is the possibility to generate a list of artists from the list of albums. With this feature you do not have to follow
or unfollow artists separately next to your albums. They are always perfectly in sync.

Spotify Account Sync is written in PHP with the help of 
* Symfony Console
* Symfony DependencyInjection
* Doctrine Dbal

# Use of Spotify Account Sync

## Prerequisites
Before using Spotify Account Sync you need to register yourself at Spotify as a developer and create an application with a `clientId`, `clientSecret` and `redirectionUrl`.
After doing this, you need to visit the following Spotify Oauth permission page with each of the two account and grant your application permission to read and alter different
item lists like albums, playists and so on:

    https://accounts.spotify.com/authorize?response_type=code&client_id=[clientId]&redirect_uri=[URL encoded redirectiontUrl]&scope=user-follow-modify,user-follow-read,playlist-modify-public,playlist-modify-private,playlist-read-private,playlist-read-collaborative,user-library-modify,user-library-read

Replace `clientId` and `redirectionUrl` with your values. The redirectionUrl page does not have to exist. But it has to be exactly the one you configured at you Spotify 
application settings. You can use something like:

    http://localhost/something
    
Spotify will redirect you after granting the permissions to this page. If it does not exist your browser will fail to load the page it but in the URL query string you can 
copy the authorization code from the query string parameter `code`. The URL looks like this:

    http://localhost/something?code=AQDvLygNc9xBc5...

This authorization code you need to use to request a refresh token within a certain amount of time. This can be done with the command `spotify:get:refresh-token`. 
The `refresh tokens` have to be added to the configuration.

After checking out this application you need to have an environment from which you can run PHP applications from available. As well, you need composer and several PHP extensions
available which are named in the `composer.json` file. Run

    composer install
    
to install the dependencies. 

Copy the `config/config.php.dist` to `config/config.php` and fill out the following settings with your ``refresh tokens`, `clientId`, `clientSecret`, `redirectionUrl`:

    // spotify
    $config['spotify']['account']['left']['token'] = '';
    $config['spotify']['account']['right']['token'] = '';
    $config['spotify']['api']['clientId'] = '';
    $config['spotify']['api']['clientSecret'] = '';
    $config['spotify']['api']['redirectionUrl'] = '';

Now you are good to go. 

In the end you need to decide if you want to run this on a server (started via cron automatically) or desktop computer (executing the commands manually from time to time).
Both is absolutely fine and depends on your requirements.

## Things to consider ...

### ... at the first run

Starting the sync of e.g. albums, playlists or shows (all are independent commands) for the first time will sync all missing items from one side to the other and vice versa.
This means you will end up in two accounts which are totally in sync. If you do not want to sync certain items you should delete them first! Ohterwise, you have to deletes 
them twice after running the sync. In case one of the accounts is empty this is the best situation to start from.

### ... about the persistence

To be able to sync the accounts in both ways the application stores the state of the last run in a small sqlite database. During the next sync run it compares both accounts
against the last state stored in the database. Detects additions and removals on both sides and executes them on the other account. Deleting the database file 
`spotify-account-sync.sqlite` under `data/` will recreate the file on the next run and the application will behave like during the first run. Currently, there is no 
difference basically but maybe there will be a difference in the future. So keep in mind to keep the database file while moving around your application.

Since sqlite is a very lightweight database without any daemon running you do not need to install another sevrvice/daemon next to PHP. Only the sqlite PHP extension. 

### ... running some commands

The semantic of this application differentiate between a left (One account) and a right side (The other account). Only syncing between two accounts is currently supported. 
Some commands do their work only on one account at a time. So these commands take as an argument the side you want to do something on.

## The commands

A simple `php bin/console` will list you all available commands:

    Spotify Two-Way Account Sync v0.1
    
    Usage:
      command [options] [arguments]

    Options:
        ...
    
    Available commands:
      help                              Displays help for a command
      list                              Lists commands
     spotify
      spotify:get:refresh-token         Requests a refresh token with the help of the specified access code
      spotify:sync:albums               Syncs all albums between the left account to the right account
      spotify:sync:artists-from-albums  Syncs all artists found in the albums library to the artist library
      spotify:sync:playlists            Syncs all playlists between the left account to the right account
      spotify:sync:shows                Syncs all shows between the left account to the right account

Getting help for each account you can run `php bin/console [command] --help` which lists the available options and arguments.

A complete account sync would look like executing the following commands:

    # php bin/console spotify:sync:albums
    # php bin/console spotify:sync:artists-from-albums left
    # php bin/console spotify:sync:artists-from-albums right
    # php bin/console spotify:sync:playlists 
    # php bin/console spotify:sync:shows

# Licence
TBA
