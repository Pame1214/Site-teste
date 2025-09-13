<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

try {
    require 'conexao.php';

    // Supondo que o $id venha via GET ou POST
    $id = $_GET['id'] ?? null;

    if ($id) {
        $sql = "DELETE FROM midias WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo "Registro deletado com sucesso!";
        } else {
            echo "Erro ao deletar o registro.";
        }
    } else {
        echo "ID inválido.";
    }

} catch (Throwable $th) {
    echo "Erro: " . $th->getMessage();
}
