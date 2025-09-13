    <?php
    $p = 2;
    $titulo ='Listagem';
    require 'Cabecalho.php';
    ?>
    <?php
    require 'conexao.php';
    $sql = "SELECT `id`, `titulo`, `ano`, `genero`, `poster` FROM `midias` ORDER BY `titulo`;";
    $stmt = $conn->query($sql);
    ?>

    <div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-success">
        <tr>
            <th scope="col" style="width: 10%;">ID</th>
            <th scope="col" style="width: 25%;">Nome</th>
            <th scope="col" style="width: 15%;">Imagem</th>
            <th scope="col" style="width: 25%;">Descrição</th>
            <th scope="col" style="width: 25%;" colspan="2"></th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $stmt->fetch()) { ?>  
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['titulo'] ?></td>
            <td>
            <a target="_blank" href="<?= $row['poster'] ?>">Link imagem</a>
            </td>
            <td><?= $row['genero'] ?> - <?= $row['ano'] ?></td>
            <td>
            <a class="btn btn-sm btn-warning" href="#">
                <span data-feather="edit"></span> Editar
            </a>
            </td>
            <td>
            <a class="btn btn-sm btn-danger" href="excluir.php?id=<?= $row['id'] ?>">
                <span data-feather="trash-2"></span> Excluir
            </a>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
    </div>

    <?php

    require 'Rodape.php'

    ?>
