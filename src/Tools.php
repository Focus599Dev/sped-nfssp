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
use NFePHP\NFs\SP\Signer;
use NFePHP\Common\Strings;
use NFePHP\NFs\SP\Make;
use NFePHP\NFs\SP\Exception\InvalidArgumentException;

class Tools extends ToolsCommon
{

    public function enviaRPS($request)
    {

        if (empty($request)) {
            throw new InvalidArgumentException('$xml');
        }
        //remove all invalid strings
        $request = Strings::clearXmlString($request);

        if ($this->tpAmb == '1') {
            $servico = 'EnvioRPS';
        } else {
            $servico = 'TesteEnvioRPS';
        }

        $this->servico(
            $servico,
            'SP',
            $this->tpAmb
        );

        $request = Signer::assinatura(
            $this->certificate,
            $request,
            OPENSSL_ALGO_SHA1
        );

        // $request = Signer::sign(
        //     $this->certificate,
        //     $request,
        //     'RPS',
        //     'Id',
        //     $this->algorithm,
        //     $this->canonical,
        // );

        $request = Signer::sign(
            $this->certificate,
            $request,
            'PedidoEnvioRPS',
            'Id',
            $this->algorithm,
            $this->canonical
        );

        $this->lastRequest = $request;

        $this->isValid($this->versao, $request, 'PedidoEnvioRPS');

        $parameters = ['EnvioLoteRPS' => $request];

        $request = $this->makeBody($servico, $request);

        $this->lastResponse = $this->sendRequest($request, $parameters);

        $this->lastResponse = $this->removeStuffs($this->lastResponse);

        return $this->lastResponse;
    }

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
        $inscricao = ''
    ) {

        $servico = 'PedidoConsultaNFePeriodo';

        $this->servico(
            $servico,
            'SP',
            $this->tpAmb,
            true
        );

        $makeXML = new Make();

        $consulta = $makeXML->GenerateXMLPedidoConsultaNFePeriodo($CPFCNPJRemet, $CPFCNPJ, $dateIni, $dateEnd, $inscricao, $page);

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


    /**
     * Serviço Consulta NFE
     * de interesse do remetente
     * @param stdClass $data {InscricaoPrestador:'', cnpj: '', NumeroRPS: '', SerieRPS: '', NumeroNFe: ''}
     * @return string
     */
    public function ConsultaNFe(
        \stdClass $data
    ) {

        $servico = 'ConsultaNFe';

        $this->servico(
            $servico,
            'SP',
            $this->tpAmb,
            true
        );

        $makeXML = new Make();

        $consulta = $makeXML->GenerateXMLConsultaNFe($data->cnpj, $data->InscricaoPrestador, $data->NumeroRPS, $data->SerieRPS, $data->NumeroNFe);

        $request = Signer::sign(
            $this->certificate,
            $consulta,
            'PedidoConsultaNFe',
            '',
            $this->algorithm,
            $this->canonical
        );

        $this->isValid($this->urlVersion, $request, 'PedidoConsultaNFe');

        $body = $this->makeBody('ConsultaNFe', $request);

        $parameters = [
            'ConsultaNFeRequest' => $request
        ];
        //este webservice não requer cabeçalho
        $this->objHeader = null;

        $this->lastResponse = $this->sendRequest($body, $parameters);

        $this->lastResponse = $this->removeStuffs($this->lastResponse);

        return $this->lastResponse;
    }

    /**
     * Serviço Cancelar NFE
     * @return string
     */
    public function CancelaNfse(
        \stdClass $data
    ) {

        $servico = 'CancelamentoNFe';

        $this->servico(
            $servico,
            'SP',
            $this->tpAmb,
            true
        );

        $makeXML = new Make();

        $consulta = $makeXML->GenerateXMLCancelarNFe($data->cnpj, $data->InscricaoMunicipal, $data->rps, $data->Numero);

        $request = Signer::assinaturaCancelamento(
            $this->certificate,
            $consulta,
            OPENSSL_ALGO_SHA1,
            $data->InscricaoMunicipal,
            $data->Numero
        );

        $request = Signer::sign(
            $this->certificate,
            $request,
            'PedidoCancelamentoNFe',
            '',
            $this->algorithm,
            $this->canonical
        );

        $this->isValid($this->urlVersion, $request, 'PedidoCancelamentoNFe');

        $body = $this->makeBody('CancelamentoNFe', $request);

        $parameters = [
            'CancelamentoNFe' => $request
        ];

        //este webservice não requer cabeçalho
        $this->objHeader = null;

        $this->lastResponse = $this->sendRequest($body, $parameters);

        $this->lastResponse = $this->removeStuffs($this->lastResponse);

        return $this->lastResponse;
    }
}
