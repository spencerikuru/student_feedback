<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>University Feedback Portal</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            height: 100vh;
            margin: 0;
            color: white;
            overflow: hidden;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .center-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 600px;
            max-width: 90%;
        }

        .title {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 1.1rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .btn-custom {
            width: 220px;
            padding: 14px;
            font-size: 1.1rem;
            border-radius: 10px;
            transition: 0.3s;
            margin: 10px;
        }

        .btn-custom:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }

        .footer {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            opacity: 0.7;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

    <div class="center-box">

        <h1 class="title">
            <i class="bi bi-mortarboard-fill"></i>
            University Feedback Portal
        </h1>

        <p class="subtitle">
            A simple, modern, and secure platform for students to share course feedback  
            — and for professors and administrators to review insights.
        </p>

        <div>
            <a href="index.php?role=student" class="btn btn-light btn-custom">
                <i class="bi bi-person"></i> Student Login
            </a>

            <a href="index.php?role=professor" class="btn btn-light btn-custom">
                <i class="bi bi-person-badge"></i> Professor Login
            </a>

            <a href="index.php?role=admin" class="btn btn-dark btn-custom">
                <i class="bi bi-shield-lock-fill"></i> Admin Login
            </a>
        </div>

    </div>

    <div class="footer">
        © <?php echo date('Y'); ?> University Feedback System — Made with ❤
    </div>

</body>
</html>
