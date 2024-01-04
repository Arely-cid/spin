<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Gastos de Obras por Mes y Año</title>

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

  <!--Custom CSS -->
  <style>
    body {
      background-color: #f7f7f7;
    }
    
    .container {
      max-width: 800px;
      background-color: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px #aaa; 
    }
    
    h2 {
      color: #1b396a;
    }
    
    .table {
      background-color: #f2f2f2; 
    }
    
    .table th,
    .table td {
      vertical-align: middle;
    }
  </style>

</head>

<body>

  <div class="container mt-5">

  <h2 class="text-center">
      <svg width="2em" height="2em" viewBox="0 0 16 16" class="bi bi-file-earmark-bar-graph" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <path d="M4 0h5.5v1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h1V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2z"/>
        <path d="M9.5 3V0L14 4.5h-3A1.5 1.5 0 0 1 9.5 3z"/>
        <path fill-rule="evenodd" d="M8 7a.5.5 0 0 1 .5.5V12a.5.5 0 0 1-1 0V7.5A.5.5 0 0 1 8 7z"/>
        <path fill-rule="evenodd" d="M5 10.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
        <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z"/>
        <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z"/>
      </svg>
      Reporte de Gastos de Obras
    </h2>

    <form method="post" action="">
      <div class="form-group row">
        <label for="mes" class="col-sm-2 col-form-label">Mes</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="mes" name="mes" placeholder="MM">
        </div>
      </div>

      <div class="form-group row">
        <label for="ano" class="col-sm-2 col-form-label">Año</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="ano" name="ano" placeholder="YYYY">
        </div>
      </div>

      <div class="form-group row">
        <div class="col-sm-10">
          <button type="submit" class="btn btn-primary" name="generate_report">
            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-file-earmark-bar-graph" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
              <path d="M0 1a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V1zm2 1v10h1V2H2zm2 4a1 1 0 1 1 0 2h7a1 1 0 1 1 0-2H4zm0 3a1 1 0 1 1 0 2h5a1 1 0 1 1 0-2H4zm0 3a1 1 0 1 1 0 2h3a1 1 0 1 1 0-2H4zm10-7h-4V2h4v3zm0 4h-4V6h4v3zm0 4h-4v-3h4v3z"/>
            </svg>
            Generar Reporte
          </button>
          <button type="button" class="btn btn-secondary ml-2" id="cancel_report">
            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-x" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M3.646 3.646a.5.5 0 0 1 .708 0L8 7.293l3.646-3.647a.5.5 0 0 1 .708.708L8.707 8l3.647 3.646a.5.5 0 0 1-.708.708L8 8.707l-3.646 3.647a.5.5 0 0 1-.708-.708L7.293 8 3.646 4.354a.5.5 0 0 1 0-.708z"/>
            </svg>
            Cancelar
          </button>
        </div>
      </div>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["generate_report"])) {
        $mes_a_consultar = $_POST["mes"];
        $ano_a_consultar = $_POST["ano"];

        if (!empty($mes_a_consultar) && !empty($ano_a_consultar)) {
            $servername = "localhost"; 
            $username = "root"; 
            $password = "";
            $dbname = "apis-soluciones-spin"; 
            // Crear conexión
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Verificar conexión
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Consulta SQL
            $sql = "
                SELECT 
                    todas_obras.nombre_obra,
                    COALESCE(SUM(gastos.gasto_total), 0) AS gasto_total
                FROM (
                    SELECT DISTINCT nombre AS nombre_obra FROM obra
                ) todas_obras
                LEFT JOIN (
                    SELECT 
                        o.nombre AS nombre_obra,
                        COALESCE(SUM(ds.cantidad * a.precio), 0) AS gasto_total
                    FROM obra o
                    LEFT JOIN solicitud_epp s ON o.id_obra = s.id_obra
                    LEFT JOIN detalle_solicitud ds ON s.id_solicitud = ds.id_solicitud
                    LEFT JOIN almacen a ON ds.id_equipo = a.id_equipo
                    WHERE ds.id_equipo IS NOT NULL
                        AND s.status = 'entregado'
                        AND MONTH(s.fecha) = '$mes_a_consultar'
                        AND YEAR(s.fecha) = '$ano_a_consultar'
                    GROUP BY o.nombre
                ) gastos ON todas_obras.nombre_obra = gastos.nombre_obra
                GROUP BY todas_obras.nombre_obra
                ORDER BY todas_obras.nombre_obra;
            ";

            $result = $conn->query($sql);

            if (!$result) {
                die("Error en la consulta: " . $conn->error);
            }

            if ($result->num_rows > 0) {
                echo '<div id="report_section">';
                echo '<h3 class="mt-5"><svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-bar-chart-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 5a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5zm5 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm4-6a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1V3zm-5 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg> Reporte de Gastos de Obras para el Mes ' . htmlspecialchars($mes_a_consultar) . ' y Año ' . htmlspecialchars($ano_a_consultar) . '</h3>';
                echo '<table class="table mt-3">';
                echo '<thead><tr><th>Nombre de la Obra</th><th>Gasto Total</th></tr></thead>';
                echo '<tbody>';
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row["nombre_obra"] . '</td>';
                    echo '<td>$' . $row["gasto_total"] . '</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            } else {
                echo '<p class="mt-3">No se encontraron datos para el mes ' . htmlspecialchars($mes_a_consultar) . ' y año ' . htmlspecialchars($ano_a_consultar) . '.</p>';
            }

            $conn->close();
        } else {
            echo '<p class="mt-3">Por favor, complete el formulario con el mes y el año.</p>';
        }
    }
    ?>

  </div>

  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    document.getElementById("cancel_report").addEventListener("click", function() {
      var reportSection = document.getElementById("report_section");
      if (reportSection) {
        reportSection.style.display = "none";
      }
    });
  </script>
</body>

</html>

