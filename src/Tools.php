<?php 

namespace NFePHP\NFs\SP;

/**
 * @category   NFePHP
 * @package    NFePHP\NFs\SP
 * @copyright  Copyright (c) 2008-2017
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O.Barbosa <marlon.academi@gmail.com>
 * @link       http://github.com/nfephp-org/sped-nfssp for the canonical source repository
 */

use NFePHP\NFs\SP\Common\Tools as ToolsCommon;
use NFePHP\Common\Signer;
use NFePHP\Common\Strings;
use NFePHP\NFs\SP\Make;

class Tools extends ToolsCommon{
  
  /**
     * Serviço de distribuição de informações de documentos eletronicos
     * de interesse do remetente
     * @param string $CPFCNPJRemet CPF/CNPJ do Remetente autorizado a enviar a mensagem XML.
     * @param string $CPFCNPJ Informe o CPF/CNPJ do tomador da NF-e.
     * @param date   $dateIni Data início da consulta.
     * @param date   $dateEnd Data fim da consulta.
     * @param date   $page Data fim da consulta.
     * @return string
     */
    public function sefazDistDFe(
        $CPFCNPJRemet,
        $CPFCNPJ,
        $dateIni,
        $dateEnd,
        $page = 1,
        $inscricao= ''
    ) {

		$servico = 'PedidoConsultaNFePeriodo';

		$this->servico(
            $servico,
            'SP',
            $this->tpAmb,
            true
        );

        $makeXML = new Make();

        $consulta = $makeXML->GenerateXMLPedidoConsultaNFePeriodo($CPFCNPJRemet, $CPFCNPJ, $dateIni, $dateEnd, $inscricao,$page);

        $request = Signer::sign(
                $this->certificate,
                $consulta,
                'PedidoConsultaNFePeriodo',
                '',
                $this->algorithm,
                $this->canonical
            );

        $this->isValid($this->urlVersion, $request, 'PedidoConsultaNFePeriodo');

        $body = $this->makeBody('ConsultaNFeRecebidas', $request);

        $parameters = [
            'ConsultaNFeRecebidasRequest' => $request
        ];
        //este webservice não requer cabeçalho
        $this->objHeader = null;
        
        $this->lastResponse = $this->sendRequest($body, $parameters);

        return $this->lastResponse;

    }


}

?>
