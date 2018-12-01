<?php
ini_set('auto_detect_line_endings', true);

include 'utils.php';

$dir_in = 'in';
$out = 'out/saida.done.dat';

echo 'Diretório de entrada: ' . realpath($dir_in) . '<br />';
echo 'Diretório de saída: ' . realpath($out) . '<br />';

$arquivos = scandir($dir_in);

// d($arquivos);

$ignorar = ['.', '..'];
// $vendedores = [];
$clientes = [];
$folha_pagamento = 0;
$venda_mais_cara = 0;
// $venda_por_vendedor = [];
$id_venda_mais_cara = 0;
$pior_venda = 0;
$id_pior_vendedor = 0;
$qtd_vendedor = 0;
$qtd_clientes = 0;

foreach ($arquivos as $arquivo) {
    //ignorar arquivos da lista de  ignorados
    if (in_array($arquivo, $arquivo)) {
        continue;
    }

    //ignorar todos os arquivos diferentes de .dat
    $formato = end(explode('.', $arquivo));
    if ($formato != 'dat') {
        continue;
    }

    // dd(file_get_contents($dir_in . '/' . $arquivo));


    //começa o verdadeiro processamento
    $fh = fopen($dir_in . '/' . $arquivo, 'r');
    while ($linha = fgets($fh)) {
        $linha = onlyAsciiChars($linha);
        
        preg_match_all("/[^[\]]+/", $linha, $matches);
        //uasndo preg_split por causa do parametro PREG_SPLIT_NO_EMPTY que automaticamente remove as posições em branco
        $p1 = preg_split('/,/', $matches[0][0], null, PREG_SPLIT_NO_EMPTY);
        // d($matches[0]);
        $tipo = $p1[0];
        $id = $p1[1];
        
        //venda
        if ((int) $tipo == 3) {
            $abre_col = strpos($linha, '[');
            $fecha_col = strpos($linha, ']');

            $vendas = substr($linha, $abre_col+1, $fecha_col-$abre_col-1);

            //uasndo preg_split por causa do parametro PREG_SPLIT_NO_EMPTY que automaticamente remove as posições em branco
            // $nome = preg_split('/,/', trim($matches[0][2]), null, PREG_SPLIT_NO_EMPTY);

            //captura o nome, fazendo um substring da ultima vírgula até o final da string
            $nome = trim(substr($linha, strrpos($linha, ',')+1));
            
            //[Item ID­-Item Quantity-­Item Price]
            // dd($linha, $id, $vendas, $nome);

            //começa o processamento das vendas
            $vendas_linha = explode(',', $vendas);
            foreach ($vendas_linha as $vd) {
                $venda_info = explode('-', $vd);
                $venda_id = trim($venda_info[0]);
                $item_qtd = trim($venda_info[1]);
                $item_preco = (float) trim(str_replace(' ', '', $venda_info[2]));
                $total_venda = $item_qtd * $item_preco;

                if ($total_venda > $venda_mais_cara) {
                    $venda_mais_cara = $total_venda;
                    $id_venda_mais_cara = $venda_id;
                }

                // //soma as vendas por vendedores
                // if (!isset($venda_por_vendedor[$id])) {
                //     $venda_por_vendedor[$id] = 0;
                // }
                // $venda_por_vendedor[$id] += $total_venda;
                
                if ($pior_venda == 0 || $total_venda < $pior_venda) {
                    $pior_venda = $total_venda;
                    $id_pior_vendedor = $id;
                }
            }
        } elseif ($tipo == 1) {//vendedores
            // $nome = $p1[2];
            $salario = (float) $p1[3];
            //vou assumir que há apenas uma linhas por vendedor,
            //portanto, não traterei uma potencial existencia de mais de uma linha para um mesmo vendedor
            $qtd_vendedor++;

            // if (!isset($vendedores[$id])) {
            // $vendedores[$id] = ['Nome' => $nome, 'Salario' => $salario];
            $folha_pagamento += $salario;
        // }
        } elseif ($tipo == 2) {
            $qtd_clientes++;
        }
    }
}
$media_salarial = round($folha_pagamento / $qtd_vendedor,2);
//exporta o arquivo
echo 'Venda mais cara ($ / Id): R$ ' . $venda_mais_cara . ' / ' . $id_venda_mais_cara . '<br />';
echo 'Média Salarial: ' . $media_salarial . '<br />';
echo 'Pior venda: ' . $id_pior_vendedor . '<br />';
echo 'Qtd. de vendedores: ' . $qtd_vendedor . '<br />';
echo 'Qtd. de clientes: ' . $qtd_clientes . '<br />';

$fp = fopen($out, 'w');
fwrite($fp, implode(',', [$qtd_clientes,$qtd_vendedor,$media_salarial,$venda_mais_cara,$id_venda_mais_cara,$id_pior_vendedor]));
fclose($fp);
