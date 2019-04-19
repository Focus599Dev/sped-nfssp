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
use NFePHP\Common\DOMImproved as Dom;

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

        return $this->dom;
    }

    public function setVersion($version){
    	$this->version = $version;
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

}

?>