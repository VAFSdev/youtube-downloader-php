<?php
    echo "Digite a URL do vídeo: \n";
    $url = trim(fgets(STDIN));

    if($url == ''){
        echo "/n Url inválida";
        exit;
    }

    exec(
        'yt-dlp -J ' . escapeshellarg($url),
        $output,
        $status
    );

    if ($status !== 0) {
        exit("Não foi possível obter informações do vídeo.\n");
    }

    $json = implode("\n", $output);

    $videoInfo = json_decode($json, true);

    $titulo = preg_replace('/[\\\\\/:*?"<>|]/', '', $videoInfo['title']);

    $pasta = __DIR__ . "/downloads/$titulo";

    $outputFile = $pasta . '/%(title)s.%(ext)s';

    $qualidade = 1080; //Altere o valor para mudar a resolução
    $formato_saida = 'mp4'; //Altere o tipo de extensão

    $filtro = "bv*[height<=$qualidade]+ba";

    $cmd = [

        'yt-dlp',

        '--newline',

        '-f',
        $filtro,

        '--merge-output-format',
        $formato_saida,

        '-o',
        $outputFile,

        $url

    ];

    $descriptors = [
        0 => ["pipe", 'r'], //entrada
        1 => ["pipe", 'w'], //output
        2 => ["pipe", 'w'] //erros
    ];

    $process = proc_open($cmd, $descriptors, $pipes);

    if (!is_resource($process)) {

        exit("Erro ao iniciar yt-dlp.");

    }

    $largura = 50;
    while (!feof($pipes[1])) {

        $linha = fgets($pipes[1]);

       if(preg_match('/(\d+(?:\.\d+)?)%\s+of\s+(.+?)\s+at\s+(.+?)\s+ETA\s+(.+)/',$linha,$m)) {

            $porcentagem = (float)$m[1];
            $velocidade = $m[3];
            $tamanho = $m[4];

            $preenchido = (int)(($porcentagem / 100) * $largura);

            $barra =
                str_repeat('█', $preenchido) .
                str_repeat('░', $largura - $preenchido);


            echo "Arquivo sendo baixado: {$titulo}\n";
            echo "\r[$barra] {$porcentagem}% | Velocidade: {$velocidade} | Tamanho: {$tamanho}";
        }

    }
    $erro = stream_get_contents($pipes[2]);

    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    proc_close($process);
    exec('cls');
    
?>