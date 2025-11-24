<?php
// admin/api_carros.php
header('Content-Type: application/json');

// --- 1. CONEXÃO COM O BANCO ---
$host = '127.0.0.1';
$user = 'root';      // Seu usuário do banco
$pass = '';          // Sua senha do banco
$db   = 'game_rpg_carros'; // Nome do seu banco de dados

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["status" => "erro", "msg" => "Falha na conexão DB"]));
}

// Recebe o método (GET, POST, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

// --- 2. LISTAR CARROS (GET) ---
if ($method === 'GET') {
    $sql = "SELECT * FROM modelos_carros ORDER BY id DESC";
    $result = $conn->query($sql);
    
    $carros = [];
    while($row = $result->fetch_assoc()) {
        $carros[] = $row;
    }
    echo json_encode(["status" => "sucesso", "dados" => $carros]);
    exit;
}

// --- 3. SALVAR NOVO CARRO (POST) ---
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Proteção básica contra SQL Injection
    $nome = $conn->real_escape_string($data['nome']);
    $marca = $conn->real_escape_string($data['marca']);
    $ano = (int)$data['ano_modelo'];
    $preco = (float)$data['preco'];
    $img = $conn->real_escape_string($data['img_url']);
    
    // Specs
    $tracao = $conn->real_escape_string($data['tracao']);
    $motor = $conn->real_escape_string($data['motor']);
    $aspiracao = $conn->real_escape_string($data['aspiracao']);
    $potencia = (int)$data['potencia_base'];
    $peso = (int)$data['peso_base'];
    $arranque = (float)$data['arranque_base'];
    
    // Gameplay & Visual
    $vel = (int)$data['velocidade'];
    $acc = (int)$data['aceleracao'];
    $tanque = (int)$data['tanque_max'];
    $consumo = (float)$data['consumo'];
    $placa = $conn->real_escape_string($data['layout_placa']); // Novo campo
    $historia = $conn->real_escape_string($data['historia']);

    $sql = "INSERT INTO modelos_carros 
            (nome, marca, ano_modelo, preco, imagem, tracao, motor, aspiracao, potencia_base, peso_base, arranque_base, velocidade_max, aceleracao, tanque_max, consumo, layout_placa, historia)
            VALUES 
            ('$nome', '$marca', '$ano', '$preco', '$img', '$tracao', '$motor', '$aspiracao', '$potencia', '$peso', '$arranque', '$vel', '$acc', '$tanque', '$consumo', '$placa', '$historia')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "sucesso", "msg" => "Carro criado!"]);
    } else {
        echo json_encode(["status" => "erro", "msg" => $conn->error]);
    }
    exit;
}

// --- 4. DELETAR CARRO (DELETE) ---
if ($method === 'DELETE') {
    // Pega o ID da URL (?id=1)
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id > 0) {
        $sql = "DELETE FROM modelos_carros WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "sucesso"]);
        } else {
            echo json_encode(["status" => "erro", "msg" => $conn->error]);
        }
    }
    exit;
}
?>