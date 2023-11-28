<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;


require __DIR__ . '/../vendor/autoload.php';
require_once './db/AccesoDatos.php';
//require_once './controllers/UsuarioController.php';


require_once './controllers/ClienteController.php';
require_once './controllers/ReservaController.php';

require_once './middlewares/ValidarClienteMiddleware.php';

require_once './middlewares/ValidarReservaMiddleware.php';

// require_once './controllers/PedidoController.php';
// //require_once './controllers/AuthController.php';
// require_once './middlewares/Logger.php';

// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->safeLoad();

// print_r($_ENV);
//php -S localhost:888 -t app
// Instantiate App
$app = AppFactory::create();

// $app->setBasePath('/app');
//$app->setBasePath('/slim-php-deployment/app/');

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();



$app->group('/cliente', function (RouteCollectorProxy $group)
{
    $group->post('[/]',      'ClienteController:CargarUno')->add(new ValidarClienteMiddleware());
    $group->put('[/]',      'ClienteController:ModificarUno')/*->add(new ValidarClienteMiddleware())*/;

});



$app->group('/consultarUno', function (RouteCollectorProxy $group)
{
    $group->post('[/]',      'ClienteController:TraerUno');
   

    // $group->get('/{id}',  'PedidoController:TraerUno');
    // $group->get('[/]',           'PedidoController:TraerTodos');
    // $group->put('[/]',  'PedidoController:ModificarUno');
    // $group->put('/{id}',  'PedidoController:AsignarHorarioPedido');
    // $group->delete('/{id}',  'PedidoController:BorrarUno'); 

});



$app->group('/reserva', function (RouteCollectorProxy $group)
{
    $group->post('[/]',      'ReservaController:CargarUno')/*->add(new ValidarReservaMiddleware())*/;
    //$group->get('[/]',      'ReservaController:TraerUno');

});



   $app->get('/consultarReserva', 'ReservaController:TraerUno');
   $app->post('/cambiarEstadoReserva',      'ReservaController:BorrarUno');

   $app->post('/ajustarReserva', 'ReservaController:AjustarUno');

   $app->delete('/eliminarCliente',  'ClienteController:BorrarUno'); 


// $app->group('/pedidosProducto', function (RouteCollectorProxy $group)
// {
//         $group->post('[/]', 'PedidoController:AgregarProductoAPedido');
    
//         $group->get('/{id}', 'PedidoController:TraerProductosDePedido');

// });




$app->run();
