function actualizarValores() {

    let inicial = 0;

    document.querySelectorAll("input[name^='cantidades']").forEach(input => {
        const cantidad = parseFloat(input.value) || 0;
        const precio = parseFloat(input.dataset.precio) || 0;
        inicial += cantidad * precio;
    });

    const campoInicial = document.getElementById('precio_inicial');
    if (campoInicial) campoInicial.value = inicial.toFixed(2);

    let final = parseFloat(document.getElementById('precio_final')?.value) || 0;

    let descuento = 0;

    if (inicial > 0 && final > 0) {
        descuento = ((inicial - final) / inicial) * 100;
    }

    let campo = document.getElementById('descuento');
    if (campo) {
        campo.value = descuento.toFixed(2);
    }

    const campoDesc = document.getElementById('descuento');
    const hidden = document.getElementById('descuentoHidden');

    if (hidden) {
        hidden.value = descuento.toFixed(2);
    }
}

//  AQUÍ ESTÁ LA CLAVE
document.addEventListener('productos:actualizados', actualizarValores);

// INIT

document.addEventListener('DOMContentLoaded', function () {

    let final = document.getElementById('precio_final');
    if (final) final.addEventListener('input', actualizarValores);

    actualizarValores();
});