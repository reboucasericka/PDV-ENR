<?php 
require_once("../../conexao.php");

$id = $_POST['id'];
$query_con = $pdo->prepare("DELETE from fornecedores WHERE id = :id");
$query_con->bindValue(":id", $id);
$query_con->execute();
echo 'Excluído com Sucesso!';

 ?>