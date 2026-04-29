<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

class FormularioEstadoCamarero extends Formulario
{
    private $pedido_id;
    private $accion;
    private $producto_id;

    public function __construct($pedido_id, $accion, $producto_id = 0)
    {
        $this->pedido_id = (int) $pedido_id;
        $this->accion = $accion;
        $this->producto_id = (int) $producto_id;

        parent::__construct(
            'formCamarero_' . $this->pedido_id . '_' . $this->accion . ($this->producto_id ? '_' . $this->producto_id : '')
        );
    }

    protected function generaCamposFormulario(&$datos)
    {
        $textoBoton = 'Acción';

        if ($this->accion === 'cobrar') {
            $textoBoton = '💰 Cobrar';
        } elseif ($this->accion === 'preparar_linea') {
            $textoBoton = '🟡 Preparar';
        } elseif ($this->accion === 'pasar_entrega') {
            $textoBoton = '🛎️ Pasar a entrega';
        } elseif ($this->accion === 'entregar') {
            $textoBoton = '📦 Entregar';
        }

        return "
            <input type='hidden' name='pedido_id' value='{$this->pedido_id}'>
            <input type='hidden' name='accion' value='{$this->accion}'>
            <input type='hidden' name='producto_id' value='{$this->producto_id}'>
            <button type='submit' class='btn primary'>{$textoBoton}</button>
        ";
    }

    protected function procesaFormulario(&$datos)
    {
        $pedido_id = filter_var($datos['pedido_id'] ?? null, FILTER_VALIDATE_INT);
        $accion = $datos['accion'] ?? null;
        $producto_id = filter_var($datos['producto_id'] ?? 0, FILTER_VALIDATE_INT);

        if (!$pedido_id || !$accion) {
            return;
        }

        $accionesValidas = ['cobrar', 'preparar_linea', 'pasar_entrega', 'entregar'];
        if (!in_array($accion, $accionesValidas, true)) {
            if (function_exists('flash_set')) {
                flash_set('error', 'Acción no válida.');
            }
            header("Location: " . RUTA_APP . "/vistas/pedidos/gestionCamarero.php");
            exit;
        }

        $ok = false;

        if ($accion === 'cobrar') {
            // Aquí cambiarEstado() ya gestiona también la liquidación de BistroCoins si procede
            $ok = \PedidoService::cambiarEstado($pedido_id, 'en_preparacion');

            if (function_exists('flash_set')) {
                if ($ok) {
                    flash_set('success', 'Pago confirmado correctamente.');
                } else {
                    flash_set('error', 'No se pudo confirmar el pago.');
                }
            }

            $tab = 'recibidos';
        } elseif ($accion === 'preparar_linea') {
            if ($producto_id) {
                $ok = \PedidoService::marcarProductoPreparadoCamarero($pedido_id, $producto_id);
            }

            if (function_exists('flash_set')) {
                if ($ok) {
                    flash_set('success', 'Producto marcado como preparado correctamente.');
                } else {
                    flash_set('error', 'No se pudo actualizar el estado del producto.');
                }
            }

            $tab = 'listos';
        } elseif ($accion === 'pasar_entrega') {
            $ok = \PedidoService::terminarPedidoParaEntrega($pedido_id);

            if (function_exists('flash_set')) {
                if ($ok) {
                    flash_set('success', 'Pedido pasado a entrega correctamente.');
                } else {
                    flash_set('error', 'No se pudo pasar el pedido a entrega.');
                }
            }

            $tab = 'entregar';
        } else { // entregar
            $ok = \PedidoService::cambiarEstado($pedido_id, 'entregado');

            if (function_exists('flash_set')) {
                if ($ok) {
                    flash_set('success', 'Pedido entregado correctamente.');
                } else {
                    flash_set('error', 'No se pudo entregar el pedido.');
                }
            }

            $tab = 'entregar';
        }

        header("Location: " . RUTA_APP . "/vistas/pedidos/gestionCamarero.php?tab=" . $tab);
        exit;
    }
}