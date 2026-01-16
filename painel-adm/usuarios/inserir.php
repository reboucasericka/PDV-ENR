<?php 
require_once("../../conexao.php");

$nome = $_POST['nome'];
$email = $_POST['email'];
$nif = $_POST['nif'];
$senha = $_POST['senha'];
$nivel = $_POST['nivel'];
$id = $_POST['id'];

$antigo = $_POST['antigo'];
$antigo2 = $_POST['antigo2'];

//echo $id; //para testar
//para evitar a duplicidade no email 
if ($antigo2 != $email) {
    $query_con = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $query_con->bindValue(":email", $email);
    $query_con->execute();
    $res_con =  $query_con->fetchAll(PDO::FETCH_ASSOC);
    if (@count($res_con) > 0) {
        echo 'O email do usuário já está cadastrado!';
        exit();
    }
}

//para evitar a duplicidade no nif
if ($antigo != $nif) {
    $query_con = $pdo->prepare("SELECT * FROM usuarios WHERE nif = :nif");
    $query_con->bindValue(":nif", $nif);
    $query_con->execute();
    $res_con =  $query_con->fetchAll(PDO::FETCH_ASSOC);
    if (@count($res_con) > 0) {
        echo ' NIF já cadastrado!';
        exit();
    }
}

if ($id == "") {
    $res = $pdo->prepare("INSERT INTO usuarios SET nome = :nome, email = :email, nif = :nif, senha = :senha, nivel = :nivel");
    $res->bindValue(":nome", $nome);   
    $res->bindValue(":email", $email);
    $res->bindValue(":nif", $nif);
    $res->bindValue(":senha", $senha);
    $res->bindValue(":nivel", $nivel);
    $res->execute();
} else {
    $res = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, nif = :nif, senha = :senha, nivel = :nivel where id = :id");
    $res->bindValue(":nome", $nome);
    $res->bindValue(":email", $email);
    $res->bindValue(":nif", $nif);
    $res->bindValue(":senha", $senha);
    $res->bindValue(":nivel", $nivel);
    $res->bindValue(":id", $id);
    $res->execute();
}

echo 'Salvo com Sucesso!';
?>