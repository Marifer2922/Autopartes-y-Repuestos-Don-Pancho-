function cargarEdicion(boton) {
    document.getElementById("form-editar-repuesto").reset();

    document.getElementById("editar-id").value =
        boton.getAttribute("data-id");

    document.getElementById("editar-codigo").value =
        boton.getAttribute("data-codigo");

    document.getElementById("editar-nombre").value =
        boton.getAttribute("data-nombre");

    document.getElementById("editar-especificaciones").value =
        boton.getAttribute("data-especificaciones");

    document.getElementById("editar-descripcion").value =
        boton.getAttribute("data-descripcion");

    document.getElementById("editar-categoria").value =
        boton.getAttribute("data-categoria");

    document.getElementById("editar-marca").value =
        boton.getAttribute("data-marca");

    document.getElementById("editar-precio").value =
        boton.getAttribute("data-precio");

    document.getElementById("editar-stock").value =
        boton.getAttribute("data-stock");

    document.getElementById("editar-destacado").value =
        boton.getAttribute("data-destacado");
}