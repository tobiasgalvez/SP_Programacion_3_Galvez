<?php
//namespace App\Middleware;

//use DateTime;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
require_once './models/Validaciones.php';

class ValidarReservaMiddleware
{

    public function __invoke(Request $request, RequestHandler $handler)
    {
        //echo "estoy en el invoke";
        $reserva = $request->getParsedBody();
        //$fotos = $request->getUploadedFiles();
        //var_dump($reserva);

        $idCliente = $reserva['idCliente'];
        $tipoHabitacion = $reserva['tipoHabitacion'];
        $importeTotalReserva = $reserva['importeTotal'];
       // $imagenConfirmacion = $reserva['imagenConfirmacion'];
        $fechaEntrada = $reserva['fechaEntrada'];
        $fechaSalida = $reserva['fechaSalida'];
        //var_dump($importeTotalReserva);
        //echo $importeTotalReserva;

        $errores = Validaciones::validarReserva($idCliente, $tipoHabitacion, $importeTotalReserva,$fechaEntrada, $fechaSalida);
        

        if (!empty($errores)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode($errores));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
      
        $response = $handler->handle($request);
        if ($response === null) {
            $response = new \Slim\Psr7\Response();
        }
      
        return $response;
    }


    
}
