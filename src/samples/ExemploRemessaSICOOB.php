<?php
/*
 * CnabPHP - Geração de arquivos de remessa e retorno em PHP
*
* LICENSE: The MIT License (MIT)
*
* Copyright (C) 2013 Ciatec.net
*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this
* software and associated documentation files (the "Software"), to deal in the Software
* without restriction, including without limitation the rights to use, copy, modify,
* merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
* permit persons to whom the Software is furnished to do so, subject to the following
* conditions:
*
* The above copyright notice and this permission notice shall be included in all copies
* or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
* INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
* PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
* HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
* OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
* SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/



namespace CnabPHP\samples;
require_once ("../../vendor/autoload.php");

use \CnabPHP\Remessa;

$arquivo = new Remessa(756,'cnab240',array(

		//Informações da emrpesa recebedora
		'tipo_inscricao'  	=>	'2', // 1 para cpf, 2 cnpj
		'numero_inscricao'	=>	'27.449.872/0001-98', // seu cpf ou cnpj completo
		'agencia'       	=>	'3046', // agencia sem o digito verificador
		'agencia_dv'    	=>	'5', // somente o digito verificador da agencia
		'conta'         	=> 	'25151', // número da conta
		'conta_dv'     		=> 	'3', // digito da conta
		'nome_empresa' 		=>	"Avelar e Cordeiro Ltda-ME", // seu nome de empresa
		'numero_sequencial_arquivo'	=>	'0000001',

		'codigo_beneficiario'	=> '30638', // codigo fornecido pelo banco
		'codigo_beneficiario_dv'=> '0', // codigo fornecido pelo banco

		'situacao_arquivo' =>'T' // use T para teste e P para produção
));
$lote  = $arquivo->addLote(array('tipo_servico'=> '1')); // tipo_servico  = 1 para cobrança registrada, 2 para sem registro

$lote->inserirDetalhe(array(
		//Registro 3P Dados do Boleto
		'nosso_numero'      => '1800001', // numero sequencial de boleto
		//'nosso_numero_dv'   =>	1, // pode ser informado ou calculado pelo sistema
		'parcela' 			=>	'01',
		'modalidade'		=>	'1',
		'tipo_formulario'	=>	'4',
		'codigo_carteira'   =>	'1', // codigo da carteira
		'carteira'   		=>	'1', // codigo da carteira
		'seu_numero'        =>	"DEV180001",// se nao informado usarei o nosso numero
		'data_vencimento'   =>	'2018-03-30', // informar a data neste formato
		'valor'             =>	'3.00', // Valor do boleto como float valido em php
		'cod_emissao_boleto'=>	'2', // tipo de emissao do boleto informar 2 para emissao pelo beneficiario e 1 para emissao pelo banco
		'especie_titulo'    => 	"DM", // informar dm e sera convertido para codigo em qualquer laytou conferir em especie.php
		'data_emissao'      => 	'2018-03-07', // informar a data neste formato
		'codigo_juros'		=>	'2', // Taxa por mês
		'data_juros'   	  	=> 	'2018-03-30', // data dos juros, mesma do vencimento
		'vlr_juros'         => 	'0000000000002.00', // Valor do juros de 2% ao mês
		'codigo_desconto'	=>	'0',
		'data_desconto'     => 	'2018-03-30', // informar a data neste formato
		'vlr_desconto'      => 	'0', // Valor do desconto
		'vlr_IOF'			=> 	'0',
		'vlr_abatimento'	=> 	'0',
		'identificacao_contrato'	=>	"Prep. OAB 984958549",
		'protestar'         => 	'1', // 1 = Protestar com (Prazo) dias, 3 = Devolver após (Prazo) dias
		'prazo_protesto'    => 	'90', // Informar o numero de dias apos o vencimento para iniciar o protesto


		// Registro 3Q [PAGADOR]
		'tipo_inscricao'    => '1', //campo fixo, escreva '1' se for pessoa fisica, 2 se for pessoa juridica
		'numero_inscricao'  => '042.720.316-35',//cpf ou ncpj do pagador
		'nome_pagador'      => "Elias da Cunha Alves", // O Pagador é o cliente, preste atenção nos campos abaixo
		'endereco_pagador'  => 'Rua Belvedere, 287',
		'bairro_pagador'    => 'Bairro Queluz',
		'cep_pagador'       => '36400-000', // com hífem
		'cidade_pagador'    => 'Conselheiro Lafaiete',
		'uf_pagador'        => 'MG',

		// Registro 3R Multas, descontos, etc
		'codigo_multa'		=> '2', //2 - valor percentual
		'data_multa'        => '2018-03-30', // mesma data do vencimento
		'vlr_multa'         => '0000000000000.03', // valor da multa 0,03%/d


		// Registro 3S3 Mensagens a serem impressas
		'mensagem_sc_1' 	=> "Após venc. Mora 0,03%ad/multa2,00%",
		'mensagem_sc_2' 	=> "Não Conceder Desconto",
		'mensagem_sc_3' 	=> "Sujeito a protesto após o venc.",
		'mensagem_sc_4' 	=> "VelvetTux Soluções em Sistemas",

));

$remessa = utf8_decode($arquivo->getText()); // observar a header do seu php para não gerar comflitos de codificação de caracteres;

/* Função que pega o nome das pastas de acordo com o número do ano
 * Caso as pastas não existam, serão criadas.
 * Os arquivos de remessa serão organizados em ano/mês
*/
function verificaPastas() {
	date_default_timezone_set('America/Sao_Paulo');
	$base_dir = dir('./data/remessas/');

	if (!is_dir($base_dir->path.date('Y').'/'.date('m').'/')){
		mkdir ($base_dir->path.date('Y'), 0755);
		mkdir($base_dir->path.date('Y').'/'.date('m'), 0755);
	};
	$base_dir = dir($base_dir->path.date('Y').'/'.date('m').'/');
	//Retorna o caminho para guardar o arquivo
	return $base_dir;
}

// Grava o arquivo
file_put_contents(verificaPastas()->path.$arquivo->getName(), $remessa);
verificaPastas()->close();
echo $remessa;

?>
