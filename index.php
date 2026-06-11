<?php
$integrantes = [
    "Cristian Mlahue",
    "Nelson Espinoza",
    "Boris Carrasco",
    "Matias Guzman"
];

$nombreAplicacion = "Gestor personal de grupos y tareas";

$descripcion = "Esta aplicación permitirá gestionar tareas de un equipo de trabajo, 
permitiendo registrar actividades, asignarlas a integrantes, modificar su estado 
y eliminar tareas que ya no sean necesarias.";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nombreAplicacion; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 40px;
        }

        .contenedor {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }

        h1 {
            color: #2c3e50;
        }

        h2 {
            color: #34495e;
            margin-top: 30px;
        }

        ul {
            line-height: 1.8;
        }

        .crud {
            background-color: #eef3f7;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        strong {
            color: #1f618d;
        }
    </style>
</head>
<body>

    <div class="contenedor">
        <h1><?php echo $nombreAplicacion; ?></h1>

        <h2>Integrantes del grupo</h2>
        <ul>
            <?php foreach ($integrantes as $integrante): ?>
                <li><?php echo $integrante; ?></li>
            <?php endforeach; ?>
        </ul>

        <h2>Descripción de la aplicación</h2>
        <p>
            <?php echo $descripcion; ?>
        </p>

        <h2>Operaciones CRUD de la aplicación</h2>

        <div class="crud">
            <strong>Crear:</strong> Permitir registrar nuevas tareas con título, descripción, fecha y responsable.
        </div>

        <div class="crud">
            <strong>Leer:</strong> Mostrar una lista de todas las tareas registradas en el sistema.
        </div>

        <div class="crud">
            <strong>Actualizar:</strong> Permitir modificar los datos de una tarea, como su estado, descripción o responsable.
        </div>

        <div class="crud">
            <strong>Eliminar:</strong> Permitir borrar tareas que ya fueron completadas o que no sean necesarias.
        </div>
    </div>

</body>
</html>
