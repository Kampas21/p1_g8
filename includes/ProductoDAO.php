<?php
require_once __DIR__ . '/../entities/producto.php';
require_once __DIR__ . '/application.php';

class ProductoDAO {

    public static function getAllByCategoria($categoria_id) {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM productos WHERE categoria_id = ?");
        $stmt->bind_param("i", $categoria_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $productos = [];

        while ($fila = $result->fetch_assoc()) {
            $productos[] = new Producto(
                $fila['id'],
                $fila['nombre'],
                $fila['descripcion'],
                $fila['categoria_id'],
                $fila['precio_base'],
                $fila['iva'],
                $fila['disponible'],
                $fila['ofertado'],
                $fila['imagen'] ?? null,
                $fila['se_cocina'] ?? 1
            );
        }

        $result->free();
        $stmt->close();

        return $productos;
    }

    public static function getAllActivosByCategoria($categoria_id) {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM productos WHERE ofertado = 1 AND categoria_id = ?");
        $stmt->bind_param("i", $categoria_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $productos = [];

        while ($fila = $result->fetch_assoc()) {
            $productos[] = new Producto(
                $fila['id'],
                $fila['nombre'],
                $fila['descripcion'],
                $fila['categoria_id'],
                $fila['precio_base'],
                $fila['iva'],
                $fila['disponible'],
                $fila['ofertado'],
                $fila['imagen'] ?? null,
                $fila['se_cocina'] ?? 1
            );
        }

        $result->free();
        $stmt->close();

        return $productos;
    }

    public static function getAllActivos() {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM productos WHERE ofertado = 1");
        $stmt->execute();

        $result = $stmt->get_result();
        $productos = [];

        while ($fila = $result->fetch_assoc()) {
            $productos[] = new Producto(
                $fila['id'],
                $fila['nombre'],
                $fila['descripcion'],
                $fila['categoria_id'],
                $fila['precio_base'],
                $fila['iva'],
                $fila['disponible'],
                $fila['ofertado'],
                $fila['imagen'] ?? null,
                $fila['se_cocina'] ?? 1
            );
        }

        $result->free();
        $stmt->close();

        return $productos;
    }

    public static function getById($id) {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $result->free();
        $stmt->close();

        if (!$row) return null;

        return new Producto(
            $row['id'],
            $row['nombre'],
            $row['descripcion'],
            $row['categoria_id'],
            $row['precio_base'],
            $row['iva'],
            $row['disponible'],
            $row['ofertado'],
            $row['imagen'] ?? null,
            $row['se_cocina'] ?? 1
        );
    }

    public static function create($nombre, $descripcion, $categoria_id, $precio, $iva, $se_cocina = 1) {
        global $conn;

        $disponible = 1;
        $ofertado = 1;

        $stmt = $conn->prepare("
            INSERT INTO productos
            (nombre, descripcion, categoria_id, precio_base, iva, disponible, ofertado, se_cocina)
            VALUES (?, ?, ?, ?, ?, ?, ? ,?)
        ");

        $stmt->bind_param(
            "ssidiiii",
            $nombre,
            $descripcion,
            $categoria_id,
            $precio,
            $iva,
            $disponible,
            $ofertado,
            $se_cocina
        );

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

  public static function update(
$id,
$nombre,
$descripcion,
$categoria_id,
$precio,
$iva,
$se_cocina,
$imagen
){

global $conn;

$sql="
UPDATE productos
SET
nombre=?,
descripcion=?,
categoria_id=?,
precio_base=?,
iva=?,
se_cocina=?,
imagen=?
WHERE id=?
";

$stmt=$conn->prepare($sql);

$stmt->bind_param(
"ssidiisi",
$nombre,
$descripcion,
$categoria_id,
$precio,
$iva,
$se_cocina,
$imagen,
$id
);

$ok=$stmt->execute();

$stmt->close();

return $ok;

}

    public static function activar($id) {
        global $conn;

        $stmt = $conn->prepare("UPDATE productos SET ofertado = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function desactivar($id) {
        global $conn;

        $stmt = $conn->prepare("UPDATE productos SET ofertado = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function getProductosDeOferta($oferta_id) {
        global $conn;

        $stmt = $conn->prepare("
            SELECT p.*, op.cantidad 
            FROM productos p
            JOIN oferta_productos op ON p.id = op.producto_id
            WHERE op.oferta_id = ?
        ");
        $stmt->bind_param("i", $oferta_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $productos = [];

        while ($fila = $result->fetch_assoc()) {
            $producto = new Producto(
                $fila['id'],
                $fila['nombre'],
                $fila['descripcion'],
                $fila['categoria_id'],
                $fila['precio_base'],
                $fila['iva'],
                $fila['disponible'],
                $fila['ofertado'],
                $fila['imagen'] ?? null,
                $fila['se_cocina'] ?? 1
            );

            $producto->cantidad = $fila['cantidad'];
            $productos[] = $producto;
        }

        $result->free();
        $stmt->close();

        return $productos;
    }
}
