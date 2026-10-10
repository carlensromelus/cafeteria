<?php
session_start();
require_once __DIR__ . '/bootstrap.php';

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    check_csrf();

    $email = $_POST["email"] ?? '';
    $senha = $_POST["senha"] ?? '';



    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($senha, $user["senha"])) {
            session_regenerate_id(true);
            $_SESSION["logado"] = true;
            $_SESSION["user_id"] = (int)$user["id"];
            $_SESSION["usuario"] = $email;

            if($user["role"] === "admin") {
                header("Location: admin.php");
                exit;
            }

            // ✅ CAMINHO CORRETO
            header("Location: index.php");
            exit;
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Email não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Login Cafeteria</title>

<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family: Arial, sans-serif;
}

body {
    height:100vh;
    background:#EFEBE9;
    display:flex;
    justify-content:center;
    align-items:center;
}

/* CONTAINER */
.login-container {
    width:900px;
    height:520px;
    background:white;
    display:flex;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 15px 40px rgba(0,0,0,0.2);
}

/* LADO IMAGEM */
.left {
    width:50%;
    background:url("https://images.unsplash.com/photo-1509042239860-f550ce710b93") no-repeat center;
    background-size:cover;
    position:relative;
}

.left::after {
    content:"";
    position:absolute;
    width:100%;
    height:100%;
    background:rgba(62,39,35,0.65);
}

.left-text {
    position:absolute;
    bottom:30px;
    left:30px;
    color:white;
    z-index:2;
    font-size:20px;
    font-weight:bold;
}

/* LADO DIREITO */
.right {
    width:50%;
    padding:50px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

/* TITULO */
.right h2 {
    color:#3E2723;
    margin-bottom:10px;
}

.right p {
    color:#777;
    font-size:14px;
    margin-bottom:25px;
}

/* INPUTS */
input {
    padding:12px;
    margin-bottom:15px;
    border:1px solid #ddd;
    border-radius:5px;
    outline:none;
    transition:0.3s;
}

input:focus {
    border-color:#6D4C41;
}

/* LINK */
.link {
    font-size:12px;
    color:#6D4C41;
    text-align:right;
    margin-bottom:15px;
    cursor:pointer;
}

/* BOTÃO */
button {
    padding:12px;
    background:#6D4C41;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-weight:bold;
    transition:0.3s;
    width:100%;
}

button:hover {
    background:#D7A86E;
}

.create-account {
    margin-top: 15px;
}

.create-account button {
    width: 100%;
}

.admin-btn {
    display: inline-block;
    width: 100%;
    text-align: center;
    padding: 12px;
    background: #4A3424;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
    margin-top: 10px;
    text-decoration: none;
}

.admin-btn:hover {
    background: #6D4C41;
}

/* DIVISOR */
.divisor {
    text-align:center;
    margin:20px 0;
    color:#aaa;
    font-size:12px;
}

/* SOCIAL */
.social {
    display:flex;
    gap:10px;
}

.social button {
    flex:1;
    background:#EFEBE9;
    color:#333;
    border:1px solid #ccc;
}

.social button:hover {
    background:#ddd;
}

/* ERRO */
.erro {
    color:red;
    margin-bottom:10px;
    font-size:13px;
}
</style>

</head>

<body>

<div class="login-container">

    <!-- IMAGEM -->
    <div class="left">
        <div class="left-text">
        </div>
    </div>

    <!-- FORM -->
    <div class="right">
        <h2>Bem-vindo de volta</h2>
        <p>Faça login para acessar o painel</p>

        <?php if(isset($erro)) echo "<div class='erro'>$erro</div>"; ?>

        <form method="POST"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>

        <div class="create-account">
            <button type="button" onclick="window.location='cadastro.php'">Criar Conta</button>
            <!-- <a href="admin.php" class="admin-btn">Acessar Admin</a> -->
        </div>

</div>

</body>
</html>