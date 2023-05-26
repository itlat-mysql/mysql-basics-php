<?php

use App\DAO\ProductDAO;
use Dotenv\Dotenv;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

return function (App $app): void {
    // загружаем переменные окружения
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    // получаем контейнер для назначения зависимостей
    $container = $app->getContainer();

    // устанавливаем шаблонизатор
    $container->set('view', function () {
        return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
    });

    // инициализация соединения с базой данных (параметры берутся из переменных окружения + см. файл .env)
    $container->set('db_connection', function () {
        $dns = sprintf(
            'mysql:host=%s;dbname=%s;port=%s;charset=%s',
            $_ENV['DB_HOST'],
            $_ENV['DB_DATABASE'],
            $_ENV['DB_PORT'],
            $_ENV['DB_CHARSET']
        );

        return new PDO($dns, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    });

    // устанавливаем объект для работы с данными продуктов
    $container->set('product_dao', function ($container) {
        return new ProductDAO($container->get('db_connection'));
    });

    // включаем адекватное отображение ошибок
    $app->addErrorMiddleware(false, false, false);

    // включаем шаблонизатор в цепочку посредников
    $app->add(TwigMiddleware::createFromContainer($app));
};
