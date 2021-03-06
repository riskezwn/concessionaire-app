<?php

// Senear variables de un formulario
function sanitize($v)
{
    $v = trim($v);
    $v = strip_tags($v);
    return $v;
}
// Eliminar sesion
function destroySession($session)
{
    if (isset($_SESSION[$session])) {
        unset($_SESSION[$session]);
    }
}
function showErrors($error, $field = null)
{
    $result = '';
    if (isset($error)) {
        $result = "<div class='error'><i class='fas fa-exclamation-circle'></i>$error</div>";
    }
    return $result;
}
function showDBError($status, $con)
{
    if ($status) {
        $result = "<div class='msg'><i class='fas fa-exclamation-circle'></i>Se ha guardado correctamente</div>";
    } else {
        $result = "<div class='error'><i class='fas fa-exclamation-circle'></i>Ha habido un error al guardar en la base de datos</div>" . mysqli_error($con);
    }
    return $result;
}
function checkString($v)
{
    $result = false;
    if (is_string($v) && preg_match('/^[A-Za-zÀ-ÿ0-9 ]+$/', $v)) {
        $result = true;
    }
    return $result;
}
function checkInt($v)
{
    $result = false;
    if (is_int($v)) {
        $result = true;
    }
    return $result;
}
function checkFecha($fecha)
{
    $valores = explode('/', $fecha);
    if (count($valores) == 3 && checkdate($valores[1], $valores[0], $valores[2])) {
        return true;
    }
    return false;
}

// DB functions

function getCars($con, $id = null)
{
    $sql = "SELECT *,
    LOWER(CONCAT(marca, '_', REPLACE(modelo, ' ', ''))) AS imagen
     FROM coches ";
    if (isset($id)) {
        $sql .= "WHERE id = '$id'";
    }
    $sql .= "ORDER BY marca ASC";
    $stmt = mysqli_query($con, $sql);

    $result = false;
    if ($stmt && mysqli_num_rows($stmt) >= 1) {
        $result = $stmt;
    }
    return $result;
}

function createCar($con, $marca, $modelo, $precio, $stock, $activo)
{
    $sql = "INSERT INTO coches (marca, modelo, precio, stock, activo)
    VALUES ('$marca', '$modelo', $precio, $stock, $activo) ";

    $stmt = mysqli_query($con, $sql);
    $result = false;
    if ($stmt) $result = true;
    return $result;
}
function editCar($con, $id, $marca, $modelo, $precio, $stock, $activo)
{
    $sql = "UPDATE coches SET 
            marca = '$marca',
            modelo = '$modelo',
            precio = $precio,
            stock = $stock,
            activo = $activo
            WHERE id = $id";
    
    $stmt = mysqli_query($con, $sql);
    $result = false;
    if ($stmt) $result = true;
    return $result;
}

function getSellers($con, $id = null)
{
    $sql = "SELECT v.*,
            DATE_FORMAT(v.fecha_alta, '%d-%m-%Y') AS antiguedad,
            c.nombre AS cargo,
            g.nombre AS grupo,
            CONCAT (j.nombre, ' ', j.apellidos) AS jefe
            FROM vendedores v 
                INNER JOIN grupos g ON v.grupo_id = g.id
                INNER JOIN cargos c ON v.cargo_id = c.id
                LEFT JOIN vendedores j ON v.jefe_id = j.id
                ";
    if (isset($id)) {
        $sql .= "WHERE id = '$id'";
    }
    $sql .= "ORDER BY cargo_id ASC";
    $stmt = mysqli_query($con, $sql);

    $result = false;
    if ($stmt && mysqli_num_rows($stmt) >= 1) {
        $result = $stmt;
    }
    return $result;
}
function createSeller($con, $nombre, $apellidos, $email, $pass, $cargo, $salario, $comision, $grupo, $responsable)
{
    $sql = "INSERT INTO vendedores (nombre, apellidos, email, clave, cargo_id, sueldo, comision, grupo_id, jefe_id, fecha_alta)
    VALUES ('$nombre', '$apellidos', '$email', '$pass', $cargo, $salario, $comision, $grupo, $responsable, CURDATE()) ";

    $stmt = mysqli_query($con, $sql);
    $result = false;
    if ($stmt) $result = true;
    return $result;
}
function getGroups($con, $table)
{
    $sql = "SELECT *
            FROM $table";
    $stmt = mysqli_query($con, $sql);
    $result = false;
    if ($stmt && mysqli_num_rows($stmt) >= 1) {
        $result = $stmt;
    }
    return $result;
}
function getBosses($con)
{
    $sql = "SELECT id,
            CONCAT(nombre, ' ', apellidos) AS nombre
            FROM vendedores
            WHERE cargo_id <= 3;";
    $stmt = mysqli_query($con, $sql);
    $result = false;
    if ($stmt && mysqli_num_rows($stmt) >= 1) {
        $result = $stmt;
    }
    return $result;
}
function getCustomers($con, $id = null, $id_vendedor = null)
{
    $sql = "SELECT c.*,
            CONCAT (v.nombre, ' ', v.apellidos) AS vendedor
            FROM clientes c
                INNER JOIN vendedores v ON c.vendedor_id = v.id
                ";
    if (isset($id)) {
        $sql .= "WHERE id = '$id'";
    }
    if (isset($id_vendedor)) {
        $sql .= "WHERE vendedor_id = $id_vendedor";
    }
    $stmt = mysqli_query($con, $sql);

    $result = false;
    if ($stmt && mysqli_num_rows($stmt) >= 1) {
        $result = $stmt;
    }
    return $result;
}
function createCustomer($con, $nombre, $ciudad, $activo, $vendedor)
{
    $sql = "INSERT INTO clientes (nombre, ciudad, activo, vendedor_id, fecha_alta)
    VALUES ('$nombre', '$ciudad', $activo, '$vendedor', CURDATE()) ";

    $stmt = mysqli_query($con, $sql);
    $result = false;
    if ($stmt) $result = true;
    return $result;
}
function getOrders($con, $id = null)
{
    $sql = "SELECT p.*,
            LPAD(p.id, 5, '0') AS pedido_id,
            DATE_FORMAT(p.fecha, '%d-%m-%Y') AS fecha,
            cl.nombre AS cliente,
            CONCAT(co.marca, ' ', co.modelo) AS coche,
            CONCAT(v.nombre, ' ', v.apellidos) AS vendedor,
            co.precio * p.cantidad AS importe_total
            FROM pedidos p 
                INNER JOIN clientes cl ON p.cliente_id = cl.id
                INNER JOIN coches co ON p.coche_id = co.id 
                INNER JOIN vendedores v ON cl.vendedor_id = v.id ";
    if (isset($id)) {
        $sql .= "WHERE p.id = '$id'";
    }
    $sql .= "ORDER BY p.fecha DESC";
    $stmt = mysqli_query($con, $sql);

    $result = false;
    if ($stmt && mysqli_num_rows($stmt) >= 1) {
        $result = $stmt;
    }
    return $result;
}
function createOrder($con, $cliente, $modelo, $cantidad, $fecha)
{
    $car = mysqli_fetch_assoc(getCars($con, $modelo));
    $car_stock = $car['stock'];

    $result = false;
    if ($cantidad <= $car_stock) {
        $sql = "INSERT INTO pedidos (coche_id, cliente_id, cantidad, fecha)
                VALUES ($modelo, $cliente, $cantidad, '$fecha') ";
        $stmt = mysqli_query($con, $sql);

        $sqlUpdStock = "UPDATE coches
                        SET stock = stock - $cantidad
                        WHERE id = $modelo
                        AND stock >= $cantidad";
        $stmtUpdStock = mysqli_query($con, $sqlUpdStock);

        if ($stmt && $stmtUpdStock) $result = true;
    }
    return $result;
}

/* function controlStock($con, $cantidad, $coche_id)
{
    $sqlUpdStock = "UPDATE coches
            SET stock =
            stock - $cantidad
            WHERE id = $coche_id
            AND stock >= $cantidad";
    $stmtUpdStock = mysqli_query($con, $sql);
    $result = false;
    if ($stmt) $result = true;
    return $result;
} */

/* PERMISSIONS */

// Permisos sólo de administrador
function checkAdminPermissions($con, $id, $permission_level)
{
    $sql = "SELECT cargo_id
            FROM vendedores
            WHERE id = $id";
    $stmt = mysqli_query($con, $sql);

    $result = false;
    if ($stmt) {
        $userdata = mysqli_fetch_assoc($stmt);
        $cargo_id = $userdata['cargo_id'];

        if ($cargo_id <= $permission_level) {
            $result = true;
        }
    }
    return $result;
}
