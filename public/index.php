<?php

use App\DAO\Filtration;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;


// подключение автозагрузчика
require __DIR__ . '/../vendor/autoload.php';


// установка контейнера и самого объекта приложения
$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();


// подключение зависимостей (база данных, шаблонизатор и т.д.)
(require_once __DIR__ . '/../config/dependencies.php')($app);


// показываем все продукты безо всяких условий
$app->get('/', function (Request $request, Response $response) {
    $products = $this->get('product_dao')->getAllProducts();
    
    return $this->get('view')->render($response, 'show-all-products.twig', compact('products'));
})->setName('show-all-products');


// показываем продукты, которые соответствуют присланным в запросе условиям
$app->get('/search/', function (Request $request, Response $response) {
    $queryParams = $request->getQueryParams();

    $filtration = (new Filtration())
        ->equal('id', $queryParams['id'] ?? '', 255)
        ->like('name', $queryParams['name'] ?? '', 255)
        ->like('ean', $queryParams['ean'] ?? '', 255)
        ->greaterEqual('price', $queryParams['price_gte'] ?? '', 255)
        ->lessEqual('price', $queryParams['price_lte'] ?? '', 255);

    $products = $this->get('product_dao')->getProductsWithFiltration($filtration);

    return $this->get('view')->render($response, 'search-products.twig', compact(
        'products', 'queryParams'
    ));
})->setName('search-products');


// показываем один конкретный продукт
$app->get('/product/{id:[0-9]+}', function (Request $request, Response $response, $args) {
    // если число окажется слишком большим, то PHP автоматически назначит максимальное значение типа int
    $id = (int)$args['id'];
    $product = $this->get('product_dao')->getSingleProduct($id);

    if ($product === null) {
        throw new HttpNotFoundException($request);
    }

    return $this->get('view')->render($response, 'show-single-product.twig', compact('product'));
})->setName('show-single-product');


// показываем продукты по несколько штук на странице (с использованием постраничной навигации)
$app->get('/pages/', function (Request $request, Response $response) {
    // получим общее количество продуктов и посчитаем, сколько у нас вообще может быть страниц
    $dao = $this->get('product_dao');
    $productsQty = $dao->getProductsQty();
    $qtyPerPage = 2;
    $pages = ceil($productsQty / $qtyPerPage);

    # если номер пришедшей страницы слишком маленький или слишком большой - вернем 404 ошибку (данные не найдены)
    $page = $request->getQueryParams()['page'] ?? 1;
    if (!is_numeric($page) || $page < 1 || $page > $pages) {
        throw new HttpNotFoundException($request);
    }
    $page = (int)$page;

    $currentOffset = ($page - 1) * $qtyPerPage;
    $products = $dao->getProductsWithLimitAndOffset($qtyPerPage, $currentOffset);

    return $this->get('view')->render($response, 'split-products-by-pages.twig', compact(
        'products', 'pages', 'qtyPerPage', 'page'
    ));
})->setName('split-products-by-pages');


// запуск приложения
$app->run();
