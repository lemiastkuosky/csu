<?php
// api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permite que o admin acesse

$arquivo = 'dados.json';

// 1. Se recebermos dados (POST), vamos salvar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pega os dados enviados pelo Javascript
    $dadosRecebidos = file_get_contents('php://input');
    
    // Verifica se é um JSON válido
    if (json_decode($dadosRecebidos) != null) {
        // Salva no arquivo
        file_put_contents($arquivo, $dadosRecebidos);
        echo json_encode(["status" => "sucesso", "mensagem" => "Dados salvos!"]);
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "JSON inválido"]);
    }
    exit;
}

// 2. Se for apenas um acesso normal (GET), vamos ler e mostrar os dados
if (file_exists($arquivo)) {
    echo file_get_contents($arquivo);
} else {
    // Se o arquivo não existir, cria um padrão
    echo json_encode(["carros" => [], "pecas" => [], "config" => []]);
}
?>