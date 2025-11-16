<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'INKOS - Sistem Informasi Kos'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom minimal styles -->
    <style>
        :root {
            --bs-primary: #6a11cb;
            --bs-primary-rgb: 106, 17, 203;
        }

        .bg-primary {
            background: linear-gradient(135deg, #6a11cb, #2575fc) !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a0db8, #1c6ae4);
            transform: translateY(-1px);
        }

        .text-primary {
            color: #6a11cb !important;
        }

        .border-primary {
            border-color: #6a11cb !important;
        }
    </style>
</head>

<body class="bg-light">