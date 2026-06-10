<?php
require __DIR__.'/vendor/autoload.php';
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
(new Dotenv())->bootEnv(__DIR__.'/.env');
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
$resto = $em->getRepository(App\Entity\Restaurant::class)->find(1);
$resto->setLatitude(45.793500);
$resto->setLongitude(4.788500);
$em->flush();
echo "Flushed\n";
