Uso: gfphp -m [d|f|regex] -p /caminho/do/diretorio -pattern regex

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

                (Busca em um unico arquivo pelo pattern servers)
