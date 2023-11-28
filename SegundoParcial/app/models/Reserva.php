<?php
class Reserva
{
    public $id;
    public $tipoCliente;
    public $idCliente;
    public $fechaEntrada;
    public $fechaSalida;
    public $tipoHabitacion;
    public $importeTotalReserva;
    public $imagenConfirmacion;
    public $estado;

    public function mostrarDatos()
    {
        return  "<br> Id reserva: " . $this->id . 
                "<br> Id cliente: " . $this->idCliente .
                "<br> Fecha de entrada: " . $this->fechaEntrada . 
                "<br> Fecha de salida: " . $this->fechaSalida . 
                "<br> Tipo de habitacion: " . $this->tipoHabitacion . 
                "<br> Importe total de la reserva: $" . $this->importeTotalReserva;

    }

    public function altaReserva()
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO reservas (tipoCliente, idCliente, fechaEntrada, fechaSalida, tipoHabitacion, importeTotalReserva, imagenConfirmacion) 
            VALUES (                :tipoCliente, :idCliente, :fechaEntrada, :fechaSalida, :tipoHabitacion, :importeTotalReserva, :imagenConfirmacion)");

            $consulta->bindValue(':tipoCliente', $this->tipoCliente, PDO::PARAM_STR);
            $consulta->bindValue(':idCliente', $this->idCliente, PDO::PARAM_STR);
            $consulta->bindValue(':fechaEntrada', $this->fechaEntrada, PDO::PARAM_STR);
            $consulta->bindValue(':fechaSalida', $this->fechaSalida, PDO::PARAM_STR);
            $consulta->bindValue(':tipoHabitacion', $this->tipoHabitacion, PDO::PARAM_STR);
            $consulta->bindValue(':importeTotalReserva', $this->importeTotalReserva, PDO::PARAM_STR);
            $consulta->bindValue(':imagenConfirmacion', $this->imagenConfirmacion, PDO::PARAM_STR);

            $consulta->execute();

            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return 'Error al crear el reserva: ' . $e->getMessage();
        }
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, tipoCliente, idCliente, fechaEntrada, fechaSalida, tipoHabitacion, importeTotalReserva, imagenConfirmacion, estado 
        FROM reservas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Reserva');
    }

    public static function obtenerReserva($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, tipoCliente, idCliente, fechaEntrada, fechaSalida, tipoHabitacion, importeTotalReserva, imagenConfirmacion, estado
        FROM reservas WHERE id = :id AND estado != 0");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Reserva');
    }





    public function existeReserva($idReserva)
    {
        // Carga los datos del archivo
        $data = $this->obtenerTodos();

        if ($data != null) {

            // Busca al reserva en los datos
            foreach ($data as $item) {
                if ($item->id == $idReserva) {
                    // Si encuentra al reserva, retorna true
                    return true;
                }
            }
        }

        // Si no encuentra al reserva, retorna false
        return false;
    }

    public function existeReservaConIdYTipoCliente($idReserva, $idCliente, $tipoCliente)
    {
        $data = $this->obtenerTodos();
        if($this->existeReserva($idReserva))
        {
            foreach($data as $item)
            {
                if($item->id == $idReserva && $item->idCliente == $idCliente && $item->tipoCliente == $tipoCliente)
                {
                    return true;
                }
            }
        }
        return false;
    }




    public function cancelarReservaPorId($idReserva)
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE reservas SET estado = 'Cancelada' WHERE id = :idReserva");

  
            $consulta->bindValue(':idReserva', $idReserva, PDO::PARAM_STR);

            $consulta->execute();

            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return 'Error al modifcar el estado de la reserva a Cancelada: ' . $e->getMessage();
        }

    }


    public function verificarSiReservaEstaCancelada($idReserva)
    {
        $datos = $this->obtenerTodos();

        foreach ($datos as &$item)
        {
            if($item->id == $idReserva)
            {
                if($item->estado == 'Cancelada')
                {
                    return true;                
                }
            }
        }

        return false;

    }




    // public function existeClienteEnHotelIdYTipo($idCliente, $tipoCliente) 
    // {
    //     // Carga los datos del archivo
    //     $data = $this->obtenerClientes();

    //     // Busca al cliente en los datos
    //     foreach ($data as $item) {
    //         if ($idCliente == $item['id'] && $tipoCliente == $item['tipoCliente']) 
    //         {
    //             // Si encuentra al cliente, retorna true
    //             return true;
    //         }
    //     }

    //     // Si no encuentra al cliente, retorna false
    //     return false;
    // }



    public function totalReservasPorFechaYTipoHabitacion($fechaEntrada, $tipoHabitacion)
    {
        $datos = $this->obtenerTodos();
        $total = 0;

        foreach ($datos as $reserva) {
            if ($reserva->fechaEntrada == $fechaEntrada && $reserva->tipoHabitacion == $tipoHabitacion) {
                $total += $reserva->importeTotalReserva;
            }
        }

        return $total;
    }

    public function reservasPorCliente($idCliente, $tipoCliente)
    {
        $datos = $this->obtenerTodos();
        $reservas = array();

        foreach ($datos as $reserva) {
            //var_dump($reserva->tipoHabitacion);
            if ($reserva->idCliente == $idCliente && $reserva->tipoCliente == $tipoCliente) {
                $objReserva = new Reserva();
                $objReserva->tipoCliente = $reserva->tipoCliente;
                $objReserva->idCliente =   $reserva->idCliente;
                $objReserva->fechaEntrada = $reserva->fechaEntrada;
                $objReserva->fechaSalida =  $reserva->fechaSalida;
                $objReserva->tipoHabitacion =  $reserva->tipoHabitacion;
                $objReserva->importeTotalReserva = $reserva->importeTotalReserva;
                $objReserva->imagenConfirmacion =  $reserva->imagenConfirmacion;
                $objReserva->id = $reserva->id;
                $objReserva->estado = $reserva->estado;
                $reservas[] = $objReserva;
            }
        }

        return $reservas;
    }

    public function reservasEntreFechas($fecha1, $fecha2)
    {
        $datos = $this->obtenerTodos();
        $reservas = array();

        $fecha1 = new DateTime($fecha1);
       $fecha2 = new DateTime($fecha2);
       
       foreach ($datos as $reserva) {
            $fechaEntrada = new DateTime($reserva->fechaEntrada);
            // var_dump($fechaEntrada);
            // var_dump($fecha1);
            if ($fechaEntrada >= $fecha1 && $fechaEntrada <= $fecha2) {
                // echo "entre al iffff";
                $objReserva = new Reserva();
                $objReserva->tipoCliente = $reserva->tipoCliente;
                $objReserva->idCliente =   $reserva->idCliente;
                $objReserva->fechaEntrada = $reserva->fechaEntrada;
                $objReserva->fechaSalida =  $reserva->fechaSalida;
                $objReserva->tipoHabitacion =  $reserva->tipoHabitacion;
                $objReserva->importeTotalReserva = $reserva->importeTotalReserva;
                $objReserva->imagenConfirmacion =  $reserva->imagenConfirmacion;
                $objReserva->id = $reserva->id;
                $objReserva->estado = $reserva->estado;
                $reservas[] = $objReserva;
            }
        }

        return $reservas;
    }

    public function reservasPorTipoHabitacion($tipoHabitacion)
    {
        $datos = $this->obtenerTodos();
        $reservas = array();

        foreach ($datos as $reserva) {
            if ($reserva->tipoHabitacion == $tipoHabitacion) {
                $objReserva = new Reserva();
                $objReserva->tipoCliente = $reserva->tipoCliente;
                $objReserva->idCliente =   $reserva->idCliente;
                $objReserva->fechaEntrada = $reserva->fechaEntrada;
                $objReserva->fechaSalida =  $reserva->fechaSalida;
                $objReserva->tipoHabitacion =  $reserva->tipoHabitacion;
                $objReserva->importeTotalReserva = $reserva->importeTotalReserva;
                $objReserva->imagenConfirmacion =  $reserva->imagenConfirmacion;
                $objReserva->id = $reserva->id;
                $objReserva->estado = $reserva->estado;
                $reservas[] = $objReserva;
            }
        }

        return $reservas;
    }




    public function cancelacionesTipoCliente($tipoCliente)
    {
        $datos = $this->obtenerTodos();
        $reservas = array();

        foreach ($datos as $reserva) {
            if ($reserva->tipoCliente == $tipoCliente) {
               $objReserva = new Reserva();
                $objReserva->tipoCliente = $reserva->tipoCliente;
                $objReserva->idCliente =   $reserva->idCliente;
                $objReserva->fechaEntrada = $reserva->fechaEntrada;
                $objReserva->fechaSalida =  $reserva->fechaSalida;
                $objReserva->tipoHabitacion =  $reserva->tipoHabitacion;
                $objReserva->importeTotalReserva = $reserva->importeTotalReserva;
                $objReserva->imagenConfirmacion =  $reserva->imagenConfirmacion;
                $objReserva->id = $reserva->id;
                $objReserva->estado = $reserva->estado;
                $reservas[] = $objReserva;
            }
        }

        return $reservas;
    }




    public function cancelacionesPorUsuario($idCliente)
    {
        $datos = $this->obtenerTodos();
        $reservas = array();

        foreach ($datos as $reserva) {
            if ($reserva->idCliente == $idCliente && $reserva->estado == "Cancelada") {
                $objReserva = new Reserva();
                $objReserva->tipoCliente = $reserva->tipoCliente;
                $objReserva->idCliente =   $reserva->idCliente;
                $objReserva->fechaEntrada = $reserva->fechaEntrada;
                $objReserva->fechaSalida =  $reserva->fechaSalida;
                $objReserva->tipoHabitacion =  $reserva->tipoHabitacion;
                $objReserva->importeTotalReserva = $reserva->importeTotalReserva;
                $objReserva->imagenConfirmacion =  $reserva->imagenConfirmacion;
                $objReserva->id = $reserva->id;
                $objReserva->estado = $reserva->estado;
                $reservas[] = $objReserva;
            }
        }

        return $reservas;
    }

    public function reservasPorUsuario($idCliente)
    {
        $datos = $this->obtenerTodos();
        $reservas = array();

        foreach ($datos as $reserva) {
            if ($reserva->idCliente == $idCliente) {
                $objReserva = new Reserva();
                $objReserva->tipoCliente = $reserva->tipoCliente;
                $objReserva->idCliente =   $reserva->idCliente;
                $objReserva->fechaEntrada = $reserva->fechaEntrada;
                $objReserva->fechaSalida =  $reserva->fechaSalida;
                $objReserva->tipoHabitacion =  $reserva->tipoHabitacion;
                $objReserva->importeTotalReserva = $reserva->importeTotalReserva;
                $objReserva->imagenConfirmacion =  $reserva->imagenConfirmacion;
                $objReserva->id = $reserva->id;
                $objReserva->estado = $reserva->estado;
                $reservas[] = $objReserva;
            }
        }

        return $reservas;
    }





    public static function modificarProducto(Cliente $productoIngresado)
    {
        // $productoAModificar = self::obtenerProducto($productoIngresado->id);

        // if ($productoAModificar != null) {
        //     $objAccesoDato = AccesoDatos::obtenerInstancia();
        //     $consulta = $objAccesoDato->prepararConsulta("UPDATE productos 
        //         SET nombre = :nombre, tipo = :tipo, precio = :precio, descripcion = :descripcion
        //         WHERE id = :id");
        //     $consulta->bindValue(':nombre', $productoIngresado->nombre, PDO::PARAM_STR);
        //     $consulta->bindValue(':tipo', $productoIngresado->tipo, PDO::PARAM_STR);
        //     $consulta->bindValue(':precio', $productoIngresado->precio, PDO::PARAM_STR);
        //     $consulta->bindValue(':descripcion', $productoIngresado->descripcion, PDO::PARAM_STR);
        //     $consulta->bindValue(':id', $productoIngresado->id, PDO::PARAM_INT);

        //     if ($consulta->execute()) 
        //     {
        //         return "Producto modificado exitosamente";
        //     } 
        //     else 
        //     {
        //         return "Error al modificar el producto";
        //     }
        // } 
        // else 
        // {
        //     return "No se encontró el producto a modificar";
        // }
    }

    public static function borrarProducto($id)
    {
        // $idProductoAEliminar = self::obtenerReserva($id);
        // if ($idProductoAEliminar != null) {
        //     $objAccesoDato = AccesoDatos::obtenerInstancia();
        //     $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET activo = 0 WHERE id = :id");
        //     $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        //     if ($consulta->execute()) {
        //         return "Producto eliminado exitosamente";
        //     } else {
        //         return "Error al eliminar el producto";
        //     }
        // } else {
        //     return "No se encontró un producto con id '{$id}'";
        // }
    }



    public static function agregarAjuste($idReserva, $ajusteImporte, $motivoAjuste)
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ajustes (idReserva, nuevoImporte, motivo) 
                                                        VALUES (                :idReserva, :nuevoImporte, :motivo)");
  
            $consulta->bindValue(':idReserva', $idReserva, PDO::PARAM_STR);
            $consulta->bindValue(':nuevoImporte', $ajusteImporte, PDO::PARAM_STR);
            $consulta->bindValue(':motivo', $motivoAjuste, PDO::PARAM_STR);

            $consulta->execute();

            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return 'Error al agregar ajuste: ' . $e->getMessage();
        }
    }


    public function actualizarEstadoReservaYMontoPorId($idReserva, $ajusteImporte)
    {
        

        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE reservas 
            SET importeTotalReserva = :importeTotalReserva, estado = :estado
            WHERE id = :id");
        $consulta->bindValue(':importeTotalReserva', $ajusteImporte, PDO::PARAM_STR);
        $consulta->bindValue(':estado', "Ajustada", PDO::PARAM_STR);
        $consulta->bindValue(':id', $idReserva, PDO::PARAM_INT);
        

        

        if ($consulta->execute()) 
        {
            return "reserva ajustada exitosamente";
        } 
        else 
        {
            return "Error al ajustar reserva";
        }
    
    }





}
