<?php

class PlanoModel {

    // Método para obter os dados dos planos a partir do arquivo JSON "plans.json"
    public function getPlanos() {
        $conteudoArquivo = file_get_contents('plans.json');
        $dados = json_decode($conteudoArquivo, true);
        return $dados;
    }

    // Método para obter os dados dos preços a partir do arquivo JSON "prices.json"
    public function getPrecos() {
        $conteudoArquivo = file_get_contents('prices.json');
        $dados = json_decode($conteudoArquivo, true);
        return $dados;
    }

}