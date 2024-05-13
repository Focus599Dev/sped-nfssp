<?php

namespace NFePHP\NFs\SP;

/**
 * Class to signner a Xml
 * Meets packages :
 *     sped-nfe,
 *     sped-cte,
 *     sped-mdfe,
 *     sped-nfse,
 *     sped-efinanceira
 *     sped-esocial
 *     sped-efdreinf
 *     e sped-esfinge
 *
 * @category  NFePHP
 * @package   NFePHP\NFs\SP
 * @copyright NFePHP Copyright (c) 2016
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-common for the canonical source repository
 */

use NFePHP\Common\Certificate;
use NFePHP\Common\Signer as SignerCommon;
use NFePHP\Common\Certificate\PublicKey;
use NFePHP\Common\Exception\SignerException;
use NFePHP\Common\Strings;
use NFePHP\Common\Validator;
use DOMDocument;
use DOMNode;
use DOMElement;

class Signer extends SignerCommon 
{
    
    public static function assinatura( $certificate,$xml,$algorithm){

        $dom = new DOMDocument('1.0', 'UTF-8');

        $dom->loadXML($xml);
        
        $dom->preserveWhiteSpace = false;
        
        $dom->formatOutput = false;
        
        $root = $dom->getElementsByTagName('RPS');

        $textAss = self::getDataAssinatura($dom);
       
        $signature = base64_encode($certificate->sign($textAss, $algorithm)); 

        $content = $root->item(0)->firstChild;

        $elmAss = $dom->createElement('Assinatura', $signature);

        $root->item(0)->insertBefore($elmAss, $content);

        return $dom->saveHTML();
    }

    public static function assinaturaCancelamento( $certificate,$xml,$algorithm, $InscricaoPrestado, $NumeroNFe){
        
        $textAss = '';

        $textAss .= str_pad($InscricaoPrestado, 8, '0', STR_PAD_LEFT);
        
        $textAss .= str_pad($NumeroNFe, 12, '0', STR_PAD_LEFT);

        $signature = base64_encode($certificate->sign( $textAss, OPENSSL_ALGO_SHA1));

        $dom = new DOMDocument('1.0', 'UTF-8');

        $dom->loadXML($xml);
        
        $dom->preserveWhiteSpace = false;
        
        $dom->formatOutput = false;
        
        $root = $dom->getElementsByTagName('Detalhe')->item(0);

        $elmAss = $dom->createElement('AssinaturaCancelamento', $signature);

        $root->appendChild($elmAss);

        return $dom->saveHTML();
    }
    

    private static function getDataAssinatura($dom){

        $textAss = '';

        // inscrição municipal prestador
        $node = $dom->getElementsByTagName('InscricaoPrestador');

        if ($node->length){
            
            $node = str_pad($node->item(0)->nodeValue, 8, '0', STR_PAD_LEFT);

        } else 
            $node = str_pad('', 8, '0', STR_PAD_LEFT);

        $textAss .= $node;

        $node = $dom->getElementsByTagName('SerieRPS');

        if ($node->length){
            
            $node = str_pad($node->item(0)->nodeValue, 5, ' ', STR_PAD_RIGHT);

        } else {
            $node = str_pad('', 5, ' ', STR_PAD_RIGHT);
        }

        $textAss .= $node;

        $node = $dom->getElementsByTagName('NumeroRPS');

        if ($node->length){
            
            $node = str_pad($node->item(0)->nodeValue, 12, '0', STR_PAD_LEFT);

        } else 
            $node = str_pad('', 12, '0', STR_PAD_LEFT);

        $textAss .= $node;
        

        $node = $dom->getElementsByTagName('DataEmissao');
        
        if ($node->length){
            
            $node = self::removePointAndComa($node->item(0)->nodeValue);

        } else 
            $node = str_pad('', 8, ' ', STR_PAD_LEFT);

        $textAss .= $node;

        $node = $dom->getElementsByTagName('TributacaoRPS');
        
        if ($node->length){
            
            $node = $node->item(0)->nodeValue;

        } else 
            $node = ' ';

        $textAss .= $node;

        $node = $dom->getElementsByTagName('StatusRPS');
        
        if ($node->length){
            
            $node = $node->item(0)->nodeValue;

        } else 
            $node = ' ';

        $textAss .= $node;

        $node = $dom->getElementsByTagName('ISSRetido');
        
        if ($node->length){
            
            if ($node->item(0)->nodeValue == '1')
                $node = 'S';
            else 
                $node = 'N';
        } else 
            $node = 'N';

        $textAss .= $node;
        
        $node = $dom->getElementsByTagName('ValorServicos');
        
        if ($node->length){
            
            $node = str_pad( self::removePointAndComa($node->item(0)->nodeValue) , 15, '0', STR_PAD_LEFT);
           
        } else 
            $node = str_pad('' , 15, '0', STR_PAD_LEFT);
            
        $textAss .= $node;

        $node = $dom->getElementsByTagName('ValorDeducoes');
        
        if ($node->length){
            
            $node = str_pad( self::removePointAndComa($node->item(0)->nodeValue) , 15, '0', STR_PAD_LEFT);
           
        } else 
            $node = str_pad('' , 15, '0', STR_PAD_LEFT);
            
        $textAss .= $node;

        $node = $dom->getElementsByTagName('CodigoServico');
        
        if ($node->length){
            
            $node = str_pad( self::removePointAndComa($node->item(0)->nodeValue) , 5, '0', STR_PAD_LEFT);
           
        } else 
            $node = str_pad('' , 5, '0', STR_PAD_LEFT);
            
        $textAss .= $node;

        $indCPFCNPJ = 3;
        
        $CPFCNPJ = str_pad( '' , 14, '0', STR_PAD_LEFT);

        $node = $dom->getElementsByTagName('CPFCNPJTomador');

        if ($node->length){

            $nodeCPFCNPJ = $node->item(0)->getElementsByTagName('CNPJ');

            if ($nodeCPFCNPJ->length){
                $indCPFCNPJ = 2;   
               
                $CPFCNPJ = $nodeCPFCNPJ->item(0)->nodeValue;

            } else {

                $indCPFCNPJ = 1;
                
                $nodeCPFCNPJ = $node->item(0)->getElementsByTagName('CPF');

                $CPFCNPJ = $nodeCPFCNPJ->item(0)->nodeValue;
                
            }

            $CPFCNPJ = str_pad( $CPFCNPJ , 14, '0', STR_PAD_LEFT);

            $textAss .= $indCPFCNPJ;

            $textAss .= $CPFCNPJ;
        }

        $indIntermediario = 3;
        
        $CPFCNPJ = str_pad( '' , 14, '0', STR_PAD_LEFT);

        $node = $dom->getElementsByTagName('CPFCNPJIntermediario');

        if ($node->length){

            $nodeCPFCNPJ = $node->item(0)->getElementsByTagName('CNPJ');

            if ($nodeCPFCNPJ->length){
                $indIntermediario = 2;   
               
                $CPFCNPJ = $nodeCPFCNPJ->item(0)->nodeValue;

            } else {

                $indIntermediario = 1;
                
                $nodeCPFCNPJ = $node->item(0)->getElementsByTagName('CPF');

                $CPFCNPJ = $nodeCPFCNPJ->item(0)->nodeValue;
                
            }

            $CPFCNPJ = str_pad( $CPFCNPJ , 14, '0', STR_PAD_LEFT);

            $textAss .= $indIntermediario;

            $textAss .= $CPFCNPJ;
    
            $node = $dom->getElementsByTagName('ISSRetidoIntermediario');
            
            if ($node->length){
                
                if ($node->item(0)->nodeValue == 'true')
                    $node = 'S';
                else 
                    $node = 'N';
            } else 
                $node = 'N';
    
            $textAss .= $node;

        }

        return $textAss;
    }

    private static function removePointAndComa($text){

        return preg_replace('/(-|,|\.)/', '', $text);
    }
}
