<?php

namespace NFePHP\NFs\SP\Factories;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\WebISS\Factories\
 * @copyright  Copyright (c) 2008-2019
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O. Barbosa <marlon.academi at gmail dot com>
 * @link       https://github.com/Focus599Dev/sped-nfsginfe for the canonical source repository
 */

use NFePHP\NFs\SP\Make;
use NFePHP\NFs\SP\Exception\DocumentsException;
use stdClass;
use NFePHP\Common\Strings;

class Parser
{

    /**
     * @var array
     */
    protected $structure;

    /**
     * @var Make
     */
    protected $make;

    /**
     * @var stdClass
     */
    protected $loteRps;

    /**
     * @var stdClass
     */
    protected $tomador;

    /**
     * @var stdClass
     */
    protected $servico;

    /**
     * @var String
     */
    protected $version;

    /**
     * @var stdClass
     */
    protected $cabecalho;

    /**
     * @var stdClass
     */
    protected $RPS;

    /**
     * @var stdClass
     */
    protected $IBSCBS;

    /**
     * Configure environment to correct NFSe layout
     * @param string $version
     */
    public function __construct($version = '1')
    {

        $ver = str_replace('.', '', $version);

        $path = realpath(__DIR__ . "/../../storage/txtstructure$ver.json");

        $this->structure = json_decode(file_get_contents($path), true);

        $this->version = $version;

        $this->make = new Make();
    }

    /**
     * Convert txt to XML
     * @param array $nota
     * @return string|null
     */
    public function toXml($nota)
    {

        $this->array2xml($nota);

        $this->make->buildRPS($this->RPS);

        if ($this->make->monta()) {

            return $this->make->getXML();
        }

        return null;
    }

    /**
     * Converte txt array to xml
     * @param array $nota
     * @return void
     */
    protected function array2xml($nota)
    {

        foreach ($nota as $lin) {

            $fields = explode('|', $lin);

            if (empty($fields)) {
                continue;
            }

            $metodo = strtolower(str_replace(' ', '', $fields[0])) . 'Entity';

            if (method_exists(__CLASS__, $metodo)) {

                $struct = $this->structure[strtoupper($fields[0])];

                $std = $this->fieldsToStd($fields, $struct);

                $this->$metodo($std);
            }
        }

        $this->endParseMetodos();
    }

    /**
     * Creates stdClass for all tag fields
     * @param array $dfls
     * @param string $struct
     * @return stdClass
     */

    protected static function fieldsToStd($dfls, $struct)
    {

        $sfls = explode('|', $struct);

        $len = count($sfls);

        $std = new \stdClass();

        for ($i = 1; $i < $len; $i++) {

            $name = $sfls[$i];

            if (isset($dfls[$i]))
                $data = $dfls[$i];
            else
                $data = '';

            if (!empty($name)) {

                $std->$name = Strings::replaceSpecialsChars($data);
            }
        }

        return $std;
    }

    /**
     * Create tag cabecalho [A]
     * A|NumeroLote|versao|
     * @param stdClass $std
     * @return void
     */
    private function aEntity($std)
    {

        $this->cabecalho = $std;
    }

    /**
     * Complete tag cabecalho [B]
     * B|CpfCnpj|dtInicio|dtFim|ValorTotalServicos|ValorTotalDeducoes|transacao|QtdRPS
     * @param stdClass $std
     * @return void
     */
    private function bEntity($std)
    {

        $this->cabecalho = (object) array_merge((array) $this->cabecalho, (array) $std);

        $this->make->buildCabecalho($this->cabecalho);
    }


    /**
     * Create tag Tomador [E]
     * "E|CpfCnpj|InscricaoMunicipalTomador|InscricaoEstadualTomador|RazaoSocialTomador|EmailTomador|
     * @param stdClass $std
     * @return void
     */
    private function eEntity($std)
    {

        $this->RPS = (object) array_merge((array) $this->RPS, (array) $std);
    }

    /**
     * Complete tag Tomador [E02]
     * E02|TipoLogradouro|Logradouro|NumeroEndereco|ComplementoEndereco|Bairro|Cidade|UF|CEP
     * @param stdClass $std
     * @return void
     */
    private function e02Entity($std)
    {

        $this->RPS = (object) array_merge((array) $this->RPS, (array) $std);
    }

    /**
     * Create tag IdentificacaoRps [H]
     * H|InscricaoPrestador|SerieRPS|NumeroRPS'
     * @param stdClass $std
     * @return void
     */
    private function hEntity($std)
    {

        $this->make->buildChaveRPS($std);
    }

    /**
     * Complete tag RPS [H01]
     * H01|TipoRPS|DataEmissao|StatusRPS|TributacaoRPS|ValorServicos|ValorDeducoes|ValorPIS|ValorCOFINS|ValorINSS|ValorIR|ValorCSLL|CodigoServico|AliquotaServicos|ISSRetido|CPFCNPJIntermediario|InscricaoMunicipalIntermediario|ISSRetidoIntermediario|EmailIntermediario|ValorCargaTributaria|PercentualCargaTributaria|FonteCargaTributaria|CodigoCEI|MatriculaObra|MunicipioPrestacao|NumeroEncapsulamento|ValorTotalRecebido|
     * Layout 2 reforma tributaria
     * H01|TipoRPS|DataEmissao|StatusRPS|TributacaoRPS|ValorServicos|ValorDeducoes|ValorPIS|ValorCOFINS|ValorINSS|ValorIR|ValorCSLL|CodigoServico|AliquotaServicos|ISSRetido|CPFCNPJIntermediario|InscricaoMunicipalIntermediario|ISSRetidoIntermediario|EmailIntermediario|ValorCargaTributaria|PercentualCargaTributaria|FonteCargaTributaria|CodigoCEI|MatriculaObra|MunicipioPrestacao|NumeroEncapsulamento|ValorTotalRecebido|ValorInicialCobrado|ValorFinalCobrado|ValorMulta|ValorJuros|ValorIPI|ExigibilidadeSuspensa|PagamentoParceladoAntecipado|NCM|NBS|atvEvento|cLocPrestacao|cPaisPrestacao|
     * @param stdClass $std
     * @return void
     */
    private function h01Entity($std)
    {

        $this->RPS = (object) array_merge((array) $this->RPS, (array) $std);
    }

    /**
     * Complete tag Descricao [H02]
     * H02|Discriminacao
     * @param stdClass $std
     * @return void
     */
    private function h02Entity($std)
    {

        $this->RPS = (object) array_merge((array) $this->RPS, (array) $std);
    }

    /**
     * tag IBSCBS
     * I|finNFSe|indFinal|cIndOp|tpOper|tpEnteGov|indDest|dtEmiDoc|dtCompDoc|
     * @param stdClass $std
     * @return void
     */
    private function iEntity($std)
    {

        $this->IBSCBS = $std;
    }

    /**
     * gRefNFSe
     * I01|refNFSe
     * @param stdClass $std
     * @return void
     */
    private function i01Entity($std)
    {
        if (!isset($this->IBSCBS->refNFSe)) {
            $this->IBSCBS->refNFSe = [];
        }

        $this->IBSCBS->refNFSe[] = $std;
    }

    /**
     * tag dest
     * I02|xNome|end|email|
     * @param stdClass $std
     * @return void
     */
    private function i02Entity($std)
    {
        $this->IBSCBS->dest = $std;
    }

    /**
     * tag end
     * I02A|CPF
     * @param stdClass $std
     * @return void
     */
    private function i02AEntity($std)
    {
        if (!isset($this->IBSCBS->dest)) {
            $this->IBSCBS->dest = new \stdClass();
        }

        $this->IBSCBS->dest->CPF = $std;
    }

    /**
     * tag end
     * I02B|CNPJ
     * @param stdClass $std
     * @return void
     */
    private function i02BEntity($std)
    {
        if (!isset($this->IBSCBS->dest)) {
            $this->IBSCBS->dest = new \stdClass();
        }

        $this->IBSCBS->dest->CNPJ = $std;
    }

    /**
     * tag end
     * I02C|NIF
     * @param stdClass $std
     * @return void
     */
    private function i02CEntity($std)
    {
        if (!isset($this->IBSCBS->dest)) {
            $this->IBSCBS->dest = new \stdClass();
        }

        $this->IBSCBS->dest->NIF = $std;
    }

    /**
     * tag end
     * I02D|NaoNIF
     * @param stdClass $std
     * @return void
     */
    private function i02DEntity($std)
    {
        if (!isset($this->IBSCBS->dest)) {
            $this->IBSCBS->dest = new \stdClass();
        }

        $this->IBSCBS->dest->NaoNIF = $std;
    }

    /**
     * tag documentos
     * I03|dFeNacional|docFiscalOutro|docOutro|dtEmiDoc|dtCompDoc|tpReeRepRes|xTpReeRepRes|vlrReeRepRes|
     * @param stdClass $std
     * @return void
     */
    private function i03Entity($std)
    {
        if (!isset($this->IBSCBS->documentos)) {
            $this->IBSCBS->documentos = [];
        }

        $this->IBSCBS->documentos[] = $std;
    }

    /**
     * tag documentos->fornec
     * I03A|xNome|
     * @param stdClass $std
     * @return void
     */
    private function i03AEntity($std)
    {

        if (isset($this->IBSCBS->documentos)) {

            $item = strlen($this->IBSCBS->documentos) - 1;

            if ($this->IBSCBS->documentos[$item])
                $this->IBSCBS->documentos[$item]->fornec = $std;
        }
    }

    /**
     * tag documentos->fornec
     * I03B|CPF|
     * @param stdClass $std
     * @return void
     */
    private function i03BEntity($std)
    {
        if (isset($this->IBSCBS->documentos)) {

            $item = strlen($this->IBSCBS->documentos) - 1;

            if ($this->IBSCBS->documentos[$item])
                $this->IBSCBS->documentos[$item]->fornec = array_merge((array) $this->IBSCBS->documentos[$item]->fornec, (array) $std);
        }
    }

    /**
     * tag documentos->fornec
     * I03C|CNPJ|
     * @param stdClass $std
     * @return void
     */
    private function i03CEntity($std)
    {
        if (isset($this->IBSCBS->documentos)) {

            $item = strlen($this->IBSCBS->documentos) - 1;

            if ($this->IBSCBS->documentos[$item])
                $this->IBSCBS->documentos[$item]->fornec = array_merge((array) $this->IBSCBS->documentos[$item]->fornec, (array) $std);
        }
    }

    /**
     * tag documentos->fornec
     * I03D|NIF|
     * @param stdClass $std
     * @return void
     */
    private function i03DEntity($std)
    {
        if (isset($this->IBSCBS->documentos)) {

            $item = strlen($this->IBSCBS->documentos) - 1;

            if ($this->IBSCBS->documentos[$item])
                $this->IBSCBS->documentos[$item]->fornec = array_merge((array) $this->IBSCBS->documentos[$item]->fornec, (array) $std);
        }
    }

    /**
     * tag documentos->fornec
     * I03E|NaoNIF|
     * @param stdClass $std
     * @return void
     */
    private function i03EEntity($std)
    {
        if (isset($this->IBSCBS->documentos)) {

            $item = strlen($this->IBSCBS->documentos) - 1;

            if ($this->IBSCBS->documentos[$item])
                $this->IBSCBS->documentos[$item]->fornec = array_merge((array) $this->IBSCBS->documentos[$item]->fornec, (array) $std);
        }
    }

    /**
     * tag documentos->fornec
     * I04|cClassTrib|cClassTribReg|
     * @param stdClass $std
     * @return void
     */
    private function i04Entity($std)
    {
        $this->IBSCBS->trib = $std;
    }

    /**
     * tag imovelobra
     * I05|inscImobFisc|cCIB|cObra|end|
     * @param stdClass $std
     * @return void
     */
    private function i05Entity($std)
    {
        $this->IBSCBS->imovelobra = $std;
    }

    private function endParseMetodos()
    {
        $this->make->buildIBSCBS($this->IBSCBS);
    }
}
