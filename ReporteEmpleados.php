<?php
header('Content-Type: text/html; charset=utf-8');

require 'vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apis-soluciones-spin";

// Crear la conexión a la base de datos 
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Establecer codificación de caracteres UTF-8
$conn->set_charset("utf8");

$results = [];

// Nueva consulta SQL  
$sql = "SELECT 
CONCAT(empleado.nombre, ' ', empleado.apell_pat, ' ', empleado.apell_mat) AS nombre_empleado,
solicitud_epp.id_administrador AS id_administrador_entrego,
almacen.nombre_equipo AS nombre_equipo_entregado,
obra.nombre AS nombre_obra_entregada,
entrega_epp.fecha AS fecha
FROM 
entrega_epp
JOIN 
empleado ON entrega_epp.clave_qr = empleado.clave_qr
JOIN 
solicitud_epp ON entrega_epp.id_solicitud = solicitud_epp.id_solicitud
JOIN 
obra ON solicitud_epp.id_obra = obra.id_obra
JOIN
almacen ON entrega_epp.id_equipo = almacen.id_equipo
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Almacenar los resultados en un array
    $results = $result->fetch_all(MYSQLI_ASSOC); 
}

// Cerrar la conexión
$conn->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['descargar'])) {

    // Generar archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Agregar encabezados 
    $sheet->setCellValue('A1', 'Nombre Empleado');
    $sheet->setCellValue('B1', 'ID Administrador'); 
    $sheet->setCellValue('C1', 'Nombre Equipo');
    $sheet->setCellValue('D1', 'Nombre Obra'); 
    $sheet->setCellValue('E1', 'Fecha Entrega');

    // Llenar la hoja con los datos
    $row = 2;
    foreach($results as $row_data) {
        $sheet->setCellValue('A' . $row, $row_data['nombre_empleado']);
        $sheet->setCellValue('B' . $row, $row_data['id_administrador_entrego']);
        $sheet->setCellValue('C' . $row, $row_data['nombre_equipo_entregado']); 
        $sheet->setCellValue('D' . $row, $row_data['nombre_obra_entregada']);
        $sheet->setCellValue('E' . $row, $row_data['fecha']);
        $row++;
    }

    // Configurar cabeceras para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename=reporte_entregas_epp.xlsx'); 
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reporte de Entregas de EPP</title>

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> 
  <!-- Font Awesome -->
  <script src="https://kit.fontawesome.com/f90d3bf50d.js" crossorigin="anonymous"></script>
  <style>
    body {
      background: #f5f5f5;
      color: #333;
    }
    .report-header {
      background: #FFFFFF;
      color: #000000;
      padding: 1rem;
      text-align: center;
    }
    .container {
      margin-top: 50px;
    }
    .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
    }
    .btn-primary:hover {
      background-color: #0056b3;
      border-color: #0056b3;
    }
    .table thead th {
      background-color: #FF8C00;
      color: #000000;
    }
  </style>
</head>
<body>

  <div class="report-header">
    <!-- Agrega la etiqueta img para el logo a la izquierda del título -->
    <img src="LOGO-spin.png" alt="LOGO-spin" style="width: 200px; height: 100px; float: left; margin-right: 20px;">
    <h1 class="display-4"><i class="fas fa-clipboard-list"></i> Reporte de Entregas EPP</h1>
    <p class="lead mb-0">Detalles de entregas de equipo de protección personal</p>
  </div>

  <div class="container my-5">

    <div class="text-right mb-3">
      <form method="post">
        <button class="btn btn-primary btn-lg" name="descargar">
          <i class="fas fa-file-excel"></i> Descargar Excel  
        </button>
        <a href="reportes.html" class="btn btn-secondary btn-lg">--Volver--</a>
        
      </form>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="bg-primary text-white">
          <tr>
            <th>Nombre Empleado</th>
            <th>ID Administrador</th>
            <th>Nombre Equipo</th>
            <th>Nombre Obra</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($results as $row): ?>
            <tr>
              <td><?php echo $row['nombre_empleado']; ?></td>
              <td><?php echo $row['id_administrador_entrego']; ?></td>
              <td><?php echo $row['nombre_equipo_entregado']; ?></td>
              <td><?php echo $row['nombre_obra_entregada']; ?></td>
              <td><?php echo $row['fecha']; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>

  <!-- jQuery y Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>
