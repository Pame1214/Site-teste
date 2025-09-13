<?php
// Processar formulário quando enviado
require_once 'conexao.php';
if ($_POST) {
    

    $titulo = $_POST['titulo'];
    $ano = $_POST['ano'];
    $genero = $_POST['genero'];
    $poster = $_POST['poster'];
    
    // Preparar e executar o INSERT
    $sql = "INSERT INTO `midias`( `titulo`, `ano`, `genero`, `poster`) VALUES (?,?,?,?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$titulo, $ano, $genero, $poster])) {
        echo '<div class="alert alert-success">Mídia cadastrada com sucesso!</div>';
    } else {
        echo '<div class="alert alert-danger">Erro ao cadastrar mídia!</div>';
    }
}