<?php 
require_once("conexao.php");
@session_start(); // Inicia a sessão para armazenar variáveis de sessão

$usuario = $_POST['usuario'];
$senha = $_POST['senha'];

$query_con = $pdo->prepare("SELECT * from usuarios WHERE (email = :usuario or nif = :usuario) and senha = :senha");
	$query_con->bindValue(":usuario", $usuario);
	$query_con->bindValue(":senha", $senha);
	$query_con->execute();
	$res_con = $query_con->fetchAll(PDO::FETCH_ASSOC);

	if(@count($res_con) > 0){
		$nivel = $res_con[0]['nivel'];
// Armazena as informações do usuário na sessão
		$_SESSION['nome_usuario'] = $res_con[0]['nome'];
		$_SESSION['nivel_usuario'] = $res_con[0]['nivel'];
		$_SESSION['nif_usuario'] = $res_con[0]['nif'];
		$_SESSION['id_usuario'] = $res_con[0]['id'];
// Após autenticação bem-sucedida, redireciona para verificar permissões

		if($nivel == 'Administrador'){
			echo "<script language='javascript'>window.location='painel-adm'</script>";
		}

		if($nivel == 'Gerente'){
			echo "<script language='javascript'>window.location='painel-gerente'</script>";
		}

		if($nivel == 'Vendedor'){
			echo "<script language='javascript'>window.location='painel-vendedor'</script>";
		}
	}else{
		echo "<script language='javascript'>window.alert('Dados Incorretos!')</script>";

		echo "<script language='javascript'>window.location='index.php'</script>";

	}

 ?>