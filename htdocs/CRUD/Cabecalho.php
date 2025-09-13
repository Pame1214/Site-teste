 <!DOCTYPE html>
<html lang="pt-br">

<?php

/*
 ____            _              _           ____  _   _ ____  
|  _ \ __ _ _ __| |_ ___     __| | ___     |  _ \| | | |  _ \ 
| |_) / _` | '__| __/ _ \   / _` |/ _ \    | |_) | |_| | |_) |
|  __/ (_| | |  | ||  __/    (_| | (_) |   |  __/|  _  |  __/ 
|_|   \__,_|_|   \__\___|   \__,_|\___/    |_|   |_| |_|_|    
                                                         
*/





$list = 'inactive';
$form = 'inactive';
$inic = 'inactive';


switch ($p) {
    case 0:
        $inic = 'active';
        break;
    
    case 1:
        $form = 'active';
        break;
        
    case 2:
        $list = 'active';
        break;
    
}


?>


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>CRUD PHP</title>

    <!-- Bootstrap core CSS -->
    <link href="dist/bootstrap.min.css" rel="stylesheet">

    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .font-1 {
            color: #000 !important;
            font-weight: bolder;
        }
    </style>

    <!-- Custom styles for this template -->
    <link href="dist/dashboard.css" rel="stylesheet">


</head>

<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">
            CRUD PHP
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample03" aria-controls="navbarsExample03" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-collapse collapse" id="navbarsExample03">
            <ul class="navbar-nav mr-auto px-3 pb-2">
                <li class="nav-item">
                    <a class="nav-link" href="inicio.php">Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Forms.php">Formulário</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="List.php">Listagem</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-light btn-sm btn-block font-1 my-1">Entrar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-danger btn-sm btn-block font-1 my-1">Sair</a>
                </li>
            </ul>
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start pe-3 d-none d-sm-block">
            <div class="text-end">
                <a href= "Forms.php" class="btn btn-light me-2" >
                <!-- Entrar.php -->
                    <span data-feather="log-in"></span>
                    Entrar
                </a>
                <a href="Sair.php" class="btn btn-danger me-2">
                    <span data-feather="log-out"></span>
                    Sair
                </a>
            </div>
        </div>

    </header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky mt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $inic;?>" aria-current="page" href="inicio.php">
                                <span data-feather="home"></span>
                                início
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $form;?>" aria-current="page" href="Forms.php">
                                <span data-feather="file-text"></span>
                                Formulário
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $list;?>" aria-current="page" href="List.php">
                                <span data-feather="list"></span>
                                Listagem
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $titulo; ?></h1>
                </div>
                <br