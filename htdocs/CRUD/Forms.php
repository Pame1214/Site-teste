<?php
$p = 1;
$titulo = 'Formulário de Mídias';
require 'Cabecalho.php';
require 'conexao.php';


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Mídias</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Cadastrar Nova Mídia</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="gravar.php">
                            <div class="mb-3">
                                <label for="titulo" class="form-label">Título</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ano" class="form-label">Ano</label>
                                <input type="number" class="form-control" id="ano" name="ano" min="1900" max="2030" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="genero" class="form-label">Gênero</label>
                                <select class="form-select" id="genero" name="genero" required>
                                    <option value="">Selecione um gênero</option>
                                    <option value="Ação">Ação</option>
                                    <option value="Aventura">Aventura</option>
                                    <option value="Comédia">Comédia</option>
                                    <option value="Drama">Drama</option>
                                    <option value="Terror">Terror</option>
                                    <option value="Ficção Científica">Ficção Científica</option>
                                    <option value="Romance">Romance</option>
                                    <option value="Suspense">Suspense</option>
                                    <option value="Animação">Animação</option>
                                    <option value="Documentário">Documentário</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="poster" class="form-label">Poster do filme (URL)</label>
                                <input type="url" class="form-control" id="poster" name="poster" placeholder="Endereço http de um poster">
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Gravar</button>
                                <button type="button" class="btn btn-warning" onclick="limparFormulario()">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function limparFormulario() {
            document.querySelector('form').reset();
        }
    </script>
</body>
</html>

<?php
require 'Rodape.php';
?>