window.onload = function () {

    // FILTRO POR CATEGORÍAS (SOLO VISUAL)
    window.filtrar = function (cat) {

        document.querySelectorAll(".producto").forEach(p => {

            if (cat === 0) {
                p.style.display = "block";
            } else {
                p.style.display = (p.dataset.cat == cat) ? "block" : "none";
            }
        });
    };

    // AÑADIR PRODUCTO (SIN AJAX, SOLO DOM + INPUTS OCULTOS)
    window.addProduct = function (id, nombre) {

        let cantidad = document.getElementById("cant_" + id).value;

        // inputs ocultos para PHP
        document.getElementById("inputs").innerHTML += `
            <input type="hidden" name="productos[]" value="${id}">
            <input type="hidden" name="cantidades[]" value="${cantidad}">
        `;

        // lista visual
        document.getElementById("lista").innerHTML += `
            <div>
                ${nombre} - ${cantidad} uds
            </div>
        `;
    };

    // BOTONES CATEGORÍAS (NO SE TE OLVIDE ESTO)
    document.querySelectorAll(".catBtn").forEach(btn => {
        btn.addEventListener("click", function () {
            filtrar(this.dataset.id);
        });
    });

};