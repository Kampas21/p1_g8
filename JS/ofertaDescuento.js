function actualizarValores() {
    let inicial = parseFloat(document.getElementById('precio_inicial')?.value) || 0;
    let final = parseFloat(document.getElementById('precio_final')?.value) || 0;

    let descuento = 0;

    if (inicial > 0) {
        descuento = ((inicial - final) / inicial) * 100;
    }

    let campo = document.getElementById('descuento');
    if (campo) {
        campo.value = descuento.toFixed(2);
    }
}

// Esperar a que cargue la página
document.addEventListener('DOMContentLoaded', function () {

    let inicial = document.getElementById('precio_inicial');
    let final = document.getElementById('precio_final');

    if (inicial) inicial.addEventListener('input', actualizarValores);
    if (final) final.addEventListener('input', actualizarValores);

    actualizarValores(); // calcular al cargar
});