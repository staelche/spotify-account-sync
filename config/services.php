<?php

use Staelche\SpotifyAccountSync\Persistence\Persistence;
use Staelche\SpotifyAccountSync\Spotify\Spotify;
use Symfony\Component\DependencyInjection\Reference;

$containerBuilder
    ->register('Persistence', Persistence::class)
    ->addArgument($containerBuilder->getParameter('persistence'));

$containerBuilder
    ->register('Spotify', Spotify::class)
    ->addArgument($containerBuilder->getParameter('spotify'))
    ->addArgument(new Reference('Persistence'));