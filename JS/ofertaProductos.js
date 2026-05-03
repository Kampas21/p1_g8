

//  SISTEMA DE PRODUCTOS DINÁMICOS EN OFERTA

// Se asume que existe:
// const productosDisponibles = [...]; (inyectado desde PHP)


//  CREAR PRODUCTO EN FORMULARIO


function crearSelectorProducto() {

    const contenedor = document.getElementById('contenedorProductosDinamicos');
    if (!contenedor) return;

    const div = document.createElement('div');
    div.classList.add('producto-item');

    let opciones = '<option value="">Selecciona producto</option>';

    if (typeof productosDisponibles !== 'undefined') {
        productosDisponibles.forEach(p => {
            opciones += `
                <option value="${p.id}" data-precio="${p.precio}">
                    ${p.nombre}: ${p.precio}€
                </option>
            `;
        });
    }

    div.innerHTML = `
        <select class="selector-producto">
            ${opciones}
        </select>

        <input type="number"
               name="cantidades[]"
               value="1"
               min="1"
               data-precio="0">

        <button type="button" class="eliminar-producto">X</button>
    `;

    contenedor.appendChild(div);

    registrarEventos();
    emitirCambioProductos();
}


//  EVENTOS GLOBALS


function registrarEventos() {

    // Cambio de producto seleccionado
    document.querySelectorAll('.selector-producto').forEach(select => {
        select.removeEventListener('change', onProductoChange);
        select.addEventListener('change', onProductoChange);
    });

    // Cambio de cantidad
    document.querySelectorAll("input[name^='cantidades']").forEach(input => {
        input.removeEventListener('input', onCantidadChange);
        input.addEventListener('input', onCantidadChange);
    });

    // eliminar producto
    document.querySelectorAll('.eliminar-producto').forEach(btn => {
        btn.removeEventListener('click', onEliminarProducto);
        btn.addEventListener('click', onEliminarProducto);
    });

    document.querySelector("form").addEventListener("submit", function () {
        prepararEnvioFormulario();
    });
}


// CUANDO SE SELECCIONA PRODUCTO


function onProductoChange(e) {

    const select = e.target;
    const option = select.selectedOptions[0];

    const precio = parseFloat(option.dataset.precio) || 0;

    const fila = select.closest('.producto-item');
    const inputCantidad = fila.querySelector("input[name^='cantidades']");

    inputCantidad.dataset.precio = precio;


    emitirCambioProductos();
}


//  CAMBIO DE CANTIDAD

function onCantidadChange(e) {

    const input = e.target;
    const cantidad = parseFloat(input.value) || 0;

    const fila = input.closest('.producto-item');

    emitirCambioProductos();
}


//  ELIMINAR PRODUCTO MANUAL


function onEliminarProducto(e) {
    const fila = e.target.closest('.producto-item');
    if (fila) fila.remove();

    emitirCambioProductos();
}


//  NOTIFICAR CAMBIOS AL SISTEMA DE CÁLCULO


function emitirCambioProductos() {

    document.dispatchEvent(new Event('productos:actualizados'));
}


//  INIT


document.addEventListener('DOMContentLoaded', function () {

    const btn = document.getElementById('aAddProduct');

    if (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            crearSelectorProducto();
        });
    }

    registrarEventos();


    //  EDICIÓN DE OFERTA (IMPORTANTE)
    
    if (typeof productosSeleccionados !== 'undefined' && productosSeleccionados.length > 0) {

        productosSeleccionados.forEach(p => {

            crearSelectorProducto();

            const filas = document.querySelectorAll('.producto-item');
            const ultima = filas[filas.length - 1];

            const select = ultima.querySelector('.selector-producto');
            const input = ultima.querySelector("input[name^='cantidades']");

            select.value = String(p.id);
            input.value = p.cantidad;

            // fuerza cálculo de precio
            select.dispatchEvent(new Event('change'));
        });

        setTimeout(() => {
            actualizarValores(); // o recalculo de descuento
        }, 0);
    }
});


function prepararEnvioFormulario() {

    const form = document.querySelector("form");

    // eliminar antiguos hidden
    document.querySelectorAll(".hidden-producto").forEach(e => e.remove());

    document.querySelectorAll(".producto-item").forEach(fila => {

        const select = fila.querySelector(".selector-producto");
        const input = fila.querySelector("input[name^='cantidades']");

        if (!select || !input) return;

        const productoId = select.value;
        const cantidad = input.value;

        if (productoId && cantidad > 0) {

            const hiddenProd = document.createElement("input");
            hiddenProd.type = "hidden";
            hiddenProd.name = "productos[]";
            hiddenProd.value = productoId;
            hiddenProd.classList.add("hidden-producto");

            const hiddenCant = document.createElement("input");
            hiddenCant.type = "hidden";
            hiddenCant.name = "cantidades[]";
            hiddenCant.value = cantidad;
            hiddenCant.classList.add("hidden-producto");

            form.appendChild(hiddenProd);
            form.appendChild(hiddenCant);
        }
    });
}