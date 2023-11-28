<?php
require_once './models/Reserva.php';
require_once './interfaces/IApiUsable.php';
require_once './models/Validaciones.php';

class ReservaController extends Reserva implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {

    //echo "hola";
    $parametros = $request->getParsedBody();
    //var_dump($parametros);

    $fileFotoConfirmacion = $request->getUploadedFiles()['imagenConfirmacion'];

    $tipoCliente = $parametros['tipoCliente'];
    $idCliente = $parametros['idCliente'];
    $fechaEntrada = DateTime::createFromFormat('Y-m-d', $parametros['fechaEntrada'])->format('Y-m-d');
    $fechaSalida = DateTime::createFromFormat('Y-m-d', $parametros['fechaSalida'])->format('Y-m-d');
    $tipoHabitacion = $parametros['tipoHabitacion'];
    $importeTotalReserva = $parametros['importeTotal'];

    var_dump($fechaEntrada);
    var_dump($fechaSalida);


    $reserva = new Reserva();
    $reserva->tipoCliente = $tipoCliente;
    $reserva->idCliente = $idCliente;
    $reserva->fechaEntrada = $fechaEntrada;
    $reserva->fechaSalida = $fechaSalida;
    $reserva->tipoHabitacion = $tipoHabitacion;
    $reserva->importeTotalReserva = $importeTotalReserva;



    $nombreArchivo = $tipoCliente . $idCliente . $reserva->id;

    // Define la ruta donde se guardará el archivo
    $rutaArchivo = '/ImagenesDeReservas2023' . $nombreArchivo . '.' . $fileFotoConfirmacion->getClientMediaType();

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
    $fileFotoConfirmacion->moveTo($rutaArchivo);

    $reserva->imagenConfirmacion = $rutaArchivo;



    $retorno = $reserva->altaReserva();

    echo "hola cheeeeeeeeeeeee";

    if ($retorno != null) {
      if (!is_numeric($retorno)) {
        $payload = json_encode(array("error" => $retorno));
      } else {
        $payload = json_encode(array("mensaje" => "reserva creado con exito"));
      }
    }

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {


    $parametros = $request->getQueryParams();

    if (isset($parametros['fechaEntrada']) && isset($parametros['tipoHabitacion'])) {
      // Si se pasa una fechaEntrada, muestra el total de reservas para esa fechaEntrada
      $fechaEntrada = $parametros['fechaEntrada'];
      $tipoHabitacion = $parametros['tipoHabitacion'];
      $resultado = Validaciones::validarTipoHabitacion($tipoHabitacion);
      if ($resultado != null) {
        //echo  $resultado;
        $response->getBody()->write($resultado);
      } else if (!Validaciones::validarFecha($fechaEntrada)) {
        //echo "<br>La fecha de entrada no tiene el formato correcto (aaaa-mm-dd)";
        $response->getBody()->write("<br>La fecha de entrada no tiene el formato correcto (aaaa-mm-dd)");
      } else {
        $total = Reserva::totalReservasPorFechaYTipoHabitacion($fechaEntrada, $tipoHabitacion);
        //echo "Total de reservas (importe) para tipo de habitacion {$tipoHabitacion} y fecha de entrada {$fechaEntrada}: $ {$total}";
        $response->getBody()->write("Total de reservas (importe) para tipo de habitacion {$tipoHabitacion} y fecha de entrada {$fechaEntrada}: $ {$total}");
      }
    } else if (isset($parametros['numeroCliente']) && isset($parametros['tipoCliente'])) {
      // Si se pasa un número y tipo de cliente, muestra las reservas para ese cliente
      $numeroCliente = $parametros['numeroCliente'];
      $tipoCliente = $parametros['tipoCliente'];
      $reservas = Reserva::reservasPorCliente($numeroCliente, $tipoCliente);
      //echo "Reservas para el cliente de id:{$numeroCliente} y tipo:{$tipoCliente}: ";
      $response->getBody()->write("Reservas para el cliente de id:{$numeroCliente} y tipo:{$tipoCliente}: ");


      if (count($reservas) > 0) {
        foreach ($reservas as $item) {
          //$item->mostrarDatos();
          $response->getBody()->write($item->mostrarDatos());
        }
      } else {
        //echo "No hay";
        $response->getBody()->write("No hay");
      }
    } elseif (isset($parametros['fecha1']) && isset($parametros['fecha2'])) {
      // Si se pasan dos fechas, muestra las reservas entre esas fechas
      $fecha1 = $parametros['fecha1'];
      $fecha2 = $parametros['fecha2'];

      if (!Validaciones::validarFecha($fecha1) || !Validaciones::validarFecha($fecha2)) {
        //echo "La fecha de entrada y/o salida no tiene/n el formato correcto (dd-mm-aaaa)";
        $response->getBody()->write("La fecha de entrada y/o salida no tiene/n el formato correcto (aaaa-mm-dd)");
      } else {
        $reservas = Reserva::reservasEntreFechas($fecha1, $fecha2);
        //echo "Reservas entre las fechas {$fecha1} y {$fecha2}: ";
        $response->getBody()->write("Reservas entre las fechas {$fecha1} y {$fecha2}: ");


        if (count($reservas) > 0) {
          foreach ($reservas as $item) {
            //echo $item->mostrarDatos();
            // echo "mostrando datosss......";
            $response->getBody()->write($item->mostrarDatos());
          }
        } else {
          //echo "No hay";
          $response->getBody()->write("No hay");
        }
      }
    } else if (isset($parametros['tipoHabitacion'])) {
      // Si se pasa un tipo de habitación, muestra las reservas para ese tipo de habitación
      $tipoHabitacion = $parametros['tipoHabitacion'];
      $resultado = Validaciones::validarTipoHabitacion($tipoHabitacion);
      if ($resultado != null) {
        //echo $resultado;
        $response->getBody()->write($resultado);
      } else {
        $reservas = Reserva::reservasPorTipoHabitacion($tipoHabitacion);
        //echo "Reservas para el tipo de habitación {$tipoHabitacion}: ";
        $response->getBody()->write("Reservas para el tipo de habitación {$tipoHabitacion}: ");

        if (count($reservas) > 0) {
          foreach ($reservas as $item) {
            // echo '<br /><br />';
            //cho $item->mostrarDatos();
            //echo "mostrando datosss......";
            $response->getBody()->write($item->mostrarDatos());
          }
        } else {
          //echo "No hay";
          $response->getBody()->write("no hay");
        }
      }
    } else if (isset($parametros['tipoCliente'])) {
      $tipoCliente = $parametros['tipoCliente'];
      $cancelaciones = Reserva::cancelacionesTipoCliente($tipoCliente);

      //echo "Reservas canceladas para el tipo de cliente {$tipoCliente}: ";
      $response->getBody()->write("Reservas canceladas para el tipo de cliente {$tipoCliente}: ");
      if (count($cancelaciones) > 0) {
        foreach ($cancelaciones as $item) {
          //echo '<br /><br />';
          //  echo $item->mostrarDatos();
          // echo "mostrando datos.....";
          $response->getBody()->write($item->mostrarDatos());
        }
      } else {
        //echo "No hay reservas canceladas por tipo de cliente '{$tipoCliente}'";
        $response->getBody()->write("No hay reservas canceladas por tipo de cliente '{$tipoCliente}'");
      }
    } else if (isset($parametros['idCliente'])) {
      $idCliente = $parametros['idCliente'];
      $reservasUsuario = Reserva::reservasPorUsuario($idCliente);
      $cancelacionesUsuario = Reserva::cancelacionesPorUsuario($idCliente);

      //echo "*********************************RESERVAS DE USUARIO {$idCliente}************************";
      if (count($reservasUsuario) > 0) {
        foreach ($reservasUsuario as $item) {
          //echo '<br /><br />';
          //echo $item->mostrarDatos();
          //echo "mostrar datos pa.......";
          $response->getBody()->write($item->mostrarDatos());
        }
      } else {
        // echo "No hay";
      }

      //echo "************************************CANCELACIONES DE USUARIO {$idCliente}******************";
      if (count($cancelacionesUsuario) > 0) {
        foreach ($cancelacionesUsuario as $item) {
          //echo '<br /><br />';
          //echo $item->mostrarDatos();
          // echo "mostrar datos pa.......";
          $response->getBody()->write($item->mostrarDatos());
        }
      } else {
        //echo "No hay";
        $response->getBody()->write("No hay");
      }
    } else {
      //echo "Consulta no válida";
      $response->getBody()->write("Consulta no válida");
    }
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  }

  public function TraerTodos($request, $response, $args)
  {
    // $lista = Usuario::obtenerTodos();
    // $payload = json_encode(array("listaUsuario" => $lista));

    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
  }

  public function BorrarUno($request, $response, $args)
  {
    //echo "hola";
    $parametros = $request->getParsedBody();
    //var_dump($parametros);

    $idReserva = $parametros['id'];
    $idCliente = $parametros['idCliente'];
    $tipoCliente = $parametros['tipoCliente'];




    if (Reserva::existeReservaConIdYTipoCliente($idReserva, $idCliente, $tipoCliente)) {
      if (!Reserva::verificarSiReservaEstaCancelada($idReserva)) {
        $retorno = Reserva::cancelarReservaPorId($idReserva);
        if ($retorno != null) {
          if (!is_numeric($retorno)) {
            $payload = json_encode(array("error" => $retorno));
          } else {
            $payload = json_encode(array("mensaje" => "reserva cancelada con exito"));
          }
        }
      } else {
        //echo "Error, la reserva ya se encuentra cancelada";
        $payload = json_encode(array("error" => "Error, la reserva ya se encuentra cancelada"));
      }
    } else {
      // echo "El cliente de ID: " . $idCliente ." no contiene dicho numero de reserva ({$idReserva})";
      $payload = json_encode(array("error" => "El cliente de ID: " . $idCliente . " no contiene dicho numero de reserva ({$idReserva})"));
    }

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }



  public function AjustarUno($request, $response, $args)
  {
    //echo "hola";
    $parametros = $request->getParsedBody();
    //var_dump($parametros);

    $idReserva = $parametros['idReserva'];
    $ajusteImporte = $parametros['ajusteImporte'];
    $motivoAjuste = $parametros['motivoAjuste'];


    if (Reserva::existeReserva($idReserva)) {
      if (!Reserva::verificarSiReservaEstaCancelada($idReserva)) {
        $retorno = Reserva::agregarAjuste($idReserva, $ajusteImporte, $motivoAjuste);
        if ($retorno != null) {
          if (!is_numeric($retorno)) {
            $payload = json_encode(array("error" => $retorno));
          } else {
            $payload = json_encode(array("mensaje" => "ajuste agregado con exito"));
            $retornoActualizar = Reserva::actualizarEstadoReservaYMontoPorId($idReserva, $ajusteImporte);

            if ($retornoActualizar != null) {
              if (!is_numeric($retornoActualizar)) {
                $payload = json_encode(["error" => $retornoActualizar]);
            } else {
                $payload = json_encode(["mensaje" => "<br>Estado de id de reserva " . $idReserva . " modificado a 'Ajustada' con éxito!"]);
            }
            }
          }
        }
      } else {
        //echo "Error, la reserva está cancelada, no se puede realizar el ajuste";
        $payload = json_encode(["error" => "Error, la reserva está cancelada, no se puede realizar el ajuste"]);

      }
    } else {
      //echo "Error, la reserva no existe!";
      $payload = json_encode(["error" => "Error, la reserva no existe!"]);

    }


    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }
}
