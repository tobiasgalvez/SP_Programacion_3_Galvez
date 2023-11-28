<?php

class Validaciones {





    public static function validarCliente($nombre, $apellido, $tipoDocumento, $numeroDocumento, $tipoCliente, $pais, $ciudad, $telefono)
    {
        $errores = array();

        if (strlen($nombre) < 3 || strlen($apellido) < 3 || is_numeric($nombre) || is_numeric($apellido)) {
            $errores[] = "El nombre y el apellido deben tener al menos 3 letras y no pueden contener numeros";
        }

        $documentosValidos = array("DNI", "LE", "LC", "PASAPORTE");
        if (!in_array($tipoDocumento, $documentosValidos)) {
            $errores[] = "El tipo de documento no es válido. Debe ser 'DNI', 'LE', 'LC' o 'PASAPORTE'";
        }

        if (!is_numeric($numeroDocumento)) {
            $errores[] = "El número de documento debe ser numérico.";
        }

        $tiposClientesValidos = array("Individual", "Corporativo");
        if (!in_array($tipoCliente, $tiposClientesValidos)) {
            $errores[] = "El tipo de cliente no es válido. Debe ser 'Individual' o 'Corporativo'";
        }

        if (strlen($pais) < 3 || strlen($ciudad) < 3) {
            $errores[] = "El país y la ciudad deben tener al menos 3 letras.";
        }

        if (!is_numeric($telefono)) {
            $errores[] = "El teléfono debe ser numérico.";
        }

        return $errores;
    }

    public static function validarFoto($foto, $nombreArchivo)
    {
        $errores = array();

        if ($foto != null) {
            $extensionFoto = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
            if (!in_array($extensionFoto, array("jpg", "jpeg"))) {
                $errores[] = "La imagen debe tener una extensión .jpg o .jpeg.";
            }
        }

        return $errores;
    }


    public static function validarReserva($idCliente, $tipoHabitacion, $importeTotalReserva, $fechaEntrada, $fechaSalida)
    {
        $errores = array();

        // Validación del tipo de habitacion
        $errores[] = Validaciones::validarTipoHabitacion($tipoHabitacion);

        // Validación del importe
        if (!is_numeric($importeTotalReserva)) {
            $errores[] = "El importe total de la reserva debe contener numeros.";
        }

        if (!Validaciones::validarFecha($fechaEntrada) || !Validaciones::validarFecha($fechaSalida)) {
            $errores[] = "La fecha de entrada y/o salida no tiene/n el formato correcto (aaaa-mm-dd)";
        }

        // if($idCliente )

        // if($reserva->fechaEntrada > $reserva->fechaSalida)
        // {
        //     $errores[] = "La fecha de entrada debe ser por lo menos un dia anterior a la fecha de salida";
        // }

        // Si todas las validaciones pasan, el cliente es válido
        
            return $errores;
    }

    public static function validarFecha($fecha, $formato = 'Y-m-d')
    {

        $d = new DateTime($fecha);
        // La comparación estricta (===) se utiliza para asegurar que la fecha sea exactamente igual a la fecha formateada
        return $d && $d->format($formato) === $fecha;
    }


    public static function validarTipoHabitacion($tipoHabitacion)
    {
        $habitacionesValidas = array("Simple", "Doble", "Suite");
        if (!in_array($tipoHabitacion, $habitacionesValidas)) {
            return "El tipo de habitacion debe ser 'Simple', 'Doble' o 'Suite'";
        }
    }






}