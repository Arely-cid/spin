<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Conexión a la base de datos
$hosting = "localhost";
$username = "root";
$password = "";
$bdname = "apis-soluciones-spin";

$conexion = new mysqli($hosting, $username, $password, $bdname);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta SQL para obtener las ENTRADAS y SALIDAS
$sql = "SELECT id_asistencia, comentario_epp, comentario_asistencia, clave_qr, id_administrador, id_obra, es_entrada, turno, porta_epp,
               fecha, 
               CASE 
                   WHEN es_entrada = 1 THEN MIN(hora)
                   WHEN es_entrada = 0 THEN MAX(hora)
               END AS hora,
               CASE 
                   WHEN hora BETWEEN '07:00:00' AND '08:15:00' THEN 'Asistencia'
                   WHEN hora BETWEEN '08:15:01' AND '09:00:00' THEN 'Retardo'
                   WHEN hora BETWEEN '15:00:00' AND '16:15:00' THEN 'Asistencia'
                   WHEN hora BETWEEN '16:15:01' AND '17:00:00' THEN 'Retardo'
                   WHEN hora BETWEEN '20:00:00' AND '20:15:00' THEN 'Asistencia'
                   WHEN hora BETWEEN '20:15:01' AND '20:30:00' THEN 'Retardo'
                   ELSE 'Salidas'
               END as tipo_asistencia,
               CASE WHEN es_entrada = 1 THEN 'Entrada' ELSE 'Salida' END as tipo_entrada,
               CASE WHEN porta_epp = 1 THEN 'Sí' ELSE 'No' END as porta_epp_text
       FROM asistencia_obra 
       WHERE (
             (hora BETWEEN '07:00:00' AND '09:00:00' AND es_entrada = 1)
          OR (hora BETWEEN '15:00:00' AND '17:00:00' AND es_entrada = 1)
          OR (hora BETWEEN '20:00:00' AND '20:30:00' AND es_entrada = 1)
          OR (
              (hora >= '18:00:00' AND hora <= '23:59:59' AND es_entrada = 0)
           OR (hora >= '00:00:00' AND hora <= '06:00:00' AND es_entrada = 0)
           OR (hora >= '06:00:01' AND hora <= '07:00:00' AND es_entrada = 0)
          )
       )
       GROUP BY id_asistencia, comentario_epp, comentario_asistencia, clave_qr, id_administrador, id_obra, fecha, tipo_asistencia, tipo_entrada, porta_epp_text";

$result = $conexion->query($sql);

// Verificar si la consulta fue exitosa
if ($result === false) {
    die("Error en la consulta: " . $conexion->error);
}

// Crear el objeto de Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte Asistencia');

// Definir encabezados
$sheet->setCellValue('A1', 'ID Asistencia')
    ->setCellValue('B1', 'Comentario EPP')
    ->setCellValue('C1', 'Comentario Asistencia')
    ->setCellValue('D1', 'Clave QR')
    ->setCellValue('E1', 'ID Administrador')
    ->setCellValue('F1', 'ID Obra')
    ->setCellValue('G1', 'Es Entrada')
    ->setCellValue('H1', 'Turno')
    ->setCellValue('I1', 'Porta EPP')
    ->setCellValue('J1', 'Fecha')
    ->setCellValue('K1', 'Hora')
    ->setCellValue('L1', 'Tipo Asistencia'); // Eliminada la columna de "Total de Horas"

// Obtener datos y llenar el Excel
$row = 2;
if ($result->num_rows > 0) {
    while ($row_data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $row_data['id_asistencia'])
            ->setCellValue('B' . $row, $row_data['comentario_epp'])
            ->setCellValue('C' . $row, $row_data['comentario_asistencia'])
            ->setCellValue('D' . $row, $row_data['clave_qr'])
            ->setCellValue('E' . $row, $row_data['id_administrador'])
            ->setCellValue('F' . $row, $row_data['id_obra'])
            ->setCellValue('G' . $row, ($row_data['es_entrada'] == 1) ? 'Entrada' : 'Salida')
            ->setCellValue('H' . $row, $row_data['turno'])
            ->setCellValue('I' . $row, ($row_data['porta_epp'] == 1) ? 'Sí' : 'No')
            ->setCellValue('J' . $row, $row_data['fecha'])
            ->setCellValue('K' . $row, $row_data['hora'])
            ->setCellValue('L' . $row, $row_data['tipo_asistencia']); // Columna de tipo_asistencia, sin "Total de Horas"

        $row++;
    }
}

// Establecer formato para la fecha y hora
$sheet->getStyle('J2:J' . ($row - 1))->getNumberFormat()->setFormatCode('DD/MM/YYYY');
$sheet->getStyle('K2:K' . ($row - 1))->getNumberFormat()->setFormatCode('HH:MM:SS');

// Guardar el archivo en el servidor
$filePath = 'reporte_asistencia.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filePath);

// Cerrar la conexión a la base de datos
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencia</title>
    <!-- Agregar enlaces a Bootstrap y Font Awesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Reporte de Asistencia</h1>

        <!-- Agregar un botón para descargar el archivo -->
        <form action="descargar.php" method="post">
            <button type="submit" class="btn btn-primary" formaction="<?php echo $filePath; ?>">
                Descargar Reporte
                <i class="fas fa-download"></i>
            </button>
            <a href="reportes.html" class="btn btn-secondary">--Volver--</a>
        </form>
        <br>

        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Comentario EPP</th>
                    <th>Comentario Asistencia</th>
                    <th>Clave QR</th>
                    <th>ID Administrador</th>
                    <th>Obra</th>
                    <th>Registro</th>
                    <th>Turno</th>
                    <th>Porta EPP</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Tipo Asistencia</th> <!-- Columna de Tipo Asistencia -->
                </tr>
            </thead>
            <tbody>
                <?php
                 if ($result->num_rows > 0) {
                    $result->data_seek(0); // Asegúrate de volver al inicio del conjunto de resultados
                    while ($row_data = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row_data['comentario_epp']}</td>";
                        echo "<td>{$row_data['comentario_asistencia']}</td>";
                        echo "<td>{$row_data['clave_qr']}</td>";
                        echo "<td>{$row_data['id_administrador']}</td>";
                        echo "<td>{$row_data['id_obra']}</td>";
                        echo "<td>" . (($row_data['es_entrada'] == 1) ? 'Entrada' : 'Salida') . "</td>";
                        echo "<td>{$row_data['turno']}</td>";
                        echo "<td>" . (($row_data['porta_epp'] == 1) ? 'Sí' : 'No') . "</td>";
                        echo "<td>{$row_data['fecha']}</td>";
                        echo "<td>{$row_data['hora']}</td>";
                        echo "<td>{$row_data['tipo_asistencia']}</td>"; // Columna de Tipo Asistencia
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Agregar enlaces a Bootstrap y Font Awesome -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
