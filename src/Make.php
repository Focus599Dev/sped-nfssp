<?php 

namespace NFePHP\NFs\SP;

/**
 * Classe para construção de XML
 *
 * @category  NFePHP
 * @package   NFePHP\NFs\SP\
 * @copyright NFePHP Copyright (c) 2008-2017
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Marlon O. Borbosa
 * @link      https://github.com/Focus599Dev/sped-nfssp for the canonical source repository
 */

use NFePHP\Common\Strings;
use DOMElement;
use NFePHP\Common\Certificate;
use NFePHP\Common\DOMImproved as Dom;
use \stdClass;

class Make{

	/**
     * @var string
     */
	public $xml;

	/**
     * @var \NFePHP\Common\DOMImproved
     */
    public $dom;

    public $version = 1;

    protected $soapnamespaces = [
        // 'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance",
        // 'xmlns:xsd' => "http://www.w3.org/2001/XMLSchema",
        'xmlns'     => "http://www.prefeitura.sp.gov.br/nfe",
        // 'xmlns:tipos' => "http://www.prefeitura.sp.gov.br/nfe/tipos"
    ];

    /**
     * @var DOMElement
    */
	protected $Cabecalho;

    /**
     * @var DOMElement
    */
	protected $ChaveRPS;

    /**
     * @var DOMElement
    */
    protected $RPS;

     /**
     * Função construtora cria um objeto DOMDocument
     * que será carregado com o documento fiscal
     */
    public function __construct() {
        $this->dom = new Dom('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = false;
    } 

    public function clearDom(){

    	$this->dom = new Dom('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = false;
        $this->xml = null;

        return $this->dom;
    }

    public function monta(){

        $PedidoEnvioLoteRPS = $this->dom->createElement('PedidoEnvioRPS');

        foreach ($this->soapnamespaces as $key => $namespace) {
            
            $PedidoEnvioLoteRPS->setAttribute($key, $namespace);
        }

        $this->dom->appChild($PedidoEnvioLoteRPS, $this->Cabecalho, 'Falta tag "Cabecalho"');
        
        $this->dom->appChild($PedidoEnvioLoteRPS, $this->RPS, 'Falta tag "RPS"');

        $this->dom->appendChild($PedidoEnvioLoteRPS);

        $this->xml = $this->dom->saveXML();

        return true;
    }

    public function buildCabecalho($std){

		$possible = [
			'NumeroLote',
			'versao',
			'CpfCnpj',
			'InscricaoMunicipal',
			'QuantidadeRps',
            'CpfCnpj',
            'dtInicio',
            'dtFim',
            'ValorTotalServicos',
            'ValorTotalDeducoes',
            'transacao',
            'QtdRPS',
		];

        $std = $this->equilizeParameters($std, $possible);

        $Cabecalho = $this->dom->createElement("Cabecalho");

        $Cabecalho->setAttribute('xmlns', '');

        $Cabecalho->setAttribute('Versao', $this->version);

        $CPFCNPJRemetente = $this->dom->createElement("CPFCNPJRemetente");

    	$this->dom->addChild(
            $CPFCNPJRemetente,
            "CNPJ",
            Strings::replaceSpecialsChars(trim($std->CpfCnpj)),
            true,
			"CNPJ"
        );

        $this->dom->appChild($Cabecalho, $CPFCNPJRemetente, 'Falta tag "Cabecalho"');

        // $this->dom->addChild(
        //     $Cabecalho,
        //     "transacao",
        //     $std->transacao,
        //     false,
        //     "Informe se os RPS a serem substituídos por NFS-e farão parte de uma mesma transação. True - Os RPS só serão substituídos por NFS-e se não ocorrer nenhum evento de erro durante o processamento de todo o lote; False - Os RPS válidos serão substituídos por NFS-e, mesmo que ocorram eventos de erro durante processamento de outros RPS deste lote."
        // );

        // $this->dom->addChild(
        //     $Cabecalho,
        //     "dtInicio",
        //     $std->dtInicio,
        //     true,
        //     "Informe a data de início do período transmitido (AAAA-MM-DD)."
        // );

        // $this->dom->addChild(
        //     $Cabecalho,
        //     "dtFim",
        //     $std->dtFim,
        //     true,
        //     "Informe a data final do período transmitido (AAAA-MM-DD)."
        // );

        // $this->dom->addChild(
        //     $Cabecalho,
        //     "QtdRPS",
        //     $std->QtdRPS,
        //     true,
        //     "Informe o total de RPS contidos na mensagem XML."
        // );

        // $this->dom->addChild(
        //     $Cabecalho,
        //     "ValorTotalServicos",
        //     $std->ValorTotalServicos,
        //     true,
        //     "Informe o valor total dos serviços prestados dos RPS contidos na mensagem XML."
        // );

        // $this->dom->addChild(
        //     $Cabecalho,
        //     "ValorTotalDeducoes",
        //     $std->ValorTotalDeducoes,
        //     false,
        //     "Informe o valor total das deduções dos RPS contidos na mensagem XML."
        // );

        $this->Cabecalho = $Cabecalho;

        return $Cabecalho;

    }

    public function buildChaveRPS($std){

        $possible = [
			'InscricaoPrestador',
            'SerieRPS',
            'NumeroRPS',
		];

        $std = $this->equilizeParameters($std, $possible);

        $ChaveRPS = $this->dom->createElement('ChaveRPS');

        $this->dom->addChild(
            $ChaveRPS,
            "InscricaoPrestador",
            $std->InscricaoPrestador,
            true,
            "Inscrição municipal do prestador de serviços."
        );

        $this->dom->addChild(
            $ChaveRPS,
            "SerieRPS",
            $std->SerieRPS,
            false,
            "Série do RPS."
        );

        $this->dom->addChild(
            $ChaveRPS,
            "NumeroRPS",
            $std->NumeroRPS,
            true,
            "Número do RPS."
        );

        $this->ChaveRPS = $ChaveRPS;

        return $ChaveRPS;
    }

    public function buildRPS($std){

        $possible = [
			'CpfCnpj',
            'InscricaoMunicipalTomador',
            'InscricaoEstadualTomador',
            'RazaoSocialTomador',
            'EmailTomador',
            'TipoLogradouro',
            'Logradouro',
            'NumeroEndereco',
            'ComplementoEndereco',
            'Bairro',
            'Cidade',
            'UF',
            'CEP',
            'TipoRPS',
            'DataEmissao',
            'StatusRPS',
            'TributacaoRPS',
            'ValorServicos',
            'ValorDeducoes',
            'ValorPIS',
            'ValorCOFINS',
            'ValorINSS',
            'ValorIR',
            'ValorCSLL',
            'CodigoServico',
            'AliquotaServicos',
            'ISSRetido',
            'CPFCNPJIntermediario',
            'InscricaoMunicipalIntermediario',
            'ISSRetidoIntermediario',
            'EmailIntermediario',
            'ValorCargaTributaria',
            'PercentualCargaTributaria',
            'FonteCargaTributaria',
            'CodigoCEI',
            'MatriculaObra',
            'MunicipioPrestacao',
            'NumeroEncapsulamento',
            'ValorTotalRecebido',
            'Discriminacao',
		];

        $std = $this->equilizeParameters($std, $possible);

        $RPS = $this->dom->createElement('RPS');

        $RPS->setAttribute('xmlns', '');
        
        $this->dom->appChild($RPS, $this->ChaveRPS , 'Falta tag "ChaveRPS"');
        
        $this->dom->addChild(
            $RPS,
            "TipoRPS",
            $std->TipoRPS,
            true,
            "Informe o Tipo do RPS emitido."
        );

        $this->dom->addChild(
            $RPS,
            "DataEmissao",
            $std->DataEmissao,
            true,
            "Informe a Data de emissão do RPS."
        );

        $this->dom->addChild(
            $RPS,
            "StatusRPS",
            $std->StatusRPS,
            true,
            "Informe o Status do RPS."
        );

        $this->dom->addChild(
            $RPS,
            "TributacaoRPS",
            $std->TributacaoRPS,
            true,
            "Informe o tipo de tributação do RPS."
        );

        $this->dom->addChild(
            $RPS,
            "ValorServicos",
            $std->ValorServicos,
            true,
            "Informe o valor dos serviços prestados."
        );

        $this->dom->addChild(
            $RPS,
            "ValorDeducoes",
            $std->ValorDeducoes,
            true,
            "Informe o valor das deduções."
        );

        $this->dom->addChild(
            $RPS,
            "ValorPIS",
            $std->ValorPIS,
            false,
            "Informe o valor da retenção do PIS."
        );
        
        $this->dom->addChild(
            $RPS,
            "ValorCOFINS",
            $std->ValorCOFINS,
            false,
            "Informe o valor da retenção do COFINS."
        );

        $this->dom->addChild(
            $RPS,
            "ValorINSS",
            $std->ValorINSS,
            false,
            "Informe o valor da retenção do INSS."
        );

        $this->dom->addChild(
            $RPS,
            "ValorIR",
            $std->ValorIR,
            false,
            "Informe o valor da retenção do IR."
        );

        $this->dom->addChild(
            $RPS,
            "ValorCSLL",
            $std->ValorCSLL,
            false,
            "Informe o valor da retenção do CSLL."
        );

        $this->dom->addChild(
            $RPS,
            "CodigoServico",
            $std->CodigoServico,
            true,
            "Informe o código do serviço do RPS. Este código deve pertencer à lista de serviços."
        );

        $this->dom->addChild(
            $RPS,
            "AliquotaServicos",
            $std->AliquotaServicos,
            true,
            "Informe o valor da alíquota. Obs. O conteúdo deste campo será ignorado caso a tributação ocorra no município (Situação do RPS = T )."
        );

        $this->dom->addChild(
            $RPS,
            "ISSRetido",
            $std->ISSRetido,
            true,
            "Informe a retenção."
        );

        $CPFCNPJTomador = $this->dom->createElement('CPFCNPJTomador');

		if ($std->TaxIDType == 1){
        
            $std->CpfCnpj = substr($std->CpfCnpj, -11);

        }

        if ( strlen($std->CpfCnpj) >= 14){

            $this->dom->addChild(
                $CPFCNPJTomador,
                "CNPJ",
                $std->CpfCnpj,
                true,
                "CNPJ Tomador"
            );
        } else {

            $this->dom->addChild(
                $CPFCNPJTomador,
                "CPF",
                $std->CpfCnpj,
                true,
                "CPF Tomador"
            );
        }

        $this->dom->appChild( $RPS,  $CPFCNPJTomador , 'Falta tag "CPFCNPJTomador"');

        $this->dom->addChild(
            $RPS,
            "InscricaoMunicipalTomador",
            $std->InscricaoMunicipalTomador,
            false,
            "Informe a Inscrição Municipal do Tomador. ATENÇÃO: Este campo só deverá ser preenchido para tomadores estabelecidos no município de São Paulo (CCM). Quando este campo for preenchido, seu conteúdo será considerado como prioritário com relação ao campo de CPF/CNPJ do Tomador, sendo utilizado para identificar o Tomador e recuperar seus dados da base de dados da Prefeitura."
        );

        $this->dom->addChild(
            $RPS,
            "InscricaoEstadualTomador",
            $std->InscricaoEstadualTomador,
            false,
            "Informe a inscrição estadual do tomador. Este campo será ignorado caso seja fornecido um CPF/CNPJ ou a Inscrição Municipal do tomador pertença ao município de São Paulo."
        );

        $this->dom->addChild(
            $RPS,
            "RazaoSocialTomador",
            $std->RazaoSocialTomador,
            false,
            "Informe o Nome/Razão Social do tomador. Este campo é obrigatório apenas para tomadores Pessoa Jurídica (CNPJ). Este campo será ignorado caso seja fornecido um CPF/CNPJ ou a Inscrição Municipal do tomador pertença ao município de São Paulo."
        );

        if ($std->TipoLogradouro || $std->Logradouro || $std->NumeroEndereco || $std->ComplementoEndereco || $std->Bairro || $std->Cidade || $std->UF || $std->CEP ){
    
            $EnderecoTomador = $this->dom->createElement('EnderecoTomador');

            $this->dom->addChild(
                $EnderecoTomador,
                "TipoLogradouro",
                $std->TipoLogradouro,
                false,
                "Endereço tomador TipoLogradouro"
            );

            $this->dom->addChild(
                $EnderecoTomador,
                "Logradouro",
                $std->Logradouro,
                false,
                "Endereço tomador Logradouro"
            );

            $this->dom->addChild(
                $EnderecoTomador,
                "NumeroEndereco",
                $std->NumeroEndereco,
                false,
                "Endereço tomador NumeroEndereco"
            );

            $this->dom->addChild(
                $EnderecoTomador,
                "ComplementoEndereco",
                $std->ComplementoEndereco,
                false,
                "Endereço tomador ComplementoEndereco"
            );

            $this->dom->addChild(
                $EnderecoTomador,
                "Bairro",
                $std->Bairro,
                false,
                "Endereço tomador Bairro"
            );

            $this->dom->addChild(
                $EnderecoTomador,
                "Cidade",
                $std->Cidade,
                false,
                "Endereço tomador Cidade"
            );

            $this->dom->addChild(
                $EnderecoTomador,
                "UF",
                $std->UF,
                false,
                "Endereço tomador UF"
            );

            $this->dom->addChild(
                $EnderecoTomador,
                "CEP",
                $std->CEP,
                false,
                "Endereço tomador CEP"
            );

            $this->dom->appChild( $RPS,  $EnderecoTomador , 'Falta tag "CPFCNPJTomador"');

        }

        $this->dom->addChild(
            $RPS,
            "EmailTomador",
            $std->EmailTomador,
            false,
            "Informe o e-mail do tomador."
        );

        if ($std->CPFCNPJIntermediario){
           
            $CPFCNPJIntermediario = $this->dom->createElement("CPFCNPJIntermediario");

            $this->dom->addChild(
                $CPFCNPJIntermediario,
                "CNPJ",
                Strings::replaceSpecialsChars(trim($std->CPFCNPJIntermediario)),
                true,
                "CNPJ"
            );

            $this->dom->appChild($RPS, $CPFCNPJIntermediario, 'Falta tag "Cabecalho"');
        }

        $this->dom->addChild(
            $RPS,
            "InscricaoMunicipalIntermediario",
            $std->InscricaoMunicipalIntermediario,
            false,
            "Inscrição Municipal do intermediário de serviço."
        );

        $this->dom->addChild(
            $RPS,
            "ISSRetidoIntermediario",
            $std->ISSRetidoIntermediario,
            false,
            "Retenção do ISS pelo intermediário de serviço."
        );

        $this->dom->addChild(
            $RPS,
            "EmailIntermediario",
            $std->EmailIntermediario,
            false,
            "E-mail do intermediário de serviço."
        );

        $this->dom->addChild(
            $RPS,
            "Discriminacao",
            $std->Discriminacao,
            true,
            "Informe a discriminação dos serviços."
        );

        $this->dom->addChild(
            $RPS,
            "ValorCargaTributaria",
            $std->ValorCargaTributaria,
            false,
            "Valor da carga tributária total em R$."
        );

        $this->dom->addChild(
            $RPS,
            "PercentualCargaTributaria",
            $std->PercentualCargaTributaria,
            false,
            "Valor percentual da carga tributária."
        );

        $this->dom->addChild(
            $RPS,
            "FonteCargaTributaria",
            $std->FonteCargaTributaria,
            false,
            "Fonte de informação da carga tributária."
        );

        $this->dom->addChild(
            $RPS,
            "CodigoCEI",
            $std->CodigoCEI,
            false,
            "Código do CEI – Cadastro específico do INSS."
        );
        
        $this->dom->addChild(
            $RPS,
            "MatriculaObra",
            $std->MatriculaObra,
            false,
            "Código que representa a matrícula da obra no sistema de cadastro de obras."
        );

        $this->dom->addChild(
            $RPS,
            "MunicipioPrestacao",
            $std->MunicipioPrestacao,
            false,
            "Código da cidade do município da prestação do serviço."
        );

        $this->dom->addChild(
            $RPS,
            "NumeroEncapsulamento",
            $std->NumeroEncapsulamento,
            false,
            "Código que representa o número do encapsulamento da obra."
        );

        $this->dom->addChild(
            $RPS,
            "ValorTotalRecebido",
            $std->ValorTotalRecebido,
            false,
            "Informe o valor total recebido.."
        );

        $this->RPS = $RPS;

        return $RPS;
    }   

    /**
     * Returns xml string and assembly it is necessary
     * @return string
    */
    public function getXML(){
        if (empty($this->xml)) {
            $this->monta();
        }

        return $this->xml;
    }

    public function setVersion($version){
    	$this->version = $version;
    }

    /**
     * Includes missing or unsupported properties in stdClass
     * @param stdClass $std
     * @param array $possible
     * @return stdClass
    */
    protected function equilizeParameters(stdClass $std, $possible){
        
        $arr = get_object_vars($std);

        foreach ($possible as $key) {

            if (!array_key_exists($key, $arr)) {

                $std->$key = null;

            }

        }

        return $std;
    }

    public function GenerateXMLCancelarNFe($cnpj, $InscricaoPrestador, $NumeroRPS, $NumeroNFe){
        
        $PedidoCancelamentoNFe = $this->dom->createElement("PedidoCancelamentoNFe");

    	$PedidoCancelamentoNFe->setAttribute('xmlns', 'http://www.prefeitura.sp.gov.br/nfe');

        $cabecalho = $this->dom->createElement('Cabecalho');

    	$cabecalho->setAttribute('Versao', $this->version);
    	
    	$cabecalho->setAttribute('xmlns', '');
        
        $CPFCNPJRemetente = $this->dom->createElement("CPFCNPJRemetente");

        $this->dom->addChild(
            $CPFCNPJRemetente,
            "CNPJ",
            Strings::replaceSpecialsChars(trim($cnpj)),
            true,
			"CNPJ"
        );

        $this->dom->appChild($cabecalho, $CPFCNPJRemetente, 'Falta tag "Cabecalho"');
        
        $this->dom->addChild(
            $cabecalho,
            "transacao",
            'true',
            true,
			"transacao"
        );

        $this->dom->appChild($PedidoCancelamentoNFe, $cabecalho, 'Falta tag "Detalhe"');   

        $detalhe = $this->dom->createElement('Detalhe');

    	$detalhe->setAttribute('xmlns', '');

    	$ChaveNFe = $this->dom->createElement('ChaveNFe');

        $this->dom->addChild(
            $ChaveNFe,
            "InscricaoPrestador",
            Strings::replaceSpecialsChars(trim($InscricaoPrestador)),
            true,
			"InscricaoPrestador"
        );

        $this->dom->addChild(
            $ChaveNFe,
            "NumeroNFe",
            $NumeroNFe,
            true,
			"NumeroNFe"
        );

        $this->dom->appChild($detalhe, $ChaveNFe, 'Falta tag "Detalhe"');   
        
        $this->dom->appChild($PedidoCancelamentoNFe, $detalhe, 'Falta tag "Detalhe"');   
        
        $this->dom->appendChild($PedidoCancelamentoNFe);

        return $this->dom->saveXML();
    }

    public function GenerateXMLConsultaNFe($cnpj = '', $InscricaoPrestador = '', $NumeroRPS = '', $SerieRPS = '', $NumeroNFe = ''){

        $PedidoConsultaNFe = $this->dom->createElement("PedidoConsultaNFe");

    	$PedidoConsultaNFe->setAttribute('xmlns', 'http://www.prefeitura.sp.gov.br/nfe');

    	$cabecalho = $this->dom->createElement('Cabecalho');

    	$cabecalho->setAttribute('Versao', $this->version);
    	
    	$cabecalho->setAttribute('xmlns', '');

    	$CPFCNPJRemetente = $this->dom->createElement("CPFCNPJRemetente");

        $this->dom->addChild(
            $CPFCNPJRemetente,
            "CNPJ",
            Strings::replaceSpecialsChars(trim($cnpj)),
            true,
			"CNPJ"
        );

        $this->dom->appChild($cabecalho, $CPFCNPJRemetente, 'Falta tag "Cabecalho"');   

        $this->dom->appChild($PedidoConsultaNFe, $cabecalho, 'Falta tag "Detalhe"');   

    	$detalhe = $this->dom->createElement('Detalhe');

    	$detalhe->setAttribute('xmlns', '');

    	$ChaveRPS = $this->dom->createElement('ChaveRPS');

    	// $ChaveNFe = $this->dom->createElement('ChaveNFe');

        $this->dom->addChild(
            $ChaveRPS,
            "InscricaoPrestador",
            Strings::replaceSpecialsChars(trim($InscricaoPrestador)),
            true,
			"InscricaoPrestador"
        );

        $this->dom->addChild(
            $ChaveRPS,
            "SerieRPS",
            Strings::replaceSpecialsChars(trim($SerieRPS)),
            true,
			"SerieRPS"
        );

        $this->dom->addChild(
            $ChaveRPS,
            "NumeroRPS",
            $NumeroRPS,
            true,
			"NumeroRPS"
        );

        $this->dom->appChild($detalhe, $ChaveRPS, 'Falta tag "Detalhe"');   

        // $this->dom->addChild(
        //     $ChaveNFe,
        //     "InscricaoPrestador",
        //     Strings::replaceSpecialsChars(trim($InscricaoPrestador)),
        //     true,
		// 	"InscricaoPrestador"
        // );

        // $this->dom->addChild(
        //     $ChaveNFe,
        //     "NumeroNFe",
        //     $NumeroNFe,
        //     true,
		// 	"NumeroNFe"
        // );

        // $this->dom->appChild($detalhe, $ChaveNFe, 'Falta tag "Detalhe"');   
       
        $this->dom->appChild($PedidoConsultaNFe, $detalhe, 'Falta tag "Detalhe"');   

        $this->dom->appendChild($PedidoConsultaNFe);

        return $this->dom->saveXML();

    }

    public function GenerateXMLPedidoConsultaNFePeriodo($CPFCNPJRemetS,$CPFCNPJS,$dateIni,$dateEnd,$inscricao = '',$page = 1){

    	$PedidoConsultaNFePeriodo = $this->dom->createElement("PedidoConsultaNFePeriodo");

    	$PedidoConsultaNFePeriodo->setAttribute('xmlns', 'http://www.prefeitura.sp.gov.br/nfe');

    	$cabecalho = $this->dom->createElement('Cabecalho');

    	$cabecalho->setAttribute('Versao', $this->version);
    	
    	$cabecalho->setAttribute('xmlns', '');

    	$CPFCNPJRemetente = $this->dom->createElement("CPFCNPJRemetente");

    	$this->dom->addChild(
            $CPFCNPJRemetente,
            "CNPJ",
            Strings::replaceSpecialsChars(trim($CPFCNPJRemetS)),
            true,
			"CNPJ"
        );

        $this->dom->appChild($cabecalho, $CPFCNPJRemetente, 'Falta tag "Cabecalho"');


    	$CPFCNPJ = $this->dom->createElement("CPFCNPJ");

    	$this->dom->addChild(
            $CPFCNPJ,
            "CNPJ",
            Strings::replaceSpecialsChars(trim($CPFCNPJS)),
            true,
			"CNPJ"
        );        

        $this->dom->appChild($cabecalho, $CPFCNPJ, 'Falta tag "Cabecalho"');

        $this->dom->addChild(
            $cabecalho,
            "Inscricao",
            Strings::replaceSpecialsChars(trim($inscricao)),
            false,
			"Inscricao"
        );

        $this->dom->addChild(
            $cabecalho,
            "dtInicio",
            Strings::replaceSpecialsChars(trim($dateIni)),
            true,
			"data Inicio"
        );                

        $this->dom->addChild(
            $cabecalho,
            "dtFim",
            Strings::replaceSpecialsChars(trim($dateEnd)),
            true,
			"data fim"
        );

        $this->dom->addChild(
            $cabecalho,
            "NumeroPagina",
            Strings::replaceSpecialsChars(trim($page)),
            true,
			"Numero pagina"
        );

        $this->dom->appChild($PedidoConsultaNFePeriodo, $cabecalho, 'Falta tag "Cabecalho"');

        $this->dom->appendChild($PedidoConsultaNFePeriodo);

        return $this->dom->saveXML();

    }

    private function removePointAndComa($text){

        return preg_replace('/(-|,|\.)/', '', $text);
    }
}

?>
