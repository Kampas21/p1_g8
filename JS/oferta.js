function calcularTotal() {
    let total = 0;

    document.querySelectorAll("input[name^='cantidades']").forEach(input => {
        const cantidad = parseInt(input.value) || 0;
        const precio = parseFloat(input.dataset.precio) || 0;

        total += cantidad * precio;
    });

    document.getElementById("precioTotal").innerText = total.toFixed(2);

    let descuento = calcularDescuento(total);
    document.getElementById("descuentoTxt").innerText = descuento;

    // ⚠️ guardar en input real (para PHP)
    document.getElementById("descuento").value = descuento;

    let final = total * (1 - descuento / 100);
    document.getElementById("precioFinal").innerText = final.toFixed(2);
}

function calcularDescuento(total) {
    if (total > 100) return 20;
    if (total > 50) return 10;
    return 5;
}

// calcular al cargar (modo edición)
window.onload = calcularTotal;