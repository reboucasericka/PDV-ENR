<?php 
require_once("../../conexao.php");

$id = $_POST['id'];

$query_con = $pdo->query("DELETE FROM usuarios WHERE id = '$id'");
echo 'Excluido com sucesso!';

?>
