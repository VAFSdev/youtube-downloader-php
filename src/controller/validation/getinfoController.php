<?php 
    headers_json();

    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        body_json([
            'status' => false,
            'erro' => '[GET-INFO] Método de Request inválido'
        ]);
    }

    /**
     * PHP Espera:
     * 'url'
     */

    $dados = json_decode(file_get_contents("php://input"), true);

    if(isset($dados['url'])){
        $get_info = $ytdlp->get_info(trim($dados['url']));
        if(isset($get_info['status'])){
            body_json([
                'status' => false,
                'erro' => "[GET-INFO] {$get_info['error']}"
            ]);
        }else{
            body_json([
                'status' => true,
                'info' => $get_info
            ]);
        }
    }else{
        body_json([
            'status' => false,
            'erro' => '[GET-INFO] Estrutura de JSON inválida'
        ]);
    }

?>