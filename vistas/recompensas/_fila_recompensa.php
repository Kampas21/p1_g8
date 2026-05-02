<?php
$puede = $disponiblesPedido >= $recompensa->getBistrocoins() && $pedidoId > 0;
?>

<tr>
    <td data-label="Producto">
        <strong><?= escaparHtml($recompensa->getProductoNombre()) ?></strong><br>
        <span class="muted"><?= escaparHtml($recompensa->getProductoDescripcion()) ?></span>
    </td>

    <td data-label="Precio carta">
        <?= number_format($recompensa->getProductoPrecioFinal(), 2) ?> €
    </td>

    <td data-label="Coste">
        <?= (int)$recompensa->getBistrocoins() ?> BistroCoins
    </td>

    <td data-label="Estado">
        <?= $puede ? 'Disponible' : 'Saldo insuficiente o sin pedido' ?>
    </td>

    <td data-label="Acción">
        <form method="post">
            <input type="hidden"
                   name="recompensa_id"
                   value="<?= (int)$recompensa->getId() ?>">

            <button type="submit"
                    class="btn small"
                    <?= $puede ? '' : 'disabled' ?>>
                Canjear
            </button>
        </form>
    </td>
</tr>
