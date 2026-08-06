<?php

    class YTDLPServices{
        private $version; //versão atual do yt-dlp instalado

        /**
         * Summary of __construct
         * O construtor da classe tenta validar o yt-dlp através da abertura de um processo para obter a versão do yt-dlp no sistema.
         * Utiliza proc_open e separa em pipes de stdin, stdout e stderr
         * @throws RuntimeException
         */
        public function __construct()
        {
            $cmd = "yt-dlp --version";
            $descriptors = [
                0 => ['pipe', 'r'], //stdin
                1 => ['pipe', 'w'], //stdout
                2 => ['pipe', 'w'] //stderr
            ];
            
            $process = proc_open($cmd, $descriptors, $pipes);
            if(is_resource($process)){
                $stdout = stream_get_contents($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);

                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);

                $exitcode = proc_close($process);

                if($exitcode !== 0){
                    throw new \RuntimeException("[YT-DLP] Erro ao processar versão. \n Mensagem de erro: {$stderr}");
                }

                $this->version = trim($stdout);
            }else{
                throw new \RuntimeException("[PROCESS] Falha ao abrir o processo e instância-lo");
            }
        }
        /**
         * Summary of get_version
         * Tenta capturar a versão do yt-dlp associada ao atributo
         * @return false caso não consiga capturar a versão. 
         * @return string com a versão do yt-dlp
         * @return bool|string
         */
        public function get_version():bool | string{
            return empty($this->version) ? false : $this->version;
        }

        private function run_process($_cmd, $_timeout=15){
            $descriptors = [
                0 => ['pipe', 'r'], //stdin
                1 => ['pipe', 'w'], //stdout
                2 => ['pipe', 'w'] //stderr
           ];

           $process = proc_open($_cmd, $descriptors, $pipes);
           if(!is_resource($process)){
                return [
                    'status' => false,
                    'error' => 'Não pode inicializar o processo'
                ];
           }

           fclose($pipes[0]);

           stream_set_blocking($pipes[1], false);

           $timeout = $_timeout; //segundos
           $start = time();
           $output = '';

           while(time() - $start < $timeout){
                $chunk = fread($pipes[1], 8192);
                if($chunk !== false){
                    $output .= $chunk;
                }
                $status = proc_get_status($process);

                if(!$status['running']){
                    break;
                }
                usleep(100000); //0.1 segundos
           }

           $status = proc_get_status($process);

           if($status['running']){
                proc_terminate($process); //mata se estiver rodando após o timeout
                fclose($pipes[1]);
                fclose($pipes[2]);
                return [
                    'status' => false,
                    'error' => 'timeout excedido'
                ];
           }

           fclose($pipes[1]);
           fclose($pipes[2]);

           proc_close($process);

           return [
            'status' => true,
            'output' => trim($output),
            'error' => ''
           ];
        }

        private function validate_url($_url){
            if(empty($_url) || !filter_var($_url, FILTER_VALIDATE_URL)){
                return [
                    'status' => false,
                    'error' => 'URL inválida'
                ];
            }

           $cmd = "yt-dlp --no-warnings --simulate --print extractor " . escapeshellarg($_url);
           $extractor = $this->run_process($cmd);

           if(!$extractor['status']){
                return [
                    'status' => $extractor['status'],
                    'error' => $extractor['error']
                ];
           }

           if(empty($extractor['output'])){
                return [
                    'status' => false,
                    'error' => 'Não conseguiu localizar um extrator válido'
                ];
           }

           return [
                'status' => true,
                'extractor' => $extractor['output']
           ];

        }

        private function is_playlist(string $output){
            $lines = array_filter(array_map('trim', explode("\n", trim($output))));
            return count($lines) > 1;
        }

        private function get_best_thumbnail(array $_thumbnails){
            $total_arrays = count($_thumbnails);
            $maior_resolucao = 0;
            $melhor_thumbnail = null;
            for($i = 0; $i < $total_arrays; $i++){
                $resolucao = (int)($_thumbnails[$i]['height'] ?? 0);

                if($resolucao > $maior_resolucao){
                    $maior_resolucao = $resolucao;
                    $melhor_thumbnail = $_thumbnails[$i]['url'];
                }
            }

            return $melhor_thumbnail;
        }

        private function parse_playlist(array $lines){
            $items = [];
            $total_duracao = 0;

            foreach($lines as $line){
                $video = json_decode($line, true);

                if(!$video){ //JSON CORROMPIDO
                    continue;
                }

                $thumbnail = $this->get_best_thumbnail($video['thumbnails'] ?? []);
                $duration = $video['duration'] ?? 0;
                $total_duracao += $duration;

                $items[] = [
                    'title' => $video['title'] ?? 'Sem título',
                    'duration' => $duration,
                    'url' => $video['url'] ?? $video['webpage_url'] ?? null,
                    'thumbnail' => $thumbnail,
                    'channel' => $video['channel'] ?? $video['uploader'] ?? 'Desconhecido'
                ];
            }

            $playlist = json_decode($lines[0], true);
            return [
                'type' => 'playlist',
                'playlist_title' => $playlist['playlist_title'] ?? $playlist['title'] ?? "Sem título",
                'playlist_url' => $playlist['playlist_webpage_url'] ?? null,
                'playlist_uploader' => $playlist['playlist_uploader'] ?? null,
                'total_items' => $playlist['playlist_count'] ?? $playlist['n_entries'] ?? count($items),
                'total_duration' => $total_duracao,
                'thumbnail' => $items[0]['thumbnail'] ?? null,
                'items' => $items
            ];
        }

        private function parse_video(array $_output){
            $video = $_output;

            return [
                'title' => $video['title'] ?? 'Sem título',
                'duration' => $video['duration'] ?? 0,
                'url' => $video['url'] ?? $video['webpage_url'] ?? null,
                'thumbnail' => $this->get_best_thumbnail($_output['thumbnails'] ?? []),
                'channel' => $video['channel'] ?? $video['uploader'] ?? 'Desconhecido'
            ];
        }

        public function get_info($_url){

            $validar_url = $this->validate_url($_url);
            if(!isset($validar_url['extractor']) || $validar_url['status'] !== true){
                return [
                    'status' => false,
                    'error' => $validar_url['error']
                ];
            }

            $extractor = $validar_url['extractor'];

            $cmd = "yt-dlp --no-warnings --flat-playlist --dump-json " . escapeshellarg($_url);
            $info = $this->run_process($cmd, 60);

            if(!$info['status']){
                return [
                    'status' => false,
                    'error' => $info['error']
                ];
            }

            if($this->is_playlist($info['output'])){
                $lines = array_filter(array_map('trim',explode("\n", trim($info['output']))));
                $parse_playlist = $this->parse_playlist($lines);

                if(empty($parse_playlist)){
                    return [
                        'status' => false,
                        'error' => 'Falha ao parsear playlist'
                    ];
                }

                $parse_playlist['extractor'] = explode("\n", $extractor)[0];

                return $parse_playlist;

            }else{
                $video_json = json_decode($info['output'], true);

                if(!$video_json){
                    return [
                        'status' => false,
                        'error' => "Falha em decodificar JSON"
                    ];
                }
                $parse_video = $this->parse_video($video_json);
                if(empty($parse_video)){
                     return [
                        'status' => false,
                        'error' => 'Falha ao parsear video'
                    ];
                }
                $parse_video['extractor'] = $extractor;

                return $parse_video;
            }
            

        }
    }
    
?>