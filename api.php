<?php


// Incluir as classes definidas anteriormente
require_once 'classes.php';

// Função para ler o arquivo JSON e retornar um array de objetos
function lerArquivoJSON($arquivo)
{
    $conteudo = file_get_contents($arquivo);
    $json = json_decode($conteudo);
    return $json;
}

// Ler o arquivo de planos
$planosJSON = lerArquivoJSON('db/plans.json');
$planos = array();
foreach ($planosJSON as $planoJSON) {
    $plano = new Plano();
    $plano->registro = $planoJSON->registro;
    $plano->codigo = $planoJSON->codigo;
    $plano->nome = $planoJSON->nome;
    $planos[] = $plano;
}

// Ler o arquivo de preços
$precosJSON = lerArquivoJSON('db/prices.json');
$precos = array();
foreach ($precosJSON as $precoJSON) {
    $preco = new Preco();
    $preco->codigo = $precoJSON->codigo;
    $preco->minimo_vidas = $precoJSON->minimo_vidas;
    $preco->faixa1 = $precoJSON->faixa1;
    $preco->faixa2 = $precoJSON->faixa2;
    $preco->faixa3 = $precoJSON->faixa3;
    $precos[] = $preco;
}
function receberDadosDoUsuario()
{

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }
    return $_POST;
}

$dadosDoUsuario = receberDadosDoUsuario();
function calcularPrecoPlano($dadosDoUsuario, $planos, $precos)
{
    $planoEscolhido = null;
    // Encontrar o registro do plano escolhido na tabela de planos
    foreach ($planos as $plano) {
        if ($plano->registro === $dadosDoUsuario['registro']) {
            $planoEscolhido = $plano;
            break;
        }
    }

    // Verificar se o registro do plano escolhido foi encontrado
    if ($planoEscolhido === null) {
        return 'Registro do plano escolhido não encontrado.';
    }

    // Calcular o preço para cada beneficiário
    $precoBeneficiarios = array();
    foreach ($dadosDoUsuario['beneficiarios'] as $beneficiario) {
        $idade = $beneficiario['idade'];

        // Encontrar o preço correspondente à faixa de idade do beneficiário
        $preco = 0;
        if ($idade <= 17) {
            $preco = $precos[$planoEscolhido->codigo]->faixa1;
        } elseif ($idade <= 40) {
            $preco = $precos[$planoEscolhido->codigo]->faixa2;
        } else {
            $preco = $precos[$planoEscolhido->codigo]->faixa3;
        }

        $precoBeneficiarios[] = array(
            'nome' => $beneficiario['nome'],
            'idade' => $idade,
            'preco' => $preco
        );
    }

    // Calcular o preço total do Plano escolhido
    $precoTotal = 0;
    foreach ($precoBeneficiarios as $beneficiario) {
        $precoTotal += $beneficiario['preco'];
    }

    return array(
        'plano_escolhido' => $planoEscolhido,
        'preco_beneficiarios' => $precoBeneficiarios,
        'preco_total' => $precoTotal
    );
}

$resultadoCalculo = calcularPrecoPlano($dadosDoUsuario, $planos, $precos);


function salvarPropostaJSON($resultadoCalculo, $dadosDoUsuario)
{
    $propostaExistente = array();
    if (file_exists('proposta.json')) {
        $jsonExistente = file_get_contents('proposta.json');
        $propostaExistente = json_decode($jsonExistente, true);
    }

    // Acrescentar a nova proposta aos dados existentes
    $novaProposta = array(
        'plano_escolhido' => $resultadoCalculo['plano_escolhido']->nome,
        'preco_beneficiarios' => $resultadoCalculo['preco_beneficiarios'],
        'preco_total' => $resultadoCalculo['preco_total'],
        'dados_usuario' => $dadosDoUsuario
    );

    $propostaExistente[] = $novaProposta;

    // Codificar novamente todo o conteúdo para JSON
    $jsonProposta = json_encode($propostaExistente, JSON_PRETTY_PRINT);

    // Escrever o arquivo atualizado
    file_put_contents('proposta.json', $jsonProposta);
}

// Salvar a proposta em proposta.json
salvarPropostaJSON($resultadoCalculo, $dadosDoUsuario);
echo json_encode($resultadoCalculo);
