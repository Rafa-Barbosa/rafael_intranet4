<?php
/*
 * Data Criacao 25/10/2023
 * Autor: Rafael Postal Barbosa
 *
 * Descricao: Classe responsável por lidar com notas fiscais eletrônicas
 * 
 * Alterações:
 * 
 */

global $config;
require_once $config['include'] . 'vendor\nfephp-org\sped-nfe\bootstrap.php';

use NFePHP\NFe\Tools;
use NFePHP\NFe\Make;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapFake;
use NFePHP\NFe\Common\Standardize;

if(!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

class NFeService {
    // Cofiguração inicial
    private $_config;

    // Classe para assinatura e envio do certificado
    private $_tools;

    // XML da NFe
    private $_xml;

    // Erros gerados
    private $_erros = [];

    // Resposta do sefaz
    private $_resposta;

    // Recibo do envio
    private $_recibo;

    function __construct() {
        global $config;
        
        $this->_config = [
            "atualizacao" => "2017-02-20 09:11:21",
            "tpAmb"       => 2,
            "razaosocial" => "SUA RAZAO SOCIAL LTDA",
            "cnpj"        => "99999999999999",
            "siglaUF"     => "SP",
            "schemes"     => "PL_009_V4",
            "versao"      => '4.00',
            "tokenIBPT"   => "AAAAAAA",
            "CSC"         => "GPB0JBWLUR6HWFTVEAS6RJ69GPCROFPBBB8G",
            "CSCid"       => "000001",
            "proxyConf"   => [
                "proxyIp"   => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ];

        try {
            $configJson = json_encode($this->_config);
            $pfxcontent = file_get_contents('C:\xampp\htdocs\intranet4\twsfw4\teste\expired_certificate.pfx');
    
            $this->_tools = new Tools($configJson, Certificate::readPfx($pfxcontent, 'associacao'));
            //$tools->disableCertValidation(true); //tem que desabilitar
            $this->_tools->model('65');
        } catch(\Exception $e) {
            $this->_erros[] = $e->getMessage();
            // echo $e->getMessage();
        }
    }

    public function getXml() {
        return $this->_xml;
    }

    public function getErros() {
        return $this->_erros;
    }

    public function getResposta() {
        return $this->_resposta;
    }

    public function getRecibo() {
        return $this->_recibo;
    }

    public function gerarXml2() {
        try {
            $nfe = new Make();

            // infNFe
            $std = new stdClass();
            $std->versao = '4.00'; //versão do layout (string)
            $std->Id = ''; //se o Id de 44 digitos não for passado será gerado automaticamente
            $std->pk_nItem = null; //deixe essa variavel sempre como NULL
            $nfe->taginfNFe($std);

            // ide
            $std = new stdClass();
            $std->cUF = 51;
            $std->cNF = '93750348'; // Número aleatório gerado pelo emitente para cada NF-e
            $std->natOp = 'VENDA DE MERCADORIAS';

            $std->indPag = 0; //NÃO EXISTE MAIS NA VERSÃO 4.00

            $std->mod = 55;
            $std->serie = 1;
            $std->nNF = 2812;
            $std->dhEmi = date('Y-m-d\TH:i:sP'); // '2015-02-19T13:48:00-02:00';
            $std->dhSaiEnt = null;
            $std->tpNF = 1;
            $std->idDest = 1;
            $std->cMunFG = 5107925;
            $std->tpImp = 1;
            $std->tpEmis = 1;
            $std->cDV = ''; // calculo modulo 11
            $std->tpAmb = 2; // 1 = Produção, 2 = Homologação
            $std->finNFe = 1;
            $std->indFinal = 0;
            $std->indPres = 1;
            $std->indIntermed = 0;
            $std->procEmi = 0;
            $std->verProc = '3.10.31';
            $std->dhCont = null;
            $std->xJust = null;
            $nfe->tagide($std);

            // emit
            $std = new stdClass();
            $std->xNome = 'ELINELTON XAVIER SILVA';
            $std->xFant = 'COMERCIAL XAVIER';
            $std->IE = '135685303'; // Inscrição Estadual
            // $std->IEST;
            // $std->IM;
            // $std->CNAE;
            $std->CRT = 1;
            $std->CNPJ = '21845893000146'; //indicar apenas um CNPJ ou CPF
            // $std->CPF;
            $nfe->tagemit($std);

            // enderEmit
            $std = new stdClass();
            $std->xLgr = 'RUA CEREJEIRAS';
            $std->nro = 212;
            $std->xCpl = null;
            $std->xBairro = 'RESIDENCIAL COLINAS';
            $std->cMun = 5107925;
            $std->xMun = 'SORRISO';
            $std->UF = 'MT';
            $std->CEP = '78890000';
            $std->cPais = 1058;
            $std->xPais = 'Brasil';
            $std->fone = '66999555791';
            $nfe->tagenderEmit($std);

            // dest
            $std = new stdClass();
            $std->xNome = 'FARMACHIQ DROGARIAS LTDA';
            $std->indIEDest = 1;
            $std->IE = '137335105';
            // $std->ISUF;
            // $std->IM;
            $std->email = 'rafaelpostalbarbosa@gmail.com';
            $std->CNPJ = '31216969000128'; //indicar apenas um CNPJ ou CPF ou idEstrangeiro
            // $std->CPF = '86262653015';
            $std->idEstrangeiro = null;
            $nfe->tagdest($std);

            // enderDest
            $std = new stdClass();
            $std->xLgr = 'AV TANCREDO NEVES';
            $std->nro = 2302;
            $std->xCpl = null;
            $std->xBairro = 'CENTRO';
            $std->cMun = 5107925;
            $std->xMun = 'SORRISO';
            $std->UF = 'MT';
            $std->CEP = '78890000';
            $std->cPais = 1058;
            $std->xPais = 'Brasil';
            $std->fone = null;
            $nfe->tagenderDest($std);

            // prod
            $std = new stdClass();
            $std->item = 1; //item da NFe
            $std->cProd = '148';
            $std->cEAN = '17896061995917';
            // $std->cBarra;
            $std->xProd = 'FRALDA BABYSEC ULTRA MEGA G.........6X38';
            $std->NCM = '96190000';
            // $std->cBenef;
            $std->CFOP = '5405';
            $std->uCom = 'FD';
            $std->qCom = 14.0000;
            $std->vUnCom = 173.4000000000;
            $std->vProd = $std->qCom * $std->vUnCom;
            $std->cEANTrib = null;
            // $std->cBarraTrib;
            $std->uTrib = 'FD';
            $std->qTrib = 14.0000;
            $std->vUnTrib = 173.4000000000;
            $std->vFrete = null;
            $std->vSeg = null;
            $std->vDesc = null;
            $std->vOutro = null;
            $std->indTot = 1;
            // $std->xPed;
            // $std->nItemPed;
            // $std->nFCI;
            $nfe->tagprod($std);
            $valor_produto = $std->vProd;
            $valor_total = $std->qCom * $std->vUnCom;

            $std = new stdClass();
            $std->item = 1; //item da NFe
            $std->vTotTrib = $valor_total;
            $nfe->tagimposto($std);

            $std = new stdClass();
            $std->item = 1; //item da NFe
            $std->orig = 0;
            $std->CST = '00';
            $std->modBC = 0;
            $std->vBC = $valor_produto;
            $std->pICMS = 18.00;
            $std->vICMS = ($std->vBC * $std->pICMS) / 100;
            // $std->pFCP;
            // $std->vFCP;
            // $std->vBCFCP;
            // $std->modBCST;
            // $std->pMVAST;
            // $std->pRedBCST;
            // $std->vBCST;
            // $std->pICMSST;
            // $std->vICMSST;
            // $std->vBCFCPST;
            // $std->pFCPST;
            // $std->vFCPST;
            // $std->vICMSDeson;
            // $std->motDesICMS;
            // $std->pRedBC;
            // $std->vICMSOp;
            // $std->pDif;
            // $std->vICMSDif;
            // $std->vBCSTRet;
            // $std->pST;
            // $std->vICMSSTRet;
            // $std->vBCFCPSTRet;
            // $std->pFCPSTRet;
            // $std->vFCPSTRet;
            // $std->pRedBCEfet;
            // $std->vBCEfet;
            // $std->pICMSEfet;
            // $std->vICMSEfet;
            // $std->vICMSSubstituto; //NT 2020.005 v1.20
            // $std->vICMSSTDeson; //NT 2020.005 v1.20
            // $std->motDesICMSST; //NT 2020.005 v1.20
            // $std->pFCPDif; //NT 2020.005 v1.20
            // $std->vFCPDif; //NT 2020.005 v1.20
            // $std->vFCPEfet; //NT 2020.005 v1.20
            // $std->pRedAdRem; //NT 2023.001-v1.10
            // $std->qBCMono; //NT 2023.001-v1.10
            // $std->adRemiICMS; //NT 2023.001-v1.10
            // $std->vICMSMono; //NT 2023.001-v1.10
            // $std->adRemICMSRet; //NT 2023.001-v1.10
            // $std->vICMSMonoRet; //NT 2023.001-v1.10
            // $std->vICMSMonoDif; //NT 2023.001-v1.10
            // $std->vICMSMonoRet; //NT 2023.001-v1.10
            $nfe->tagICMS($std);

            $std = new stdClass();
            $std->item = 1; //item da NFe
            $std->CST = '49';
            $std->vBC = 0.00;
            $std->pPIS = 0.00;
            $std->vPIS = 0.00;
            $std->qBCProd = null;
            $std->vAliqProd = null;
            $nfe->tagPIS($std);

            $std = new stdClass();
            $std->item = 1; //item da NFe
            $std->CST = '49';
            $std->vBC = 0.00;
            $std->pCOFINS = 0.00;
            $std->vCOFINS = 0.00;
            $std->qBCProd = null;
            $std->vAliqProd = null;
            $nfe->tagCOFINS($std);

            $std = new stdClass();
            $std->modFrete = 9;
            $nfe->tagtransp($std);

            $std = new stdClass();
            $std->vTroco = null; //incluso no layout 4.00, obrigatório informar para NFCe (65)
            $nfe->tagpag($std);

            $std = new stdClass();
            $std->tPag = '01';
            $std->vPag = $valor_total; //Obs: deve ser informado o valor pago pelo cliente
            // $std->CNPJ = '12345678901234'; // da instituição de pagamento
            // $std->tBand = '01'; // bandeira do cartão
            // $std->cAut = '3333333';
            $std->tpIntegra = null; //incluso na NT 2015/002
            $std->indPag = '0'; //0= Pagamento à Vista 1= Pagamento à Prazo
            $nfe->tagdetPag($std);

            $std = new stdClass();
            $std->infAdFisco = 'informacoes para o fisco';
            $std->infCpl = 'informacoes complementares';
            $nfe->taginfAdic($std);

            $std = new stdClass();
            $std->qrcode = null; // = 'http://nfce.encat.org/desenvolvedor/qrcode/';
            $std->urlChave = null; // = 'http://nfce.encat.org/consumidor-nfce/consulte-nota-nfce';
            $nfe->taginfNFeSupl($std);

            // var_dump($nfe->errors);
            $this->_xml = $nfe->monta();
        } catch (\Exception $e) {
            $this->_erros[] = $e->getMessage();
            // echo $e->getMessage();
        }
    }

    public function gerarXml() {
        $dados = [];
        //$dados['pk_nItem'] = $this->_config['siglaUF'] . $this->_config['cnpj']

        try {
            $make = new Make();
        
            //infNFe OBRIGATÓRIA
            $std = new \stdClass();
            $std->Id = '';
            $std->versao = '4.00';
            $std->pk_nItem = null; // Deixe essa variavel sempre como null
            $infNFe = $make->taginfNFe($std);

            //ide OBRIGATÓRIA
            $std = new \stdClass();
            $std->cUF = 14;
            $std->cNF = '03701267';
            $std->natOp = 'VENDA CONSUMIDOR';
            $std->mod = 55;
            $std->serie = 1;
            $std->nNF = 100;
            $std->dhEmi = (new \DateTime())->format('Y-m-d\TH:i:sP');
            $std->dhSaiEnt = null;
            $std->tpNF = 1;
            $std->idDest = 1;
            $std->cMunFG = 1400100;
            $std->tpImp = 1;
            $std->tpEmis = 1;
            $std->cDV = '';
            $std->tpAmb = 2;
            $std->finNFe = 1;
            $std->indFinal = 0;
            $std->indPres = 0; // 0 = Operação sem intermediador
            $std->procEmi = 0;
            $std->verProc = '4.13';
            $std->dhCont = null;
            $std->xJust = null;
            $ide = $make->tagIde($std);
        
            //emit OBRIGATÓRIA
            $std = new \stdClass();
            $std->xNome = $this->_config['razaosocial'];
            $std->xFant = 'RAZAO';
            $std->IE = '111111111';
            // $std->IEST = null;
            //$std->IM = '95095870';
            // $std->CNAE = '4642701';
            $std->CRT = 1;
            $std->CNPJ = $this->_config['cnpj'];
            //$std->CPF = '12345678901'; //NÃO PASSE TAGS QUE NÃO EXISTEM NO CASO
            $emit = $make->tagemit($std);
        
            //enderEmit OBRIGATÓRIA
            $std = new \stdClass();
            $std->xLgr = 'Avenida Getúlio Vargas';
            $std->nro = '5022';
            $std->xCpl = 'LOJA 42';
            $std->xBairro = 'CENTRO';
            $std->cMun = 1400100;
            $std->xMun = 'BOA VISTA';
            $std->UF = 'RR';
            $std->CEP = '69301030';
            $std->cPais = 1058;
            $std->xPais = 'Brasil';
            $std->fone = '55555555';
            $ret = $make->tagenderemit($std);

            //dest OPCIONAL
            $std = new \stdClass();
            $std->xNome = 'Empresa Ltda';
            $std->CNPJ = '01234123456789';
            //$std->CPF = '12345678901';
            //$std->idEstrangeiro = 'AB1234';
            $std->indIEDest = 2;
            //$std->IE = '';
            //$std->ISUF = '12345679';
            //$std->IM = 'XYZ6543212';
            $std->email = 'seila@seila.com.br';
            $dest = $make->tagdest($std);
        
            //enderDest OPCIONAL
            $std = new \stdClass();
            $std->xLgr = 'Avenida Sebastião Diniz';
            $std->nro = '458';
            $std->xCpl = null;
            $std->xBairro = 'CENTRO';
            $std->cMun = 1400100;
            $std->xMun = 'Boa Vista';
            $std->UF = 'RR';
            $std->CEP = '69301088';
            $std->cPais = 1058;
            $std->xPais = 'Brasil';
            $std->fone = '1111111111';
            $ret = $make->tagenderdest($std);
        
            //prod OBRIGATÓRIA
            $std = new \stdClass();
            $std->item = 1;
            $std->cProd = '00341';
            $std->cEAN = 'SEM GTIN';
            $std->cEANTrib = 'SEM GTIN';
            $std->xProd = 'Produto com serviço';
            $std->NCM = '96081000';
            $std->CFOP = '5933';
            $std->uCom = 'JG';
            $std->uTrib = 'JG';
            $std->cBarra = NULL;
            $std->cBarraTrib = NULL;
            $std->qCom = '1';
            $std->qTrib = '1';
            $std->vUnCom = '200';
            $std->vUnTrib = '200';
            $std->vProd = $std->qCom * $std->vUnCom;
            $std->vDesc = NULL;
            $std->vOutro = NULL;
            $std->vSeg = NULL;
            $std->vFrete = NULL;
            $std->cBenef = NULL;
            $std->xPed = NULL;
            $std->nItemPed = NULL;
            $std->indTot = 1;
            $make->tagprod($std);
            
            //PIS
            $std = new stdClass();
            $std->item = 1; //item da NFe
            $std->CST = '99';
            $std->vBC = 200;
            $std->pPIS = 0.65;
            $std->vPIS = 13;
            $pis = $make->tagPIS($std);
            
            //COFINS
            $std = new stdClass();
            $std->item = 1; //item da NFe
            $std->CST = '99';
            $std->vBC = 200;
            $std->pCOFINS = 3;
            $std->vCOFINS = 60;
            $make->tagCOFINS($std);
        
            // Monta a tag de impostos mas não adiciona no xml
            $std = new stdClass();
            $std->item = 1; //item da NFe
            $std->vBC = 2.0;
            $std->vAliq = 8.0;
            $std->vISSQN = 0.16;
            $std->cMunFG = 1300029;
            $std->cMun = 1300029;
            $std->cPais = '1058';
            $std->cListServ = '01.01';
            $std->indISS = 1;
            $std->indIncentivo = 2;
            // Adiciona a tag de imposto ISSQN no xml
            $make->tagISSQN($std);
        
            //Imposto
            $std = new stdClass();
            $std->item = 1; //item da NFe
            $std->vTotTrib = 0;
            $make->tagimposto($std);
        
            // Item 2
            //prod OBRIGATÓRIA
            $std = new \stdClass();
            $std->item = 2; //item da NFe
            $std->cProd = '00065';
            $std->cEAN = 'SEM GTIN';
            $std->cEANTrib = 'SEM GTIN';
            $std->xProd = 'Coca Cola Lata 350 ml';
            $std->NCM = '22021000';
            $std->CFOP = '5101';
            $std->uCom = 'LAT';
            $std->uTrib = 'LAT';
            $std->cBarra = NULL;
            $std->cBarraTrib = NULL;
            $std->qCom = '1';
            $std->qTrib = '1';
            $std->vUnCom = '10.00';
            $std->vUnTrib = '10.00';
            $std->vProd = '10.00';
            $std->vDesc = NULL;
            $std->vOutro = NULL;
            $std->vSeg = NULL;
            $std->vFrete = NULL;
            $std->cBenef = NULL;
            $std->xPed = NULL;
            $std->nItemPed = NULL;
            $std->indTot = 1;
            // Como aqui se trata de um produto comum, não precisa passar a tag do imposto para a tag prod
            $prod = $make->tagprod($std);
        
            //Imposto
            $std = new stdClass();
            $std->item = 2; //item da NFe
            $std->vTotTrib = 0;
            $make->tagimposto($std);
        
            $std = new stdClass();
            $std->item = 2; //item da NFe
            $std->orig = '0';
            $std->CST = '00';
            $std->vICMS = 1.8;
            $std->pICMS = 18.0;
            $std->vBC = 10.00;
            $std->modBC = '3';
            $std->pFCP = NULL;
            $std->vFCP = NULL;
            $std->vBCFCP = NULL;
            $std->pRedBC = 0.0;
            $make->tagICMS($std);
        
            //PIS
            $std = new stdClass();
            $std->item = 2; //item da NFe
            $std->CST = '65';
            $std->vBC = 10;
            $std->pPIS = 0.65;
            $std->vPIS = 0.65;
            $pis = $make->tagPIS($std);
        
            //COFINS
            $std = new stdClass();
            $std->item = 2; //item da NFe
            $std->CST = '99';
            $std->vBC = 10;
            $std->pCOFINS = 3;
            $std->vCOFINS = 3;
            $make->tagCOFINS($std);
        
            //transp OBRIGATÓRIA
            $std = new \stdClass();
            $std->modFrete = 0;
            $transp = $make->tagtransp($std);
        
            //pag OBRIGATÓRIA
            $std = new \stdClass();
            $std->vTroco = 0;
            $pag = $make->tagpag($std);
        
            //detPag OBRIGATÓRIA
            $std = new \stdClass();
            $std->indPag = '0';
            $std->xPag = NULL;
            $std->tPag = '01';
            $std->vPag = 2.01;
            $detpag = $make->tagdetpag($std);
        
            $std = new stdClass();
            $std->CNPJ = '99999999999999'; //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
            $std->xContato = 'Fulano de Tal'; //Nome da pessoa a ser contatada
            $std->email = 'fulano@soft.com.br'; //E-mail da pessoa jurídica a ser contatada
            $std->fone = '1155551122'; //Telefone da pessoa jurídica/física a ser contatada
            //$std->CSRT = 'G8063VRTNDMO886SFNK5LDUDEI24XJ22YIPO'; //Código de Segurança do Responsável Técnico
            //$std->idCSRT = '01'; //Identificador do CSRT
            $make->taginfRespTec($std);
            
            $std = new \stdClass();
            $make->tagICMSTot($std);
            
            $std = new \stdClass();
            $std->dCompet = '2010-09-12';
            $std->cRegTrib = 6;
            $make->tagISSQNTot($std);
            $make->tagISSQNTot($std);
        
            $make->monta();
            $this->_xml = $make->getXML();
        
            // header('Content-Type: application/xml; charset=utf-8');
            // echo $xml;
        } catch (\Exception $e) {
            $this->_erros[] = $e->getMessage();
            // echo $e->getMessage();
        }
    }

    public function assinar($xml = '') {
        $xml = empty($xml) ? $this->_xml : $xml;

        try {
            // $configJson = json_encode($this->_config);
            // $pfxcontent = file_get_contents('C:\xampp\htdocs\intranet4\twsfw4\teste\expired_certificate.pfx');
    
            // $_tools = new Tools($configJson, Certificate::readPfx($pfxcontent, 'associacao'));
            // //$tools->disableCertValidation(true); //tem que desabilitar
            // $_tools->model('65');
    
            $this->_xml = $this->_tools->signNFe($xml);
        } catch (\Exception $e) {
            $this->_erros[] = $e->getMessage();
            // echo $e->getMessage();
        }
    }

    public function transmitir($xml = '') {
        // $resposta = $this->_tools->sefazEnviaLote([$xml], 1);

        // $st = new Standardize();
        // $stdResposta = $st->toStd($resposta);

        // return $stdResposta;

        $xml = empty($xml) ? $this->_xml : $xml;

        try {
            //$content = conteúdo do certificado PFX
            $idLote = str_pad(1, 15, '0', STR_PAD_LEFT);
            //envia o xml para pedir autorização ao SEFAZ
            $resp = $this->_tools->sefazEnviaLote([$xml], $idLote);

            //transforma o xml de retorno em um stdClass
            $st = new Standardize();
            $std = $st->toStd($resp);
            if ($std->cStat != 103) {
                //erro registrar e voltar
                return "[$std->cStat] $std->xMotivo";
            }
            $this->_recibo = $std->infRec->nRec;
            //esse recibo deve ser guardado para a proxima operação que é a consulta do recibo
            // header('Content-type: text/xml; charset=UTF-8');
            // echo $resp;
            $this->_resposta = $resp;
        } catch (\Exception $e) {
            $this->_erros[] = $e->getMessage();
            // echo $e->getMessage();
        }
    }
}