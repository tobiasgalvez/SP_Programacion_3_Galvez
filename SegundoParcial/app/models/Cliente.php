<?php
class Cliente
{
    public $id;
    public $nombre;
    public $apellido;
    public $mail;
    public $tipoDocumento;
    public $numeroDocumento;
    public $tipoCliente;
    public $pais;
    public $ciudad;
    public $telefono;
    public $foto;
    public $modalidadPago;
    public $activo;

    public function altaCliente()
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO clientes (nombre, apellido, mail, tipoDocumento, numeroDocumento, tipoCliente, pais, ciudad, telefono, foto, modalidadPago ) 
            VALUES (              :nombre, :apellido, :mail, :tipoDocumento, :numeroDocumento, :tipoCliente, :pais, :ciudad, :telefono, :foto, :modalidadPago)");

            $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
            $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
            $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
            $consulta->bindValue(':tipoDocumento', $this->tipoDocumento, PDO::PARAM_STR);
            $consulta->bindValue(':numeroDocumento', $this->numeroDocumento, PDO::PARAM_STR);
            $consulta->bindValue(':tipoCliente', $this->tipoCliente, PDO::PARAM_STR);
            $consulta->bindValue(':pais', $this->pais, PDO::PARAM_STR);
            $consulta->bindValue('ciudad', $this->ciudad, PDO::PARAM_STR);
            $consulta->bindValue(':telefono', $this->telefono, PDO::PARAM_STR);
            $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
            $consulta->bindValue(':modalidadPago', $this->modalidadPago, PDO::PARAM_STR);




            $consulta->execute();

            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return 'Error al crear el alta de cliente: ' . $e->getMessage();
        }
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, apellido, mail, tipoDocumento, numeroDocumento, tipoCliente, pais, ciudad, telefono, foto, modalidadPago, activo 
        FROM clientes WHERE activo != 0");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Cliente');
    }

    public static function obtenerUno($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, apellido, mail, tipoDocumento, numeroDocumento, tipoCliente, pais, ciudad, telefono, foto, modalidadPago, activo
        FROM clientes WHERE id = :id AND activo != 0");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Cliente');
    }



    public static function modificarCliente(Cliente $clienteIngresado)
    {
        $clienteAModificar = self::obtenerUno($clienteIngresado->id);

        if ($clienteAModificar != null) {
            $objAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDato->prepararConsulta("UPDATE clientes 
                        SET nombre = :nombre, apellido = :apellido, 
                         mail = :mail, pais = :pais, ciudad = :ciudad, telefono = :telefono
                        WHERE id = :id");
            $consulta->bindValue(':nombre', $clienteIngresado->nombre, PDO::PARAM_STR);
            $consulta->bindValue(':apellido', $clienteIngresado->apellido, PDO::PARAM_STR);
            $consulta->bindValue(':mail', $clienteIngresado->mail, PDO::PARAM_STR);
            $consulta->bindValue(':pais', $clienteIngresado->pais, PDO::PARAM_STR);
            $consulta->bindValue(':ciudad', $clienteIngresado->ciudad, PDO::PARAM_STR);
            $consulta->bindValue(':telefono', $clienteIngresado->telefono, PDO::PARAM_STR);


            $consulta->bindValue(':id', $clienteIngresado->id, PDO::PARAM_INT);


            if ($consulta->execute()) {
                return "cliente modificado exitosamente";
            } else {
                return "Error al modificar el cliente";
            }
        } else {
            return "No se encontró el cliente a modificar";
        }
    }

    public static function obtenerClientePorIdYTipo($id, $tipo)
    {
        // Carga los datos del archivo
        $data = Cliente::obtenerTodos();

        // Busca al cliente en los datos
        foreach ($data as $item) {
            if ($item->id == $id && $item->tipoCliente == $tipo) {
                // Si encuentra al cliente, retorna los datos
                return $item;
            }
        }

        // Si no encuentra al cliente, retorna null
        return null;
    }



    public static function buscarClienteTipoIncorrecto($idCliente, $tipoCliente)
    {
        $data = Cliente::obtenerTodos();

        if ($data != null) {
            foreach ($data as $item) {
                if ($item->id == $idCliente && $item->tipoCliente != $tipoCliente) {
                    return $item;
                }
            }
        }


        return null;
    }

    public static function eliminarCliente($idCliente, $tipoCliente)
    {
       $clienteEncontrado = Cliente::obtenerClientePorIdYTipo($idCliente, $tipoCliente);

        if ($clienteEncontrado != null) {
            $objAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDato->prepararConsulta("UPDATE clientes 
                        SET activo = 0
                        WHERE id = :id");
            

            $consulta->bindValue(':id', $idCliente, PDO::PARAM_INT);


            if ($consulta->execute()) {
                return "cliente eliminado exitosamente";
            } else {
                return "Error al eliminar el cliente";
            }
        } else {
            return "No se encontró el cliente a eliminar";
        }
    }
}
