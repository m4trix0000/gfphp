#!/usr/bin/php
<?php

###########################################################
# Uma versão modificada do gf github.com/tomnomnom/gf     #
#                                                         #
#                                                         #
# Desenvolvido por Thiago F. Melo - <m4tri_x@hotmail.com> #
###########################################################

$PATH = dirname(__FILE__);
$Pattern_Dir = $PATH."/.gfphp/";

//Obtém os argumentos da linha de comando
$Parametros = $_SERVER['argv'];

// Remove o primeiro argumento, que é o nome do script
array_shift($Parametros);

// Inicializa as variáveis com valores padrão
$Mode = null;
$Path = null;
$Tag = null;
$ListPatterns = null;
$Output = null;

// Itera sobre os argumentos
foreach ($Parametros as $index => $arg) {

    switch ($arg) {

    case "-m":

        // Se o argumento for -m, pega o próximo argumento como o valor de modo
        $Mode = $Parametros[$index + 1];

    break;
    case "-p":

        // Se o argumento for -p, pega o próximo argumento como o valor de path
        $Path = $Parametros[$index + 1];

    break;
    case "-tag":

        $Tag = $Parametros[$index + 1];

    break;
    case "-pattern":

        $Pattern_Selected = $Parametros[$index + 1];

    break;
    case "-o":

        $Output = $Parametros[$index + 1];

    break;
    case "-l":

        $ListPatterns = 1;

    break;

    }

}

if(count($Patterns_Folder = importPatternsFromFiles($Pattern_Dir)) < 1 ){

    echo "\n\nNenhum Pattern foi encontrado no caminho: $Pattern_Dir\n\n";
    exit(1);

}


switch($Mode){

    case "d":

        //Busca dentro de um diretorio em todos os arquivos
        searchDirectory($Path, $Pattern_Selected, $Patterns_Folder, $Tag, $Output);

    break;
    default:

        if(is_file($Mode)){

            //Busca dentro de um unico arquivo
            searchFile($Path, $Mode, $Pattern_Selected, $Patterns_Folder, $Tag, $Output);


        }else{

            //Busca dentro de um diretorio por todos os arquivos que tenham no nome o regex informado
            searchFileName($Path, $Mode, $Pattern_Selected, $Patterns_Folder, $Tag, $Output);


        }

    break;
}

// Verifica se os argumentos obrigatórios estão presentes
if ($Mode === null || $Path === null) {
echo "\nUso: gfphp -m [d|f|regex] -p /caminho/do/diretorio -pattern regex

 -m (mode)

        d: /caminho/do/diretorio 
        f: /caminho/do/arquivo
        r: busca todos os arquivos que contenham o regex no nome

 -p (path)

        /caminho/do/diretorio
        (vazio ele busca recursivamente no diretorio atual)

 -tag

        substitui dados de parâmetros pela tag

 -pattern

        seleciona o filtro

 -o (output)

        salva a saida em arquivo, não inclui duplicados ;) disabled stdout

 -l 
        Listar patterns


 exemplo: gfphp -m crawler200 -p /home/ubuntu/ -tag FUZZ -pattern urls 

                (Busca por todos os arquivos que contenham no nome crawler200 dentro do diretório /home/ubuntu/
                e aplica o pattern urls, substituindo dados do parâmetro por FUZZ)

          gfphp -m /caminho/do/arquivo/file.txt -p /home/ubuntu/ -pattern servers

                (Busca em um unico arquivo pelo pattern servers)\n

 Patterns: ".implode("\n           ", array_keys($Patterns_Folder))." \n\n";

    exit(1);
}

// Função para importar os patterns de arquivos .json
function importPatternsFromFiles($directory) {

    $patterns = array();

    if(is_dir($directory)){

        $files = scandir($directory);
        foreach ($files as $file) {

            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {

                include($directory . DIRECTORY_SEPARATOR . $file);

                $patterns = array_merge($patterns, $pattern);

                /*$fileName = pathinfo($file, PATHINFO_FILENAME);
                $filePath = $directory . DIRECTORY_SEPARATOR . $file;
                $jsonData = file_get_contents($filePath);
                $data = json_decode($jsonData, true);

                if (isset($data['patterns'])) {

                    $formattedPatterns = formatPatternsForPregMatch($data['patterns']);
                    $patterns[$fileName] = $formattedPatterns;

                }
                */
            }
        }
    }
    return $patterns;
}

function formatPatternsForPregMatch($originalPatterns) {

    $formattedPatterns = [];

    foreach ($originalPatterns as $pattern) {
        //$formattedPatterns[] = '/' . preg_quote($pattern, '/') . '/';
        //$formattedPatterns[] = '/'.preg_quote($pattern, '/').'/i';
        $formattedPatterns[] = '/'.$pattern.'/i';
    }

    return $formattedPatterns;
}

function searchDirectory($directory, $Pattern_Selected, $patterns, $Tag, $Output) {

    if(is_dir($directory)){

        // Abre o diretório
        $dir = opendir($directory);

        // Loop pelos arquivos no diretório
        while (($file = readdir($dir)) !== false) {

            $filePath = $directory . '/' . $file;

            // Ignora . e ..
            if ($file == '.' || $file == '..') {

                continue;
            }

            // Verifica se é um diretório
            if (is_dir($filePath)) {

                // Se for, chama a função recursivamente
                searchDirectory($filePath, $Pattern_Selected, $patterns, $Tag, $Output);

            } else {

                // Se for um arquivo, verifica os padrões
                searchPatternsInFile($file, $filePath, $Pattern_Selected, $patterns, $Tag, $Output);
            }
        }

        // Fecha o diretório
        closedir($dir);
    }
}

// Função para pesquisar em um arquivo específico
function searchFile($filePath, $Pattern_Selected, $patterns, $Tag, $Output) {

    if (file_exists($filePath)) {

        searchPatternsInFile(basename($filePath), $filePath, $Pattern_Selected, $patterns, $Tag, $Output);

    } else {

        echo "Arquivo não encontrado: $filePath";

    }

}

// Função para pesquisar padrões no nome do arquivo
function searchFileName($directory, $filePath, $Pattern_Selected, $patterns, $Tag, $Output) {

    $Mode = $filePath;

    // Abre o diretório
    if(is_dir($directory)){

        $dir = opendir($directory);
        while (($file = readdir($dir)) !== false) {

            $filePath = $directory . '/' . $file;

            // Ignora . e ..
            if ($file == '.' || $file == '..') {

                continue;
            }

            // Verifica se é um arquivo
            if (!is_dir($filePath)) {

                if (preg_match("/$Mode/", $filePath)) {

                    //Se for, verifica os padrões no nome do arquivo
                    searchPatternsInFile($file, $filePath, $Pattern_Selected, $patterns, $Tag, $Output);
                }

            }

        }
        closedir($dir);
    }
}


// Função para pesquisar padrões em um arquivo
function searchPatternsInFile($fileName, $filePath, $Pattern_Selected, $patterns, $replaceParams = null, $Output) {

    //Valida se existe o -pattern VALOR dentro dos patterns identificados
    if(isset($Pattern_Selected) AND array_key_exists($Pattern_Selected, $patterns)){

        $patterns = $patterns[$Pattern_Selected];

    }else{

        echo "\n\nNenhum Pattern com esse nome foi encontrado\n\n";
        exit(1);
    }

    $fileContents = file_get_contents($filePath);

    $lines = explode("\n", $fileContents); // Dividir o conteúdo do arquivo em linhas

    foreach ($patterns as $pattern) {

        if (preg_match_all($pattern, $fileContents, $matches, PREG_OFFSET_CAPTURE)) {

            // Iterar sobre os matches encontrados
            foreach ($matches[0] as $match) {

                // Calcular o número da linha
                $lineNumber = 1; // Inicializar o número da linha
                $matchPosition = $match[1]; // Posição do match no conteúdo do arquivo

                foreach ($lines as $line) {

                    if ($matchPosition >= 0 && $matchPosition < strlen($line)) {

                        // Obter o contexto (50 caracteres anteriores e posteriores)
                        $start = max(0, $matchPosition - 0);
                        $context = substr($line, $start, 200);
                        break;
                    }

                    $matchPosition -= strlen($line) + 1; // Subtrai o comprimento da linha mais 1 (para contar a quebra de linha)
                    $lineNumber++; // Incrementar o número da linha
                }

                // Substituir parâmetros da URL por "FUZZ", se necessário
                $newContext = $context;

                if (strlen($replaceParams) > 1) {

                    $newContext = preg_replace('/\b(\w+)=([^&\s]+)/', '$1=FUZZ', $context);
                }

                // Registro gerado pelo regex
                $Resultado = "Arquivo: $fileName, Linha: $lineNumber Contexto: $newContext\n\n";

                // Exibir o resultado
                if(strlen($Output) > 1){

                    // Verifica se o arquivo existe
                    if (file_exists($Output)) {

                        // Verifica se o conteúdo já está presente
                        $conteudoExiste = false;

                        $handle = fopen($Output, "r");
                        while (!feof($handle)) {

                            $linha = fgets($handle);

                            // Verifica se a linha contém o contexto
                            if (strpos($linha, 'Contexto:') !== false) {

                                // Obtém a substring correspondente ao conteúdo após o contexto
                                $conteudoLinha = substr($linha, strpos($linha, 'Contexto:') + strlen('Contexto:'));

                                // Remove espaços em branco extras
                                $conteudoLinha = trim($conteudoLinha);

                                // Verifica se o conteúdo da linha corresponde ao conteúdo desejado
                                if ($conteudoLinha === $newContext) {

                                    $conteudoExiste = true;
                                    break;

                                }

                            }

                        }

                        fclose($handle);

                        // Se o conteúdo não existe, adiciona ao arquivo
                        if (!$conteudoExiste) {

                            // Abre o arquivo para escrita (append)
                            $arquivoAberto = fopen($Output, 'a');

                            // Escreve o conteúdo no arquivo
                            fwrite($arquivoAberto, $Resultado);

                            // Fecha o arquivo
                            fclose($arquivoAberto);

                        }


                    } else {

                        // Se o arquivo não existe, cria um novo e adiciona o conteúdo
                        file_put_contents($Output, $Resultado);

                    }

                }

                if(!strlen($Output) > 1){

                    echo $Resultado;
                }
            }
        }
    }
}

?>