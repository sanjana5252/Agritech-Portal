<?php
include ('csession.php');
include ('../sql.php');

ini_set('memory_limit', '-1');

if (!isset($_SESSION['customer_login_user'])) {
    header("location: ../index.php"); // Redirecting To Home Page
}

$query4 = "SELECT * FROM custlogin WHERE email='$user_check'";
$ses_sq4 = mysqli_query($conn, $query4);
$row4 = mysqli_fetch_assoc($ses_sq4);
$para1 = $row4['cust_id'];
$para2 = $row4['cust_name'];

// Fetch crop data
$cropNames = [];
$quantities = [];

$sql = "SELECT crop, quantity FROM production_approx WHERE quantity > 0";
$query = mysqli_query($conn, $sql);

while ($res = mysqli_fetch_array($query)) {
    $cropNames[] = $res['crop'];
    $quantities[] = $res['quantity'];
}
?>

<!DOCTYPE html>
<html>
<?php include ('cheader.php'); ?>

<body class="bg-white" id="top">

<?php include ('cnav.php'); ?>

<section class="section section-shaped section-lg">
    <div class="shape shape-style-1 shape-primary">
        <span></span><span></span><span></span><span></span><span></span>
        <span></span><span></span><span></span><span></span><span></span>
    </div>

    <div class="container">

        <!-- Page Heading -->
        <div class="row">
            <div class="col-md-8 mx-auto text-center">
                <span class="badge badge-danger badge-pill mb-3">Crops</span>
            </div>
        </div>

        <!-- Crop Table -->
        <div class="row row-content">
            <div class="col-md-12 mb-3">
                <div class="card text-white bg-gradient-warning mb-3">
                    <div class="card-header">
                        <span class="text-warning display-4"> Crop Availability </span>
                    </div>
                    <div class="card-body text-white">
                        <table class="table table-striped table-hover table-bordered bg-gradient-white text-center display" id="myTable">
                            <thead>
                                <tr class="font-weight-bold text-default">
                                    <th><center>Crop Name</center></th>
                                    <th><center>Quantity (in KG)</center></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = mysqli_query($conn, $sql); // Re-run query
                                while ($res = mysqli_fetch_array($query)) {
                                    echo "<tr class='text-center'>";
                                    echo "<td>{$res['crop']}</td>";
                                    echo "<td>{$res['quantity']}</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie Chart Visualizer -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">Crop Distribution Visualizer (Pie Chart)</h4>
                    </div>
                    <div class="card-body text-center">
                        <div style="max-width: 450px; margin: 0 auto;">
                            <canvas id="cropPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php require("footer.php"); ?>

<!-- JavaScript includes -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function () {
        $('#myTable').DataTable();
    });

    // Pie Chart
    const ctx = document.getElementById('cropPieChart').getContext('2d');
    const cropPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($cropNames); ?>,
            datasets: [{
                label: 'Crop Quantity',
                data: <?php echo json_encode($quantities); ?>,
                backgroundColor: [
                    '#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6',
                    '#1abc9c', '#e67e22', '#34495e', '#95a5a6', '#d35400'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                },
                title: {
                    display: true,
                    text: 'Proportion of Crops by Quantity',
                    font: {
                        size: 18
                    }
                }
            }
        }
    });
</script>

</body>
</html>
