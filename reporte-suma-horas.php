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

// Consulta SQL para obtener las horas trabajadas
$sql = "SELECT
    clave_qr,
    fecha_trabajo,
    SUM(horas_trabajadas) as total_horas_trabajadas
FROM (
    SELECT
        clave_qr,
        DATE(fecha) as fecha_trabajo,
        TIMESTAMPDIFF(SECOND,
            MIN(CASE WHEN es_entrada = 1 THEN CONCAT(fecha, ' ', hora) END),
            MAX(CASE WHEN es_entrada = 0 THEN CONCAT(fecha, ' ', hora) END)
        ) / 3600 - 1 as horas_trabajadas  -- Resta 1 hora
    FROM
        asistencia_obra
    GROUP BY
        clave_qr,
        fecha_trabajo
) AS subconsulta
GROUP BY
    clave_qr,
    fecha_trabajo";

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
$sheet->setCellValue('A1', 'Clave QR')
    ->setCellValue('B1', 'Fecha de Trabajo')
    ->setCellValue('C1', 'Total Horas Trabajadas');

// Obtener datos y llenar el Excel
$row = 2;
if ($result->num_rows > 0) {
    while ($row_data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $row_data['clave_qr'])
            ->setCellValue('B' . $row, $row_data['fecha_trabajo'])
            ->setCellValue('C' . $row, $row_data['total_horas_trabajadas']);

        $row++;
    }
}

// Establecer formato para la fecha y hora
$sheet->getStyle('B2:B' . ($row - 1))->getNumberFormat()->setFormatCode('DD/MM/YYYY');

// Guardar el archivo en el servidor
$filePath = 'reporte_horas_trabajadas.xlsx';
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
    <title>Reporte de Horas Trabajadas</title>
    <!-- Agregar enlaces a Bootstrap y Font Awesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Reporte de Horas Trabajadas</h1>

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
                    <th>Clave QR</th>
                    <th>Fecha de Trabajo</th>
                    <th>Total Horas Trabajadas</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $result->data_seek(0); // Asegúrate de volver al inicio del conjunto de resultados
                    while ($row_data = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row_data['clave_qr']}</td>";
                        echo "<td>{$row_data['fecha_trabajo']}</td>";
                        echo "<td>{$row_data['total_horas_trabajadas']}</td>";
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
