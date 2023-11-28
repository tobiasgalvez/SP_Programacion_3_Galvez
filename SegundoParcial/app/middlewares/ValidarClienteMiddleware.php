<?php

//namespace App\Middleware;

//use DateTime;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
require_once './models/Validaciones.php';



class ValidarClienteMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $cliente = $request->getParsedBody();
        $fotos = $request->getUploadedFiles();

        $foto = $fotos['foto'];
        $nombreArchivo = $foto->getClientFilename();

        $nombre = $cliente['nombre'];
        $apellido = $cliente['apellido'];
        $tipoDocumento = $cliente['tipoDocumento'];
        $numeroDocumento = $cliente['numeroDocumento'];
        $tipoCliente = $cliente['tipoCliente'];
        $pais = $cliente['pais'];
        $ciudad = $cliente['ciudad'];
        $telefono = $cliente['telefono'];

        $errores = Validaciones::validarCliente($nombre, $apellido, $tipoDocumento, $numeroDocumento, $tipoCliente, $pais, $ciudad, $telefono);
        $errores = array_merge($errores, Validaciones::validarFoto($foto, $nombreArchivo));

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

