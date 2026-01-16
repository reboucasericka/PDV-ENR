<?php 
require_once("../../conexao.php");

$nome = $_POST['nome'];
$email = $_POST['email'];
$nif = $_POST['nif'];
$endereco = $_POST['endereco'];
$telemovel = $_POST['telemovel'];
$tipo_pessoa = $_POST['tipo'];
$id = $_POST['id'];

$antigo = $_POST['antigo'];
$antigo2 = $_POST['antigo2'];

// EVITAR DUPLICIDADE NO EMAIL
if($antigo2 != $email){
	$query_con = $pdo->prepare("SELECT * from fornecedores WHERE email = :email");
	$query_con->bindValue(":email", $email);
	$query_con->execute();
	$res_con = $query_con->fetchAll(PDO::FETCH_ASSOC);
	if(@count($res_con) > 0){
		echo 'O email do fornecedor já está cadastrado!';
		exit();
	}
}

if($antigo != $nif){
// EVITAR DUPLICIDADE NO nif
	$query_con = $pdo->prepare("SELECT * from fornecedores WHERE nif = :nif");
	$query_con->bindValue(":nif", $nif);
	$query_con->execute();
	$res_con = $query_con->fetchAll(PDO::FETCH_ASSOC);
	if(@count($res_con) > 0){
		echo 'O NIF do fornecedor já está cadastrado!';
		exit();
	}
}

if($id == ""){
	$res = $pdo->prepare("INSERT INTO fornecedores SET nome = :nome, email = :email, nif = :nif, telemovel = :telemovel, endereco = :endereco, tipo_pessoa = :tipo_pessoa");
	$res->bindValue(":nome", $nome);
	$res->bindValue(":email", $email);
	$res->bindValue(":nif", $nif);
	$res->bindValue(":telemovel", $telemovel);
	$res->bindValue(":endereco", $endereco);
	$res->bindValue(":tipo_pessoa", $tipo_pessoa);
	$res->execute();
}else{
	$res = $pdo->prepare("UPDATE fornecedores SET nome = :nome, email = :email, nif = :nif, telemovel = :telemovel, endereco = :endereco, tipo_pessoa = :tipo_pessoa WHERE id = :id");
	$res->bindValue(":nome", $nome);
	$res->bindValue(":email", $email);
	$res->bindValue(":nif", $nif);
	$res->bindValue(":telemovel", $telemovel);
	$res->bindValue(":endereco", $endereco);
	$res->bindValue(":tipo_pessoa", $tipo_pessoa);
	$res->bindValue(":id", $id);
	$res->execute();
}



echo 'Salvo com Sucesso!';
?>