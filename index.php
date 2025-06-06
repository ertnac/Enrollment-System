<?php
// Start session and include configuration if needed
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack - Student Enrollment</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/lucide@latest/dist/lucide.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php
            // Determine which view to show based on URL parameter
            $view = isset($_GET['view']) ? $_GET['view'] : 'dashboard';

            // Include the appropriate view
            switch ($view) {
                case 'students':
                    include 'includes/students.php';
                    break;
                case 'instructors':
                    include 'includes/instructors.php';
                    break;
                case 'enroll':
                    include 'includes/enroll.php';
                    break;
                case 'programs':
                    include 'includes/programs.php';
                    break;
                case 'pending-enrollees':
                    include 'includes/pending_enrollees.php';
                    break;
                case 'logout':
                    include 'includes/logout.php';
                    break;
                default:
                    include 'includes/dashboard.php';
            }
            ?>
        </main>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="assets/js/script.js"></script>
</body>

</html>