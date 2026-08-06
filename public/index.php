<?php 

    /**
     * obter rota atual
     */

    /**
     * Summary of headers_json
     * Monta um headers geral para padronizar chamadas de API
     * @return void
     */
    function headers_json(){
         header('Content-Type: application/json; charset=utf-8');
         header('X-Content-Type-Options: nosniff');
         header('X-Frame-Options: DENY');
         header('Referrer-Policy: strict-origin-when-cross-origin');
         header('Cache-Control: no-store, no-cache, must-revalidate');
         header('Pragma: no-cache');
    }

    /**
     * Summary of body_json
     * Monta o body do JSON com base em uma array
     * @param array $data
     * @return never
     */
    function body_json(array $data){
        echo json_encode($data);
        exit;
    }



    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); //Rota do navegador

    $rotas = [
        '/' => __DIR__ . "/../src/view/main/main.php",
        '/Controller/Validar' => __DIR__ . "/../src/controller/validation/validateController.php",
        '/Controller/GetInfo' => __DIR__ . "/../src/controller/validation/getinfoController.php"
    ];

    if(array_key_exists($url, $rotas)){
        require_once __DIR__ . "/../src/services/YTDLPServices.php";
         try{
            $ytdlp = new YTDLPServices();
         }catch(\RuntimeException $e){
            body_json([
                'ytdlp' => false,
                'erro' => $e->getMessage()
            ]);
        }
        require_once $rotas[$url];
       
    }else{
        http_response_code(404);
        require_once __DIR__ . "/../src/view/404.php";
    }



?>