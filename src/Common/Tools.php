<?php

namespace NFePHP\NFs\SP\Common;

/**
 * Class base responsible for communication with SEFAZ
 *
 * @category  NFePHP
 * @package   NFePHP\NFs\SP\Common
 * @copyright NFePHP Copyright (c) 2008-2017
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Marlon O. Borbosa
 * @link      https://github.com/Focus599Dev/sped-nfssp for the canonical source repository
 */

use SoapHeader;
use NFePHP\Common\TimeZoneByUF;
use NFePHP\Common\UFList;
use NFePHP\NFs\SP\Soap\SoapCurl;
use NFePHP\Common\Soap\SoapInterface;
use NFePHP\Common\Certificate;
use NFePHP\NFs\SP\Factories\Webservices;
use NFePHP\NFs\SP\Factories\Header;
use NFePHP\Common\Signer;
use NFePHP\Common\Validator;
use NFePHP\NFs\SP\Common\EntitiesCharacters;

class Tools
{

    /**
     * config class
     * @var \stdClass
     */
    public $config;

    /**
     * Path to storage folder
     * @var string
     */
    public $pathwsfiles = '';

    /**
     * Path to schemes folder
     * @var string
     */
    public $pathschemes = '';

    /**
     * ambiente
     * @var string
     */
    public $ambiente = 'homologacao';

    /**
     * Environment
     * @var int
     */
    public $tpAmb = 2;

    /**
     * soap class
     * @var SoapInterface
     */

    public $soap;

    /**
     * Application version
     * @var string
     */
    public $verAplic = '';

    /**
     * last soap request
     * @var string
     */
    public $lastRequest = '';
    /**
     * last soap response
     * @var string
     */
    public $lastResponse = '';
    /**
     * certificate class
     * @var Certificate
     */
    protected $certificate;
    /**
     * Sign algorithm from OPENSSL
     * @var int
     */
    protected $algorithm = OPENSSL_ALGO_SHA1;
    /**
     * Canonical conversion options
     * @var array
     */
    protected $canonical = [true, false, null, null];

    /**
     * Version of layout
     * @var string
     */
    protected $versao = '2';

    /**
     * urlPortal
     * Instância do WebService
     *
     * @var string
     */
    protected $urlPortal = 'http://www.prefeitura.sp.gov.br/nfe';

    /**
     * urlVersion
     * @var string
     */
    protected $urlVersion = '';
    /**
     * urlService
     * @var string
     */
    protected $urlService = '';
    /**
     * @var string
     */
    protected $urlMethod = '';
    /**
     * @var string
     */
    protected $urlOperation = '';
    /**
     * @var string
     */
    protected $urlNamespace = '';
    /**
     * @var string
     */
    protected $urlAction = '';
    /**
     * @var \SoapHeader | null
     */
    protected $objHeader = null;
    /**
     * @var string
     */
    protected $urlHeader = '';
    /**
     * @var array
     */
    protected $soapnamespaces = [
        'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance",
        'xmlns:xsd' => "http://www.w3.org/2001/XMLSchema",
        'xmlns:soap' => "http://schemas.xmlsoap.org/soap/envelope/",
    ];

    /**
     * @var array
     */
    protected $availableVersions = [
        '1' => 'PL_NFSe_1',
        '2' => 'PL_NFSe_2',
    ];

    /**
     * Fake model NFse dont have
     */
    protected $modelo = 61;

    protected $urlcUF = null;

    /**
     * Constructor
     * load configurations,
     * load Digital Certificate,
     * map all paths,
     * set timezone and
     * and instanciate Contingency::class
     * @param string $configJson content of config in json format
     * @param Certificate $certificate
     */
    public function __construct($configJson, Certificate $certificate)
    {
        $this->pathwsfiles = realpath(
            __DIR__ . '/../../config'
        ) . '/';

        //valid config json string
        $this->config = json_decode($configJson);

        $this->version($this->config->versao);
        $this->setEnvironmentTimeZone($this->config->siglaUF);
        $this->certificate = $certificate;
        $this->setEnvironment($this->config->tpAmb);
        $this->soap = new SoapCurl($certificate);

        if ($this->config->proxy) {
            $this->soap->proxy($this->config->proxy, $this->config->proxyPort, $this->config->proxyUser, $this->config->proxyPass);
        }
    }

    public function version($version = null)
    {

        if (null === $version) {
            return $this->versao;
        }

        //Verify version template is defined
        if (false === isset($this->availableVersions[$version])) {
            throw new \InvalidArgumentException('Essa versão de layout não está disponível');
        }

        $this->versao = $version;
        $this->config->schemes = $this->availableVersions[$version];
        $this->pathschemes = realpath(
            __DIR__ . '/../../schemes/' . $this->config->schemes
        ) . '/';

        return $this->versao;
    }

    /**
     * Sets environment time zone
     * @param string $acronym (ou seja a sigla do estado)
     * @return void
     */
    public function setEnvironmentTimeZone($acronym)
    {
        date_default_timezone_set(TimeZoneByUF::get($acronym));
    }

    /**
     * Alter environment from "homologacao" to "producao" and vice-versa
     * @param int $tpAmb
     * @return void
     */
    public function setEnvironment($tpAmb = 2)
    {
        if (!empty($tpAmb) && ($tpAmb == 1 || $tpAmb == 2)) {
            $this->tpAmb = $tpAmb;
            $this->ambiente = ($tpAmb == 1) ? 'producao' : 'homologacao';
        }
    }

    /**
     * Recover path to xml data base with list of soap services
     * @return string
     */
    protected function getXmlUrlPath()
    {
        $file = $this->pathwsfiles
            . "wsnfse_" . $this->versao . ".xml";


        if (! file_exists($file)) {
            return '';
        }
        return file_get_contents($file);
    }

    /**
     * Send request message to webservice
     * @param array $parameters
     * @param string $request
     * @return string
     */
    protected function sendRequest($request, array $parameters = [])
    {
        $this->checkSoap();

        return (string) $this->soap->send(
            $this->urlService,
            $this->urlMethod,
            $this->urlAction,
            SOAP_1_2,
            $parameters,
            $this->soapnamespaces,
            $request,
            $this->objHeader
        );
    }

    /**
     * Verify if SOAP class is loaded, if not, force load SoapCurl
     */
    protected function checkSoap()
    {
        if (empty($this->soap)) {
            $this->soap = new SoapCurl($this->certificate);
        }
    }

    /**
     * Performs xml validation with its respective
     * XSD structure definition document
     * NOTE: if dont exists the XSD file will return true
     * @param string $version layout version
     * @param string $body
     * @param string $method
     * @return boolean
     */
    protected function isValid($version, $body, $method)
    {

        $schema = $this->pathschemes . $method . "_v$version.xsd";

        if (!is_file($schema)) {
            return true;
        }

        return Validator::isValid(
            $body,
            $schema
        );
    }

    /**
     * Assembles all the necessary parameters for soap communication
     * @param string $service
     * @param string $uf
     * @param int $tpAmb
     * @param bool $ignoreContingency
     * @return void
     */
    protected function servico(
        $service,
        $uf,
        $tpAmb,
        $ignoreContingency = false
    ) {

        $ambiente = $tpAmb == 1 ? "producao" : "homologacao";

        $webs = new Webservices($this->getXmlUrlPath());
        $sigla = $uf;

        $stdServ = $webs->get($sigla, $ambiente, $this->modelo);

        if ($stdServ === false) {
            throw new \RuntimeException(
                "Nenhum serviço foi localizado para esta unidade "
                    . "da federação [$sigla], com o modelo [$this->modelo]."
            );
        }
        if (empty($stdServ->$service->url)) {
            throw new \RuntimeException(
                "Este serviço [$service] não está disponivel para esta "
                    . "unidade da federação [$uf] ou para este modelo de Nota ["
                    . $this->modelo
                    . "]."
            );
        }

        //recuperação do cUF
        $this->urlcUF = $this->getcUF($uf);

        //recuperação da versão
        $this->urlVersion = $stdServ->$service->version;

        //recuperação da url do serviço
        $this->urlService = $stdServ->$service->url;

        //recuperação do método
        $this->urlMethod = $stdServ->$service->method;

        //recuperação da operação
        $this->urlOperation = $stdServ->$service->operation;

        //montagem do namespace do serviço
        $this->urlNamespace = $this->urlPortal;

        $this->urlNamespace = sprintf(
            "%s/ws/%s",
            $this->urlPortal,
            lcfirst($this->urlOperation)
        );

        //NFS não tem header
        $this->urlHeader = null;

        $this->urlAction = "\""
            . $this->urlNamespace
            . "\"";

        //montagem do SOAP Header
        $this->objHeader = null;
    }

    /**
     * Set or get model of document NFe = 55 or NFCe = 65
     * @param int $model
     * @return int modelo class parameter
     */
    public function model($model = null)
    {
        if ($model == 61) {
            $this->modelo = $model;
        }
        return $this->modelo;
    }

    /**
     * Recover cUF number from state acronym
     * @param string $acronym Sigla do estado
     * @return int number cUF
     */
    public function getcUF($acronym)
    {
        return UFlist::getCodeByUF($acronym);
    }

    /**
     * Convert string xml message to cdata string
     * @param string $message
     * @return string
     */
    protected function stringTransform($message)
    {

        return EntitiesCharacters::unconvert(htmlentities($message, ENT_NOQUOTES));
    }

    /**
     * Remove os marcadores de XML
     * @param string $body
     * @return string
     */
    public function clear($body)
    {
        $body = str_replace('<?xml version="1.0"?>', '', $body);
        $body = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $body);
        $body = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $body);
        return $body;
    }

    public function makeBody($method, $mensagemXML)
    {

        if (in_array($method, ['ConsultaSituacaoLote'])) {
            $this->versao = '1';
            
            $tagversaoSchema = "<versaoSchema>1</versaoSchema>";
        } else if (in_array($method, ['ConsultaNFe'])) {
            $this->versao = '1';

            $tagversaoSchema = "<VersaoSchema>1</VersaoSchema>";
        } else {
            $this->versao = '2';
            
            $tagversaoSchema = "<VersaoSchema>2</VersaoSchema>";
        }

        $mensagemXML = $this->clear($mensagemXML);

        // $mensagemXML = $this->stringTransform($mensagemXML);

        $request = "$tagversaoSchema<MensagemXML><![CDATA[$mensagemXML]]></MensagemXML>";

        $body = "<" . $method . "Request xmlns=\"$this->urlPortal\">" . $request . "</" . $method . "Request>";

        return $body;
    }

    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    public function removeStuffs($xml)
    {

        $xml = htmlspecialchars_decode($xml);

        if (preg_match('/<soap:Body>/', $xml)) {

            $tag = '<soap:Body>';

            $xml = substr($xml, (strpos($xml, $tag) + strlen($tag)), strlen($xml));

            $tag = '</soap:Body>';

            $xml = substr($xml, 0, strpos($xml, $tag));
        }

        $xml = preg_replace('/(xmlns)="([a-z:\/\.]){0,}"/', '', $xml);

        $xml = preg_replace('/(xmlns):([a-z]){1,}="([a-z:\/.0-9A-Z\-]){0,}"/', '', $xml);

        $xml = str_replace('Versao="' . $this->versao . '"', '', $xml);

        $xml = $this->clear($xml);

        $xml = trim($xml);

        return $xml;
    }
}
