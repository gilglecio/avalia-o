<?php

require_once 'vendor/autoload.php';

/**
 * Database params
 */
$db = require 'app/env.php';
$db = $db->db;

/**
 * @var Doctrine\ORM\Tools\Setup
 */
$config = Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration([__DIR__ . '/app/'], $isDevMode = true);

/**
 * @var Doctrine\ORM\EntityManager
 */
$entityManager = Doctrine\ORM\EntityManager::create([
	'dbname' => $db->name,
    'user' => $db->username,
    'password' => $db->password,
    'host' => $db->host,
    'driver' => 'mysqli',
], $config);

$platform = $entityManager->getConnection()->getDatabasePlatform();
$platform->registerDoctrineTypeMapping('enum', 'string');

return Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);