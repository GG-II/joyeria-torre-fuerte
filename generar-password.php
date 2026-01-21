<?php
// Generar hash correcto para password "123456"
$password = '123456';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: $password<br>";
echo "Hash: $hash<br><br>";

// Verificar que funciona
if (password_verify($password, $hash)) {
    echo "✅ Verificación exitosa!";
} else {
    echo "❌ Error en verificación";
}
?>