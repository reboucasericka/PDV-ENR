<?php 
require_once("../conexao.php");

$nome = $_POST['nome-perfil'];
$email = $_POST['email-perfil'];
$nif = $_POST['nif-perfil'];
$senha = $_POST['senha-perfil'];

$id = $_POST['id-perfil'];

$antigo = $_POST['antigo-perfil'];
$antigo2 = $_POST['antigo2-perfil'];

// EVITAR DUPLICIDADE NO EMAIL
if($antigo2 != $email){
	$query_con = $pdo->prepare("SELECT * from usuarios WHERE email = :email");
	$query_con->bindValue(":email", $email);
	$query_con->execute();
	$res_con = $query_con->fetchAll(PDO::FETCH_ASSOC);
	if(@count($res_con) > 0){
		echo 'O email do usuário já está cadastrado!';
		exit();
	}
}

if($antigo != $nif){
// EVITAR DUPLICIDADE NO NIF
	$query_con = $pdo->prepare("SELECT * from usuarios WHERE nif = :nif");
	$query_con->bindValue(":nif", $nif);
	$query_con->execute();
	$res_con = $query_con->fetchAll(PDO::FETCH_ASSOC);
	if(@count($res_con) > 0){
		echo 'O NIF do usuário já está cadastrado!';
		exit();
	}
}


	$res = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, nif = :nif, senha = :senha WHERE id = :id");
	$res->bindValue(":nome", $nome);
	$res->bindValue(":email", $email);
	$res->bindValue(":nif", $nif);
	$res->bindValue(":senha", $senha);	
	$res->bindValue(":id", $id);
	$res->execute();



echo 'Salvo com Sucesso!';
?>