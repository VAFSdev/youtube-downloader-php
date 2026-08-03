# PHP Video Downloader (yt-dlp + FFmpeg)

Um downloader de vídeos desenvolvido em **PHP**, utilizando o **yt-dlp** e o **FFmpeg** para baixar vídeos do YouTube em formato **MP4**, com seleção automática da melhor qualidade disponível até uma resolução máxima definida pelo usuário (1080p por padrão).

Além do download, o projeto utiliza `proc_open()` para acompanhar o progresso do processo em tempo real, permitindo a criação de barras de progresso ou interfaces web.

---

# Recursos

* Download de vídeos do YouTube
* Seleção automática da melhor qualidade disponível
* Download em MP4
* Download separado de vídeo e áudio com mesclagem automática pelo FFmpeg
* Criação automática da pasta de destino
* Leitura do progresso em tempo real
* Informações de velocidade de download
* Estimativa de tempo restante (ETA)
* Código desenvolvido inteiramente em PHP

---

# Requisitos

## PHP

* PHP 8.1 ou superior

Verifique sua versão:

```bash
php -v
```

---

## yt-dlp

Instale o yt-dlp e certifique-se de que ele esteja disponível no PATH.

Link para baixar:

```bash
https://github.com/yt-dlp/yt-dlp
```

Verifique a instalação:

```bash
yt-dlp --version
```

---

## FFmpeg

O FFmpeg é utilizado para unir o vídeo e o áudio baixados separadamente.

Link para baixar (windows build):

```bash
https://www.gyan.dev/ffmpeg/builds/ffmpeg-git-essentials.7z
```

Verifique:

```bash
ffmpeg -version
```

---

## Node.js (Recomendado)

As versões atuais do yt-dlp utilizam um runtime JavaScript para obter algumas informações do YouTube.

Instale o Node.js LTS.

Verifique:

```bash
node -v
```

Caso necessário, utilize:

```bash
yt-dlp --js-runtimes node
```

---

# Instalação

Clone o repositório:

```bash
git clone https://github.com/VAFSdev/youtube-downloader-php.git
```

Entre na pasta:

```bash
cd SEU_REPOSITORIO
```

---

# Como executar

Execute:

```bash
php download.php
```

Digite a URL do vídeo:

```text
https://www.youtube.com/watch?v=XXXXXXXXXXX
```

O programa irá:

1. Obter as informações do vídeo.
2. Criar a estrutura de download.
3. Selecionar automaticamente a melhor qualidade disponível.
4. Baixar o vídeo.
5. Baixar o áudio.
6. Mesclar ambos em um único arquivo MP4.
7. Exibir o progresso do download em tempo real.

---

# Qualidade do vídeo

A resolução máxima pode ser alterada modificando a variável:

```php
$qualidade = 1080;
```

Exemplos:

```php
$qualidade = 720;
```

```php
$qualidade = 1440;
```

```php
$qualidade = 2160;
```

O yt-dlp sempre escolherá a melhor qualidade disponível até esse limite.

---

# Estrutura do projeto

```text
.
├── downloads/
├── download.php
├── README.md
```

Após um download:

```text
downloads/
└── Nome do Vídeo/
    └── Nome do Vídeo.mp4
```

---

# Funcionamento

O projeto utiliza dois comandos principais do yt-dlp.

## 1. Obtenção de informações

```bash
yt-dlp -J URL
```

O comando retorna um JSON contendo informações como:

* título
* duração
* autor
* formatos disponíveis
* miniaturas

Essas informações são utilizadas para organizar o download.

---

## 2. Download

O download é realizado utilizando:

```bash
yt-dlp -f "bv*[height<=1080]+ba"
```

Onde:

* `bv` → melhor vídeo
* `ba` → melhor áudio
* `height<=1080` → limita a resolução máxima
* `--merge-output-format mp4` → gera um arquivo MP4 ao final

---

# Acompanhamento do progresso

O projeto utiliza `proc_open()` para iniciar o processo do yt-dlp.

Isso permite acompanhar em tempo real:

* porcentagem concluída
* velocidade do download
* tamanho do arquivo
* tempo restante (ETA)

Essas informações podem ser utilizadas para construir interfaces gráficas ou barras de progresso em aplicações web.

---

# Dependências

* PHP
* yt-dlp
* FFmpeg
* Node.js (recomendado)

---

# Licença

Este projeto é distribuído sob a licença MIT. Consulte o arquivo `LICENSE` para mais informações.
