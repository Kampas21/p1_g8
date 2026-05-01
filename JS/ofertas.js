
// ── Lógica dinámica del formulario de ofertas ──────────────────────────────
// Combina el patrón <template> del Ejemplo1V2 (formularioOfertasV2.js)
// con el cálculo de totales ya existente en oferta.js.

function crearSelectorProducto() {
    const template = document.querySelector('#mySelectProductsTemplate');
    if (!template) return;

    const clone = template.content.cloneNode(true);
    document.querySelector('#contenedorProductosDinamicos').appendChild(clone);
}

function calcularTotal() {
    let total = 0;

    document.querySelectorAll("input[name^='cantidades']").forEach(input => {
        const cantidad = parseInt(input.value) || 0;
        const precio   = parseFloat(input.dataset.precio) || 0;
        total += cantidad * precio;
    });

    document.getElementById('precioTotal').innerText = total.toFixed(2);

    const descuento = calcularDescuento(total);
    document.getElementById('descuentoTxt').innerText = descuento;

    // Guarda el descuento calculado en el input oculto para que PHP lo reciba
    const inputDescuento = document.getElementById('descuentoCalculado');
    if (inputDescuento) inputDescuento.value = descuento;

    const precioFinal = total * (1 - descuento / 100);
    document.getElementById('precioFinal').innerText = precioFinal.toFixed(2);
}

function calcularDescuento(total) {
    if (total > 100) return 20;
    if (total > 50)  return 10;
    return 5;
}

function registrarEventosCantidad() {
    document.querySelectorAll("input[name^='cantidades']").forEach(input => {
        input.removeEventListener('input', calcularTotal);
        input.addEventListener('input', calcularTotal);
    });
}

window.onload = function () {
    const botonAnadir = document.getElementById('aAddProduct');
    if (botonAnadir) {
        botonAnadir.addEventListener('click', function (e) {
            e.preventDefault();
            crearSelectorProducto();
        });
    }

    registrarEventosCantidad();
    calcularTotal(); // cálculo inicial (útil en modo edición)
};
