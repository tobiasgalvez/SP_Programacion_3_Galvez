<?php

require_once './models/Cliente.php';
require_once './interfaces/IApiUsable.php';

class ClienteController extends Cliente implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $fileFoto = $request->getUploadedFiles()['foto'];




    $nombre = $parametros['nombre'];
    $apellido = $parametros['apellido'];
    $mail = $parametros['mail'];
    $tipoDocumento = $parametros['tipoDocumento'];
    $numeroDocumento = $parametros['numeroDocumento'];
    $tipoCliente = $parametros['tipoCliente'];
    $pais = $parametros['pais'];
    $ciudad = $parametros['ciudad'];
    $telefono = $parametros['telefono'];
    //$foto = $rutaArchivo;
    $modalidadPago = $parametros['modalidadPago'] ?? 'Efectivo';
    // Crea un nombre de archivo único utilizando el número y tipo de cliente
    $nombreArchivo = $numeroDocumento . $tipoCliente;

    // Define la ruta donde se guardará el archivo
    $rutaArchivo = 'ImagenesDeClientes/2023/' . $nombreArchivo . '.' . $fileFoto->getClientMediaType();

    // Comprueba si la ruta de destino existe
    if (!is_dir(dirname($rutaArchivo))) {
      // Si no existe, intenta crearla
      mkdir(dirname($rutaArchivo), 0777, true);
    }

    // Comprueba si la ruta de destino tiene permisos de escritura
    if (!is_writable(dirname($rutaArchivo))) {
      // Si no tiene permisos de escritura, intenta cambiarlos
      chmod(dirname($rutaArchivo), 0777);
    }

    // Mueve el archivo a la ubicación deseada
    $fileFoto->moveTo($rutaArchivo);

    var_dump($fileFoto);


    // Creamos el producto
    $cliente = new Cliente();
    $cliente->nombre = $nombre;
    $cliente->apellido = $apellido;
    $cliente->mail = $mail;
    $cliente->tipoDocumento = $tipoDocumento;
    $cliente->numeroDocumento = $numeroDocumento;
    $cliente->tipoCliente = $tipoCliente;
    $cliente->pais = $pais;
    $cliente->ciudad = $ciudad;
    $cliente->telefono = $telefono;
    $cliente->foto = $rutaArchivo;
    $cliente->modalidadPago = $modalidadPago;

    $retorno = $cliente->altaCliente();

    if ($retorno != null) {
      $payload = json_encode(array("mensaje" => "cliente creado con exito"));
    }

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    // // Buscamos producto por id
    // $id = $args['id'];
    // //$producto = Cliente::obtenerProducto($id);
    // $payload = json_encode($producto);

    // $response->getBody()->write($payload);
    // return $response->withHeader('Content-Type', 'application/json');


    $parametros = $request->getParsedBody();

    if (isset($parametros['id']) && $parametros['tipoCliente']) {
      $idCliente = $parametros['id'];
      $tipoCliente = $parametros['tipoCliente'];



      // Busca al cliente en el archivo
      $clienteEncontrado = Cliente::obtenerUno($idCliente);



      if ($clienteEncontrado) {
        $response->getBody()->write("País: {$clienteEncontrado->pais}, Ciudad: {$clienteEncontrado->ciudad}, Teléfono: {$clienteEncontrado->telefono}");
      } else {
        $clienteTipoIncorrecto = Cliente::buscarClienteTipoIncorrecto($idCliente, $tipoCliente);
        if ($clienteTipoIncorrecto) {
          $response->getBody()->write("Tipo de cliente incorrecto para el número de cliente: {$idCliente}.");
        } else {
          $response->getBody()->write("No existe la combinación de número y tipo de cliente.");
        }
      }
    } else {
      $response->getBody()->write("Faltan datos");
    }

    return $response;
  }

  public function TraerTodos($request, $response, $args)
  {
    // //$lista = Cliente::obtenerTodos();
    // $payload = json_encode(array("listaProducto" => $lista));

    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
 // Obtén el cuerpo de la solicitud como string
    $body = $request->getBody()->getContents();

    // Analiza el cuerpo de la solicitud (asumiendo que es una cadena JSON)
    $data = json_decode($body, true);

    // Verifica si la decodificación fue exitosa
    if ($data === null) {
        $response->getBody()->write(json_encode(["error" => "Datos no válidos"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Extrae los datos necesarios
    $id = $data['id'];
    $nombre = $data['nombre'];
    $apellido = $data['apellido'];
    $mail = $data['mail'];
    $pais = $data['pais'];
    $ciudad = $data['ciudad'];
    $telefono = $data['telefono'];

    // Crea el objeto Cliente
    $cliente = new Cliente();
    $cliente->id = $id;
    $cliente->nombre = $nombre;
    $cliente->apellido = $apellido;
    $cliente->mail = $mail;
    $cliente->pais = $pais;
    $cliente->ciudad = $ciudad;
    $cliente->telefono = $telefono;

    // Intenta modificar el cliente
    $retorno = Cliente::modificarCliente($cliente);

    // Maneja la respuesta
    if ($retorno !== null) {
        if (!is_numeric($retorno)) {
            $payload = json_encode(["error" => $retorno]);
        } else {
            $payload = json_encode(["mensaje" => "Cliente modificado con éxito"]);
        }
    } else {
        $payload = json_encode(["error" => "No se pudo modificar el cliente"]);
    }

    $response->getBody()->write($payload);

    return $response->withHeader('Content-Type', 'application/json');



  }

  public function BorrarUno($request, $response, $args)
  {
    $parametros = $request->getQueryParams();

    $tipoCliente = $parametros['tipoCliente'];
    $idCliente = $parametros['numeroCliente'];

    $retorno = Cliente::eliminarCliente($idCliente, $tipoCliente);

    if($retorno != null)
            {
              if (!is_numeric($retorno)) {
                $payload = json_encode(["error" => $retorno]);
            } else {
                $payload = json_encode(["mensaje" => "Cliente eliminado con éxito"]);
            }
                // $origen = "ImagenesDeClientes/2023/{$idCliente}{$tipoCliente}.jpg";
                // $destino = "ImagenesBackupClientes/2023/{$idCliente}{$tipoCliente}.jpg";

                // $carpetaDestino = "ImagenesBackupClientes/2023";
                // if (!file_exists($carpetaDestino)) {
                //     mkdir($carpetaDestino, 0777, true);
                // }

                // if (rename($origen, $destino)) 
                // {
                //     //echo "Imagen movida correctamente";
                //     json_encode(["mensaje" => "Imagen movida correctamente"]);
                // } 
                // else 
                // {
                //     //echo "Error al mover la imagen del cliente";
                //     $payload = json_encode(["error" =>"Error al mover la imagen del cliente"]);
                // }

            }
            else
            {
                //echo "No se pudo eliminar al cliente";
                $payload = json_encode(["error" =>"No se pudo eliminar al cliente"]);
            }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
  }



}
