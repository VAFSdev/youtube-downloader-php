<?php
    /**
     * Espera do JS
     * 'validar' = true
     */

    /**
     * valida acesso na plataforma. Tenta conseguir a versão do YT-DLP instalada.
     */
    headers_json();

    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        body_json([
            'validar' => false,
            'erro' => '[VALIDAR] Método de request inválido'
        ]);
    }

    $dados = json_decode(file_get_contents("php://input"), true);

    if(!isset($dados['validar']) || $dados['validar'] != true){
        body_json([
            'validar' => false,
            'erro' => '[VALIDAR] Estrutura JSON inválida'
        ]);
        exit;
    }
        $version = $ytdlp->get_version();

        if($version == false){
            body_json([
                'validar' => false,
                'erro' => '[VALIDAR] Falha ao obter a versão do YT-DLP'
            ]);
        }

        body_json([
            'validar' => true,
            'versao' => $version
        ]);
    


?>