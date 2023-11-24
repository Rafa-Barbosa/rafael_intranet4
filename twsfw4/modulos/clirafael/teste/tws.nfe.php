<?php
global $config;
require_once $config['include'] . 'vendor\nfephp-org\sped-nfe\bootstrap.php';

use NFePHP\NFe\Tools;
use NFePHP\NFe\Make;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapFake;

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class nfe {
    var $funcoes_publicas = array(
        'index'             => true,
        'xml'               => true,
    );

    public function index() {
        $ret = '';

        $nfe = new NFeService();
        $nfe->gerarXml2();
        // $nfe->assinar();
        $xml = $nfe->getXml();

        $erros = $nfe->getErros();
        if(count($erros) > 0) {
            print_r($erros);
        } else {
            header('Content-type: text/xml; charset=UTF-8');
            echo $xml;
        }
        die();
    }

    public function tes() {
        global $config;
        // print_r($config);

        // $URLWebservice = 'https://nfse-hom.procempa.com.br/bhiss-ws/nfse?wsdl';
        // $options = array(
        //     'soap_version'=>SOAP_1_1,
        //     'exceptions'=>true,
        //     'trace'=>true,
        //     'cache_wsdl'=>WSDL_CACHE_MEMORY,
        //     'local_cert'=>'C:\xampp\htdocs\intranet4\twsfw4\teste',
        //     'passphrase'   => '15011221',
        //     'https' => array(
        //         'curl_verify_ssl_peer'  => true,
        //         'curl_verify_ssl_host'  => true
        //     )
        // );
        // $client = new SoapClient($URLWebservice, $options);

        // $options = [
        //     'uri' => 'https://nfe.sefazrs.rs.gov.br/ws/recepcaoevento/recepcaoevento4.asmx?wsdl',
        //     'location' => 'https://nfe.sefazrs.rs.gov.br/ws/recepcaoevento/recepcaoevento4.asmx'
        // ];

        // $options = [
        //     'soap_version'  => SOAP_1_1,
        //     'trace'         => true,
        // ];

        // $url = 'https://www.nfe.fazenda.gov.br/NFeRecepcaoEvento4/NFeRecepcaoEvento4.asmx?wsdl'; // produção
        $url = 'https://hom1.nfe.fazenda.gov.br/NFeRecepcaoEvento4/NFeRecepcaoEvento4.asmx?wsdl'; // homologação


        $arr = [
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
        $configJson = json_encode($arr);
        $pfxcontent = file_get_contents('C:\xampp\htdocs\intranet4\twsfw4\teste\expired_certificate.pfx');

        $tools = new Tools($configJson, Certificate::readPfx($pfxcontent, 'associacao'));
        //$tools->disableCertValidation(true); //tem que desabilitar
        $tools->model('65');

        try {

            $make = new Make();
        
            //infNFe OBRIGATÓRIA
            $std = new \stdClass();
            $std->Id = '';
            $std->versao = '4.00';
            $infNFe = $make->taginfNFe($std);
        
            //ide OBRIGATÓRIA
            $std = new \stdClass();
            $std->cUF = 14;
            $std->cNF = '03701267';
            $std->natOp = 'VENDA CONSUMIDOR';
            $std->mod = 65;
            $std->serie = 1;
            $std->nNF = 100;
            $std->dhEmi = (new \DateTime())->format('Y-m-d\TH:i:sP');
            $std->dhSaiEnt = null;
            $std->tpNF = 1;
            $std->idDest = 1;
            $std->cMunFG = 1400100;
            $std->tpImp = 1;
            $std->tpEmis = 1;
            $std->cDV = 2;
            $std->tpAmb = 2;
            $std->finNFe = 1;
            $std->indFinal = 1;
            $std->indPres = 1;
            $std->procEmi = 3;
            $std->verProc = '4.13';
            $std->dhCont = null;
            $std->xJust = null;
            $ide = $make->tagIde($std);
        
            //emit OBRIGATÓRIA
            $std = new \stdClass();
            $std->xNome = 'SUA RAZAO SOCIAL LTDA';
            $std->xFant = 'RAZAO';
            $std->IE = '111111111';
            $std->IEST = null;
            //$std->IM = '95095870';
            $std->CNAE = '4642701';
            $std->CRT = 1;
            $std->CNPJ = '99999999999999';
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
            $std->xNome = 'Eu Ltda';
            $std->CNPJ = '01234123456789';
            //$std->CPF = '12345678901';
            //$std->idEstrangeiro = 'AB1234';
            $std->indIEDest = 9;
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
            $std->vProd = '200';
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
            $xml = $make->getXML();
            
            $xml = $tools->signNFe($xml);
            $tools->sefazEnviaLote([$xml], 1);
            // Salva o xml
            // file_put_contents($config['baseFW']."xml".DIRECTORY_SEPARATOR."teste02.xml", $xml);
        
            header('Content-Type: application/xml; charset=utf-8');
            echo $xml;
            die();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }






        // $produtos = [];
        // $produtos[] = [
        //     'descricao' => 'Banco de couro',
        //     'ncm' => 213456,
        //     'valor' => 504.76,
        //     'unidade_tributaria' => '',
        //     'quantidade_tributavel' => '',
        //     'valor_tributacao' => ''
        // ];
        // $produtos[] = [
        //     'descricao' => 'Banco de tecido',
        //     'ncm' => 213456,
        //     'valor' => 115.8,
        //     'unidade_tributaria' => '',
        //     'quantidade_tributavel' => '',
        //     'valor_tributacao' => ''
        // ];
        
        // $param = [];
        // $param['produtos'] = $produtos;
        // echo $this->xml($param);
    }

    public function xml($param = []) {

        // ==================== DADOS DA NOTA ====================
        // Desc
        $dados['id_nf'] = 'NFe31060243816719000108550000000010001234567890'; // Exemplo de ID

        
        $dados['versao'] = 'Campos: cUF - AAMM - CNPJ/CPF - mod - serie - nNF - tpEmis - cNF - cDV';
        $dados['versao'] = '5206043300991100250655012000000780026730161'; // Exemplo de chave de acesso
        $dados['id_versao'] = $this->calcularModulo11($dados['versao']);

        /**
         * Código numérico que compõe a Chave de Acesso. Número
         * aleatório gerado pelo emitente para cada NF-e para evitar
         * acessos indevidos da NF-e. (v2.0)
         */
        $dados['cNF'] = ''; // B03 -- 8 dígitos
        $dados['natOp'] = ' '; // B04 -- 1-60 caracteres -- Descrição da Natureza da Operação

        /**
         * 55=NF-e emitida em substituição ao modelo 1 ou 1A;
         * 65=NFC-e, utilizada nas operações de venda no varejo (a
         * critério da UF aceitar este modelo de documento).
         */
        $dados['modelo'] = 55; // B06 -- 2 caracteres -- Código do Modelo do Documento Fiscal 
        $dados['serie'] = '000'; // ?? B07 1-3 caracteres
        $dados['nNF'] = ''; // B08 -- 1-9 caracteres -- Número do Documento Fiscal 
        $dados['data_emissao'] = date('Y-m-d H:i:s'); // B09

        // Data e hora de Saída ou da Entrada da Mercadoria/Produto (Não informar este campo para a NFC-e)
        $dados['saida_entrada'] = ''; // B10
        /**
         * 0 = Entrada
         * 1 = Saída
         */
        $dados['tipo_operacao'] = 1; // B11

        /**
         * 1=Operação interna;
         * 2=Operação interestadual;
         * 3=Operação com exterior
         */
        $dados['idDest'] = 1; // B11a
        $dados['cMunFG'] = 4304606; // 4304606 = Canoas B12 -- 7 caracteres

        /**
         * 0=Sem geração de DANFE;
         * 1=DANFE normal, Retrato;
         * 2=DANFE normal, Paisagem;
         * 3=DANFE Simplificado; 4=DANFE NFC-e;
         * 5=DANFE NFC-e em mensagem eletrônica (o envio de
         * mensagem eletrônica pode ser feita de forma simultânea
         * com a impressão do DANFE; usar o tpImp=5 quando esta
         * for a única forma de disponibilização do DANFE).
         */
        $dados['tipo_danfe'] = 1; // B21

        /**
         * 1=Emissão normal (não em contingência);
         * 2=Contingência FS-IA, com impressão do DANFE em
         * Formulário de Segurança - Impressor Autônomo;
         * 3=Contingência SCAN (Sistema de Contingência do
         * Ambiente Nacional); *Desativado * NT 2015/002
         * 4=Contingência EPEC (Evento Prévio da Emissão em
         * Contingência);
         * 5=Contingência FS-DA, com impressão do DANFE em
         * Formulário de Segurança - Documento Auxiliar;
         * 6=Contingência SVC-AN (SEFAZ Virtual de Contingência do
         * AN);
         * 7=Contingência SVC-RS (SEFAZ Virtual de Contingência do
         * RS);
         * 9=Contingência off-line da NFC-e;
         * Observação: Para a NFC-e somente é válida a opção de
         * contingência: 9-Contingência Off-Line e, a critério da UF,
         * opção 4-Contingência EPEC. (NT 2015/002
         */
        $dados['tipo_emissao'] = 1; // B22

        // 1=Produção; 2=Homologação
        $dados['tpAmb'] = 2; // B24 -- Identificação do Ambiente

        /**
         * 1=NF-e normal;
         * 2=NF-e complementar;
         * 3=NF-e de ajuste;
         * 4=Devolução de mercadoria
         */
        $dados['finNFe'] = 1; // B25 -- Finalidade de emissão da NF-e

        /**
         * 0=Normal;
         * 1=Consumidor final;
         */
        $dados['indFinal'] = 1; // B25a -- Indica operação com Consumidor final

        /**
         * 0=Não se aplica (por exemplo, Nota Fiscal complementar
         * ou de ajuste);
         * 1=Operação presencial;
         * 2=Operação não presencial, pela Internet;
         * 3=Operação não presencial, Teleatendimento;
         * 4=NFC-e em operação com entrega a domicílio;
         * 5=Operação presencial, fora do estabelecimento; (incluído
         * NT2016.002)
         * 9=Operação não presencial, outros.
         */
        $dados['indPres'] = 1; // B25b -- Indicador de presença do comprador no estabelecimento comercial no momento da operação

        /**
         * OPCIONAL
         * 0=Operação sem intermediador (em site ou plataforma
         * própria)
         * 1=Operação em site ou plataforma de terceiros
         * (intermediadores/marketplace)
         * * Considera-se intermediador/marketplace os prestadores
         * de serviços e de negócios referentes às transações
         * comerciais ou de prestação de serviços intermediadas,
         * realizadas por pessoas jurídicas inscritas no Cadastro
         * Nacional de Pessoa Jurídica - CNPJ ou pessoas físicas
         * inscritas no Cadastro de Pessoa Física - CPF, ainda que não
         * inscritas no cadastro de contribuintes do ICMS.
         * * Considera-se site/plataforma própria as vendas que não
         * foram intermediadas (por marketplace), como venda em
         * site próprio, teleatendimento.
         * (Criado na NT 2020.006)
         */
        $dados['indIntermed'] = ''; // B25c -- Indicador de intermediador/marketplace

        /**
         * 0=Emissão de NF-e com aplicativo do contribuinte;
         * 1=Emissão de NF-e avulsa pelo Fisco;
         * 2=Emissão de NF-e avulsa, pelo contribuinte com seu
         * certificado digital, através do site do Fisco;
         * 3=Emissão NF-e pelo contribuinte com aplicativo fornecido
         * pelo Fisco.
         */
        $dados['procEmi'] = 0; // ?? B26 -- Processo de emissão da NF-e

        $dados['verProc'] = '1.0'; // ?? B27 -- 1-20 caracteres -- Versão do Processo de emissão da NF-e


        // ==================== Informação de Documentos Fiscais referenciados ====================
        $dados['AAMM'] = date('ym'); // BA05 -- 4 caracteres -- Ano e Mês de emissão da NF-e 
        $dados['mod'] = '01'; // BA07 -- 2 caracteres -- Modelo do Documento Fiscal


        // ==================== Informações da NF de produtor rural referenciada ====================
        $dados['IE'] = ''; // BA15 -- 2-14 caracteres -- Informar a IE do emitente da NF de Produtor ou o literal “ISENTO” (v2.0)
        $dados['IEST'] = ''; // C18 -- 2-14 caracteres -- IE do Substituto Tributário

        // 04=NF de Produtor; 01=NF (v2.0)
        $dados['mod_df'] = '04'; // BA16 -- Modelo do Documento Fiscal


        // Utilizar esta TAG para referenciar um CT-e emitido
        // anteriormente, vinculada a NF-e atual - (v2.0).
        $dados['refCTe'] = ''; // BA19 -- 44 caracteres -- Chave de acesso do CT-e referenciada


        // ==================== Informações do Cupom Fiscal referenciado ====================
        /**
         * "2B"=Cupom Fiscal emitido por máquina registradora (não
         * ECF);
         * "2C"=Cupom Fiscal PDV;
         * "2D"=Cupom Fiscal (emitido por ECF) (v2.0).
         */
        $dados['mod_cf'] = '2B'; // BA21 -- Modelo do Documento Fiscal

        $dados['nECF'] = ''; // BA22 -- 3 caracteres -- Número de ordem sequencial do ECF

        $dados['nCOO'] = ''; // BA23 -- 6 caracteres -- Número do Contador de Ordem de Operação - COO


        
        // ==================== DADOS EMITENTE ====================
        $dados['uf_emit'] = '43'; // 43 = Rio Grande do Sul

        $dados['cpf_emit'] = ''; // C02a -- 11 caracteres
        $dados['cnpj_emit'] = '48998435000101'; // C02 -- 14 caracteres
        $dados['xNome'] = 'Exemplo LTDA'; // C03 -- 2-60 caracteres -- Razão Social ou Nome do emitente
        $dados['xFant'] = 'Grupo Exemplos'; // C04 -- 1-60 caracteres -- Nome fantasia

        // Endereço do emitente
        $dados['xLgr_emit'] = 'Rua exemplo'; // C06 -- 2-60 caracteres -- Logradouro
        $dados['nro_emit'] = 0; // C07 -- 1-60 caracteres -- Número
        $dados['xCpl_emit'] = ' '; // C08 -- 1-60 caracteres -- Complemento
        $dados['xBairro_emit'] = 'Mathias Velho'; // C09 -- 2-60 -- Bairro
        $dados['cMun_emit'] = '4304606'; // C10 -- 7 caracteres -- Código do município
        $dados['xMun_emit'] = 'Canoas'; // C11 -- 2-60 caracteres -- Nome do município
        $dados['UF_emit'] = 'RS'; // C12 -- 2 caracteres -- Sigla da UF
        $dados['CEP_emit'] = '92340230'; // C13 -- 8 caracteres
        $dados['fone_emit'] = '51999568396'; // C16 -- 6-14 caracteres
        $dados['cPais'] = 1058; // C14 -- Código do país
        $dados['xPais'] = 'Brasil'; // C15 -- Nome país

        /**
         * 1=Simples Nacional;
         * 2=Simples Nacional, excesso sublimite de receita bruta;
         * 3=Regime Normal. (v2.0).
         */
        $dados['CRT_emit'] = 1; // C21 -- Código de Regime Tributário



        // ==================== DADOS DESTINATÁRIO ====================
        $dados['cpfcnpj_dest'] = '86262653015'; // E02 E03
        $dados['xNome_dest'] = 'Cliente Exemplo'; // E04 -- 2-60 caracteres -- Razão Social ou nome do destinatário
        $dados['email_dest'] = 'exemplo@teste.com'; // E19 -- 1-60 caracteres -- e-mail de recepção da NF-e indicada pelo destinatário

        // Campo aceita nulo
        $dados['idEstrangeiro'] = ''; // E03a -- 0,5,20 caracteres Identificação do destinatário no caso de comprador estrangeiro

        /**
         * 1=Contribuinte ICMS (informar a IE do destinatário);
         * 2=Contribuinte isento de Inscrição no cadastro de
         * Contribuintes
         * 9=Não Contribuinte, que pode ou não possuir Inscrição
         * Estadual no Cadastro de Contribuintes do ICMS.
         * Nota 1: No caso de NFC-e informar indIEDest=9 e não
         * informar a tag IE do destinatário;
         * Nota 2: No caso de operação com o Exterior informar
         * indIEDest=9 e não informar a tag IE do destinatário;
         * Nota 3: No caso de Contribuinte Isento de Inscrição
         * (indIEDest=2), não informar a tag IE do destinatário.
         */
        $dados['indIEDest'] = ''; // E16a -- Indicador da IE do Destinatário
        $dados['IE_dest'] = ''; // E17 -- 2-14 caracteres -- Inscrição Estadual do Destinatário -- CAMPO OPCIONAL

        /**
         * Obrig.atório, nas operações que se beneficiam de
         * incentivos fiscais existentes nas áreas sob controle da
         * SUFRAMA. A omissão desta informação impede o
         * processamento da operação pelo Sistema de Mercadoria
         * Nacional da SUFRAMA e a liberação da Declaração de
         * Ingresso, prejudicando a comprovação do ingresso /
         * internamento da mercadoria nestas áreas. (v2.0)
         */
        $dados['ISUF'] = ''; // E18 -- 7-8 caracteres

        /**
         * Campo opcional, pode ser informado na NF-e conjugada,
         * com itens de produtos sujeitos ao ICMS e itens de serviços
         * sujeitos ao ISSQN.
         */
        $dados['IM'] = ''; // E18a -- 1-15 caracteres -- Inscrição Municipal do Tomador do Serviço

        // Endereço do destinatário
        $dados['xLgr_dest'] = 'Rua outro exemplo'; // E06 -- 2-60 -- Logradouro
        $dados['nro_dest'] = 1; // E07 -- 1-60 -- Número
        $dados['xCpl_dest'] = ''; // E08 -- 1-60 caracteres -- Complemento
        $dados['xBairro_dest'] = 'Harmonia'; // E09 -- 2-60 caracteres -- Bairro
        $dados['cMun_dest'] = '4304606'; // E10 -- 7 caracteres -- Código do Município
        $dados['xMun_dest'] = 'Canoas'; // E11 -- 2-60 caracteres -- Nome do município
        $dados['UF_dest'] = 'RS'; // E12 -- 2 caracteres
        $dados['CEP_dest'] = '92325390'; // E13 -- 8 caracteres
        $dados['fone_dest'] = '51999568396'; // E16 -- 6-14 caracteres



        // ==================== DETALHAMENTO DE PRODUTOS E SERVIÇOS MAX 990 ====================
        if(is_array($param['produtos']) && count($param['produtos']) > 0) {
            $itens = [];
            $numero_item = 0;
            foreach($param['produtos'] as $item) {
                $numero_item++;

                $temp = [];
                $temp['nItem'] = $numero_item; // H02 -- 1-3 caracteres

                /**
                 * Preencher com CFOP, caso se trate de itens não
                 * relacionados com mercadorias/produtos e que o
                 * contribuinte não possua codificação própria.
                 * Formato: ”CFOP9999”
                 */
                $temp['cProd'] = ''; // I02 -- 1-60 caracteres -- Código do produto ou serviço

                /**
                 * Preencher com o código GTIN-8, GTIN-12, GTIN-13 ou
                 * GTIN-14 (antigos códigos EAN, UPC e DUN-14)
                 * Para produtos que não possuem código de barras com
                 * GTIN, deve ser informado o literal “SEM GTIN”;
                 * (atualizado NT 2017/001)
                 */
                $temp['cEAN'] = ''; // I03 -- 0,8,12,13,14 -- GTIN (Global Trade Item Number) do produto, antigo código EAN ou código de barras

                $temp['xProd'] = $item['descricao']; // I04 -- 1-120 caracteres
                $temp['NCM'] = str_pad($item['ncm'], 8, '0', STR_PAD_LEFT); // I05 -- 2,8 caracteres -- Código NCM com 8 dígitos
                $temp['NVE'] = $item['nve'] ?? []; // I05a -- 0-8 ocorrencia -- 6 caracteres -- Codificação NVE - Nomenclatura de Valor Aduaneiro e Estatística.

                /**
                 * Código de Benefício Fiscal utilizado pela UF, aplicado ao
                 * item.
                 * Obs.: Deve ser utilizado o mesmo código adotado na EFD e
                 * outras declarações, nas UF que o exigem.
                 * (Incluído na NT2016.002)
                 */
                $temp['cBenef'] = ''; // DESCOBRIR I05f -- 8,10 caracteres -- Código de Benefício Fiscal na UF aplicado ao item
                $temp['EXTIPI'] = ''; // I06 2-3 caracteres -- Preencher de acordo com o código EX da TIPI. Em caso de serviço, não incluir a TAG.
                $temp['CFOP'] = ''; // I08 -- 4 caracteres -- Código Fiscal de Operações e Prestações
                $temp['uCom'] = ''; // I09 -- 1-6 caracteres -- Unidade Comercial
                $temp['qCom'] = $item['quantidade'] ?? 1; // I10 -- 11v0-4 caracteres -- Quantidade Comercial
                $temp['vUnCom'] = $item['valor']; // I10a -- 11v0-10 -- Valor Unitário de Comercialização
                $temp['vProd'] = $temp['vUnCom'] * $temp['qCom']; // I11 -- 13v2 caracteres -- Valor Total Bruto dos Produtos ou Serviços

                /**
                 * Preencher com o código GTIN-8, GTIN-12, GTIN-13 ou
                 * GTIN-14 (antigos códigos EAN, UPC e DUN-14) da
                 * unidade tributável do produto.
                 * O GTIN da unidade tributável deve corresponder àquele da
                 * menor unidade comercializável identificada por
                 * código GTIN.
                 * Para produtos que não possuem código de barras com
                 * GTIN, deve ser informado o literal "SEM GTIN”;
                 * (Atualizado NT 2017.001)
                 */
                $temp['cEANTrib'] = $item['GTIN'] ?? 'SEM GTIN'; // I12 -- 0,8,12,13,14 caracteres -- GTIN (Global Trade Item Number) da unidade tributável, antigo código EAN ou código de barras
                $temp['uTrib'] = $item['unidade_tributaria']; // I13 -- 1-6 caracteres -- Unidade Tributável
                $temp['qTrib'] = $item['quantidade_tributavel']; // I14 -- 11v0-4 -- Quantidade Tributável
                $temp['vUnTrib'] = $item['valor_tributacao']; // I14a -- 11v0-10 -- Valor Unitário de tributação
                $temp['vFrete'] = $item['valor_frete'] ?? 0; // I15 -- 13v2 -- Valor Total do Frete
                $temp['vSeg'] = $item['valor_seguro'] ?? 0; // I16 -- 13v2 -- Valor Total do Seguro
                $temp['vDesc'] = $item['valor_desconto'] ?? 0; // I17 -- 13v2 -- Valor do Desconto
                $temp['vOutro'] = $item['outras_despesas'] ?? 0; // I17a -- 13v2 -- Outras despesas acessórias

                /**
                 * 0=Valor do item (vProd) não compõe o valor total da NF-e
                 * 1=Valor do item (vProd) compõe o valor total da NF- e (vProd) (v2.0)
                 */
                $temp['indTot'] = 1; // I17b -- Indica se valor do Item (vProd) entra no valor total da NF-e (vProd)

                $itens[] = $temp;
            }
        } else {
            // return 'Erro: Sem itens identificados!';
        }


        
        /*
        Total de 44 caracteres
        Campo       Quant. Caracteres
        
        cUF         02
        AAMM        04
        CNPJ/CPF    14
        mod         02
        serie       03
        nNF         09
        tpEmis      01
        cNF         08
        cDV         01
        */
        $dados['chave_acesso'] = $dados['uf_emit']
                                    . $dados['AAMM']
                                    . str_pad((!empty($dados['cnpj_emit']) ? $dados['cnpj_emit'] : $dados['cpf_emit']), 14, '0', STR_PAD_LEFT)
                                    . $dados['modelo'] . substr($dados['serie'], 0, 3)
                                    . str_pad($dados['nNF'], 9, '0', STR_PAD_LEFT)
                                    . $dados['tipo_emissao']
                                    . str_pad($dados['cNF'], 8, '0', STR_PAD_LEFT);
        // $dados['chave_acesso'] = '5206043300991100250655012000000780026730161'; // Exemplo de chave de acesso
        $dados['cDV'] = $this->calcularModulo11($dados['chave_acesso']); // B23
        $dados['chave_acesso'] .= $dados['cDV'];
        
        // UF, CNPJ ou CPF do Emitente, Série e Número da NF-e
        $dados['pk_nItem'] = $dados['uf_emit'].(!empty($dados['cnpj_emit']) ? $dados['cnpj_emit'] : $dados['cpf_emit']).$dados['serie'];


        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // TAG raiz
        $NFe = $dom->createElementNS('http://www.portalfiscal.inf.br/nfe', 'NFe'); {
            $infNFe = $dom->createElement('infNFe'); {
                $infNFe->setAttribute('Id', 'NFe' . $dados['chave_acesso']);
                $infNFe->setAttribute('Versao', '1.01');

                $pk_nItem = $dom->createElement('pk_nItem');
                $pk_nItem->appendChild($dom->createTextNode($dados['pk_nItem']));
                $infNFe->appendChild($pk_nItem);

                $ide = $dom->createElement('ide'); {
                    $cUF = $dom->createElement('cUF');
                    $cUF->appendChild($dom->createTextNode($dados['uf_emit']));
                    $ide->appendChild($cUF);

                    $cNF = $dom->createElement('cNF');
                    $cNF->appendChild($dom->createTextNode($dados['cNF']));
                    $ide->appendChild($cNF);

                    $natOp = $dom->createElement('natOp');
                    $natOp->appendChild($dom->createTextNode($dados['natOp']));
                    $ide->appendChild($natOp);

                    $mod = $dom->createElement('mod');
                    $mod->appendChild($dom->createTextNode($dados['modelo']));
                    $ide->appendChild($mod);

                    $serie = $dom->createElement('serie');
                    $serie->appendChild($dom->createTextNode($dados['serie']));
                    $ide->appendChild($serie);

                    $nNF = $dom->createElement('nNF');
                    $nNF->appendChild($dom->createTextNode($dados['nNF']));
                    $ide->appendChild($nNF);

                    $dhEmi = $dom->createElement('dhEmi');
                    $dhEmi->appendChild($dom->createTextNode($dados['data_emissao']));
                    $ide->appendChild($dhEmi);
                    
                    if($dados['modelo'] != 65) {
                        $dhSaiEnt = $dom->createElement('dhSaiEnt'); // Opcional
                        $dhSaiEnt->appendChild($dom->createTextNode($dados['saida_entrada']));
                        $ide->appendChild($dhSaiEnt);
                    }

                    $tpNF = $dom->createElement('tpNF');
                    $tpNF->appendChild($dom->createTextNode($dados['tipo_operacao']));
                    $ide->appendChild($tpNF);

                    $idDest = $dom->createElement('idDest');
                    $idDest->appendChild($dom->createTextNode($dados['idDest']));
                    $ide->appendChild($idDest);

                    $cMunFG = $dom->createElement('cMunFG');
                    $cMunFG->appendChild($dom->createTextNode($dados['cMunFG']));
                    $ide->appendChild($cMunFG);

                    $tpImp = $dom->createElement('tpImp');
                    $tpImp->appendChild($dom->createTextNode($dados['tipo_danfe']));
                    $ide->appendChild($tpImp);

                    $tpEmis = $dom->createElement('tpEmis');
                    $tpEmis->appendChild($dom->createTextNode($dados['tipo_emissao']));
                    $ide->appendChild($tpEmis);

                    $cDV = $dom->createElement('cDV');
                    $cDV->appendChild($dom->createTextNode($dados['cDV']));
                    $ide->appendChild($cDV);

                    $tpAmb = $dom->createElement('tpAmb');
                    $tpAmb->appendChild($dom->createTextNode($dados['tpAmb']));
                    $ide->appendChild($tpAmb);

                    $finNFe = $dom->createElement('finNFe');
                    $finNFe->appendChild($dom->createTextNode($dados['finNFe']));
                    $ide->appendChild($finNFe);

                    $indFinal = $dom->createElement('indFinal');
                    $indFinal->appendChild($dom->createTextNode($dados['indFinal']));
                    $ide->appendChild($indFinal);

                    $indPres = $dom->createElement('indPres');
                    $indPres->appendChild($dom->createTextNode($dados['indPres']));
                    $ide->appendChild($indPres);

                    if(!empty($dados['indIntermed'])) {
                        $indIntermed = $dom->createElement('indIntermed'); // Opcional
                        $indIntermed->appendChild($dom->createTextNode($dados['indIntermed']));
                        $ide->appendChild($indIntermed);
                    }

                    $procEmi = $dom->createElement('procEmi');
                    $procEmi->appendChild($dom->createTextNode($dados['procEmi']));
                    $ide->appendChild($procEmi);

                    $verProc = $dom->createElement('verProc');
                    $verProc->appendChild($dom->createTextNode($dados['verProc']));
                    $ide->appendChild($verProc);

                    // $x = $dom->createElement('x'); { // Grupo opcional
                    //     $dhCont = $dom->createElement('dhCont');
                    //     $dhCont->appendChild($dom->createTextNode(''));
                    //     $x->appendChild($dhCont);

                    //     $xJust = $dom->createElement('xJust');
                    //     $xJust->appendChild($dom->createTextNode(''));
                    //     $x->appendChild($xJust);
                    // }
                    // $ide->appendChild($x);

                    $NFref = $dom->createElement('NFref'); {
                        $refNFe = $dom->createElement('refNFe');
                        $refNFe->appendChild($dom->createTextNode($dados['chave_acesso']));
                        $NFref->appendChild($refNFe);

                        $refNF = $dom->createElement('refNF'); {
                            $cUF = $dom->createElement('cUF');
                            $cUF->appendChild($dom->createTextNode($dados['uf_emit']));
                            $refNF->appendChild($cUF);

                            $AAMM = $dom->createElement('AAMM');
                            $AAMM->appendChild($dom->createTextNode($dados['AAMM']));
                            $refNF->appendChild($AAMM);

                            $CNPJ = $dom->createElement('CNPJ');
                            $CNPJ->appendChild($dom->createTextNode($dados['cnpj_emit']));
                            $refNF->appendChild($CNPJ);

                            $mod = $dom->createElement('mod');
                            $mod->appendChild($dom->createTextNode($dados['mod']));
                            $refNF->appendChild($mod);

                            $serie = $dom->createElement('serie');
                            $serie->appendChild($dom->createTextNode($dados['serie']));
                            $refNF->appendChild($serie);

                            $nNF = $dom->createElement('nNF');
                            $nNF->appendChild($dom->createTextNode($dados['nNF']));
                            $refNF->appendChild($nNF);
                        }
                        $NFref->appendChild($refNF);

                        $refNFP = $dom->createElement('refNFP'); {
                            $cUF = $dom->createElement('cUF');
                            $cUF->appendChild($dom->createTextNode($dados['uf_emit']));
                            $refNFP->appendChild($cUF);

                            $AAMM = $dom->createElement('AAMM');
                            $AAMM->appendChild($dom->createTextNode($dados['AAMM']));
                            $refNFP->appendChild($AAMM);

                            if(!empty($dados['cnpj_emit'])) {
                                $CNPJ = $dom->createElement('CNPJ');
                                $CNPJ->appendChild($dom->createTextNode($dados['cnpj_emit']));
                                $refNFP->appendChild($CNPJ);
                            } else {
                                $CPF = $dom->createElement('CPF');
                                $CPF->appendChild($dom->createTextNode($dados['cpf_emit']));
                                $refNFP->appendChild($CPF);
                            }

                            $IE = $dom->createElement('IE');
                            $IE->appendChild($dom->createTextNode($dados['IE']));
                            $refNFP->appendChild($IE);

                            $mod = $dom->createElement('mod');
                            $mod->appendChild($dom->createTextNode($dados['mod_df']));
                            $refNFP->appendChild($mod);

                            $serie = $dom->createElement('serie');
                            $serie->appendChild($dom->createTextNode($dados['serie']));
                            $refNFP->appendChild($serie);

                            $nNF = $dom->createElement('nNF');
                            $nNF->appendChild($dom->createTextNode($dados['nNF']));
                            $refNFP->appendChild($nNF);
                        }
                        $NFref->appendChild($refNFP);

                        $refCTe = $dom->createElement('refCTe');
                        $refCTe->appendChild($dom->createTextNode($dados['refCTe']));
                        $NFref->appendChild($refCTe);

                        $refECF = $dom->createElement('refECF'); {
                            $mod = $dom->createElement('mod');
                            $mod->appendChild($dom->createTextNode($dados['mod_cf']));
                            $refECF->appendChild($mod);

                            $nECF = $dom->createElement('nECF');
                            $nECF->appendChild($dom->createTextNode($dados['nECF']));
                            $refECF->appendChild($nECF);

                            $nCOO = $dom->createElement('nCOO');
                            $nCOO->appendChild($dom->createTextNode($dados['nCOO']));
                            $refECF->appendChild($nCOO);
                        }
                        $NFref->appendChild($refECF);
                    }
                    $ide->appendChild($NFref);
                }
                $infNFe->appendChild($ide);

                $emit = $dom->createElement('emit'); {
                    if(!empty($dados['cnpj_emit'])) {
                        $CNPJ = $dom->createElement('CNPJ');
                        $CNPJ->appendChild($dom->createTextNode($dados['cnpj_emit']));
                        $emit->appendChild($CNPJ);
                    } else {
                        $CPF = $dom->createElement('CPF');
                        $CPF->appendChild($dom->createTextNode($dados['cpf_emit']));
                        $emit->appendChild($CPF);
                    }

                    $xNome = $dom->createElement('xNome');
                    $xNome->appendChild($dom->createTextNode($dados['xNome']));
                    $emit->appendChild($xNome);

                    $xFant = $dom->createElement('xFant');
                    $xFant->appendChild($dom->createTextNode($dados['xFant']));
                    $emit->appendChild($xFant);

                    $enderEmit = $dom->createElement('enderEmit'); {
                        $xLgr = $dom->createElement('xLgr');
                        $xLgr->appendChild($dom->createTextNode($dados['xLgr_emit']));
                        $enderEmit->appendChild($xLgr);

                        $nro = $dom->createElement('nro');
                        $nro->appendChild($dom->createTextNode($dados['nro_emit']));
                        $enderEmit->appendChild($nro);

                        $xCpl = $dom->createElement('xCpl');
                        $xCpl->appendChild($dom->createTextNode($dados['xCpl_emit']));
                        $enderEmit->appendChild($xCpl);

                        $xBairro = $dom->createElement('xBairro');
                        $xBairro->appendChild($dom->createTextNode($dados['xBairro_emit']));
                        $enderEmit->appendChild($xBairro);

                        $cMun = $dom->createElement('cMun');
                        $cMun->appendChild($dom->createTextNode($dados['cMun_emit']));
                        $enderEmit->appendChild($cMun);

                        $xMun = $dom->createElement('xMun');
                        $xMun->appendChild($dom->createTextNode($dados['xMun_emit']));
                        $enderEmit->appendChild($xMun);

                        $UF = $dom->createElement('UF');
                        $UF->appendChild($dom->createTextNode($dados['UF_emit']));
                        $enderEmit->appendChild($UF);

                        $CEP = $dom->createElement('CEP');
                        $CEP->appendChild($dom->createTextNode($dados['CEP_emit']));
                        $enderEmit->appendChild($CEP);

                        $cPais = $dom->createElement('cPais');
                        $cPais->appendChild($dom->createTextNode($dados['cPais']));
                        $enderEmit->appendChild($cPais);

                        $xPais = $dom->createElement('xPais'); // Opcional
                        $xPais->appendChild($dom->createTextNode($dados['xPais']));
                        $enderEmit->appendChild($xPais);

                        $fone = $dom->createElement('fone'); // Opcional
                        $fone->appendChild($dom->createTextNode($dados['fone_emit']));
                        $enderEmit->appendChild($fone);
                    }
                    $emit->appendChild($enderEmit);

                    $IE = $dom->createElement('IE');
                    $IE->appendChild($dom->createTextNode($dados['IE']));
                    $emit->appendChild($IE);

                    $IEST = $dom->createElement('IEST'); // Opcional
                    $IEST->appendChild($dom->createTextNode($dados['IEST']));
                    $emit->appendChild($IEST);

                    // $x = $dom->createElement('x'); { // Grupo Opcional
                    //     $IM = $dom->createElement('IM');
                    //     $IM->appendChild($dom->createTextNode(''));
                    //     $x->appendChild($IM);

                    //     $CNAE = $dom->createElement('CNAE'); // Opcional
                    //     $CNAE->appendChild($dom->createTextNode(''));
                    //     $x->appendChild($CNAE);
                    // }
                    // $emit->appendChild($x);

                    $CRT = $dom->createElement('CRT');
                    $CRT->appendChild($dom->createTextNode($dados['CRT_emit']));
                    $emit->appendChild($CRT);
                }
                $infNFe->appendChild($emit);

                $dest = $dom->createElement('dest'); {
                    if(strlen($dados['cpfcnpj_dest']) > 11) {
                        $CNPJ = $dom->createElement('CNPJ');
                        $CNPJ->appendChild($dom->createTextNode($dados['cpfcnpj_dest']));
                        $dest->appendChild($CNPJ);
                    } else {
                        $CPF = $dom->createElement('CPF');
                        $CPF->appendChild($dom->createTextNode($dados['cpfcnpj_dest']));
                        $dest->appendChild($CPF);
                    }

                    if(!empty($dados['idEstrangeiro'])) {
                        $idEstrangeiro = $dom->createElement('idEstrangeiro');
                        $idEstrangeiro->appendChild($dom->createTextNode($dados['idEstrangeiro']));
                        $dest->appendChild($idEstrangeiro);
                    }

                    $xNome = $dom->createElement('xNome');
                    $xNome->appendChild($dom->createTextNode($dados['xNome_dest']));
                    $dest->appendChild($xNome);

                    $enderDest = $dom->createElement('enderDest'); {
                        $xLgr = $dom->createElement('xLgr');
                        $xLgr->appendChild($dom->createTextNode($dados['xLgr_dest']));
                        $enderDest->appendChild($xLgr);

                        $nro = $dom->createElement('nro');
                        $nro->appendChild($dom->createTextNode($dados['nro_dest']));
                        $enderDest->appendChild($nro);

                        $xCpl = $dom->createElement('xCpl'); // Opcional
                        $xCpl->appendChild($dom->createTextNode($dados['xCpl_dest']));
                        $enderDest->appendChild($xCpl);

                        $xBairro = $dom->createElement('xBairro');
                        $xBairro->appendChild($dom->createTextNode($dados['xBairro_dest']));
                        $enderDest->appendChild($xBairro);

                        $cMun = $dom->createElement('cMun');
                        $cMun->appendChild($dom->createTextNode($dados['cMun_dest']));
                        $enderDest->appendChild($cMun);

                        $xMun = $dom->createElement('xMun');
                        $xMun->appendChild($dom->createTextNode($dados['xMun_dest']));
                        $enderDest->appendChild($xMun);

                        $UF = $dom->createElement('UF');
                        $UF->appendChild($dom->createTextNode($dados['UF_dest']));
                        $enderDest->appendChild($UF);

                        $CEP = $dom->createElement('CEP');
                        $CEP->appendChild($dom->createTextNode($dados['CEP_dest']));
                        $enderDest->appendChild($CEP);

                        $cPais = $dom->createElement('cPais');
                        $cPais->appendChild($dom->createTextNode($dados['cPais']));
                        $enderDest->appendChild($cPais);

                        $xPais = $dom->createElement('xPais');
                        $xPais->appendChild($dom->createTextNode($dados['xPais']));
                        $enderDest->appendChild($xPais);

                        $fone = $dom->createElement('fone');
                        $fone->appendChild($dom->createTextNode($dados['fone_dest']));
                        $enderDest->appendChild($fone);
                    }
                    $dest->appendChild($enderDest);

                    $indIEDest = $dom->createElement('indIEDest');
                    $indIEDest->appendChild($dom->createTextNode($dados['indIEDest']));
                    $dest->appendChild($indIEDest);

                    if(!empty($dados['IE_dest'])) {
                        $IE = $dom->createElement('IE'); // Opcional
                        $IE->appendChild($dom->createTextNode($dados['IE_dest']));
                        $dest->appendChild($IE);
                    }

                    $ISUF = $dom->createElement('ISUF');
                    $ISUF->appendChild($dom->createTextNode($dados['ISUF']));
                    $dest->appendChild($ISUF);

                    if(!empty($dados['IM'])) {
                        $IM = $dom->createElement('IM'); // Opcional
                        $IM->appendChild($dom->createTextNode($dados['IM']));
                        $dest->appendChild($IM);
                    }

                    $email = $dom->createElement('email');
                    $email->appendChild($dom->createTextNode($dados['email_dest']));
                    $dest->appendChild($email);
                }
                $infNFe->appendChild($dest);

                // $retirada = $dom->createElement('retirada'); { // Informar somente se diferente do endereço do remetente.
                //     $CNPJ = $dom->createElement('CNPJ');
                //     $CNPJ->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($CNPJ);

                //     $CPF = $dom->createElement('CPF');
                //     $CPF->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($CPF);

                //     $xNome = $dom->createElement('xNome');
                //     $xNome->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($xNome);

                //     $xLgr = $dom->createElement('xLgr');
                //     $xLgr->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($xLgr);

                //     $nro = $dom->createElement('nro');
                //     $nro->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($nro);

                //     $xCpl = $dom->createElement('xCpl');
                //     $xCpl->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($xCpl);

                //     $xBairro = $dom->createElement('xBairro');
                //     $xBairro->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($xBairro);

                //     $cMun = $dom->createElement('cMun');
                //     $cMun->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($cMun);

                //     $xMun = $dom->createElement('xMun');
                //     $xMun->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($xMun);

                //     $UF = $dom->createElement('UF');
                //     $UF->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($UF);

                //     $CEP = $dom->createElement('CEP');
                //     $CEP->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($CEP);

                //     $cPais = $dom->createElement('cPais');
                //     $cPais->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($cPais);

                //     $xPais = $dom->createElement('xPais');
                //     $xPais->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($xPais);

                //     $fone = $dom->createElement('fone');
                //     $fone->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($fone);

                //     $email = $dom->createElement('email');
                //     $email->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($email);

                //     $IE = $dom->createElement('IE');
                //     $IE->appendChild($dom->createTextNode(''));
                //     $retirada->appendChild($IE);
                // }
                // $infNFe->appendChild($retirada);

                // $entrega = $dom->createElement('entrega'); { // Informar somente se diferente do endereço do destinatário.
                //     $CNPJ = $dom->createElement('CNPJ');
                //     $CNPJ->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($CNPJ);

                //     $CPF = $dom->createElement('CPF');
                //     $CPF->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($CPF);

                //     $xNome = $dom->createElement('xNome');
                //     $xNome->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($xNome);

                //     $xLgr = $dom->createElement('xLgr');
                //     $xLgr->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($xLgr);

                //     $nro = $dom->createElement('nro');
                //     $nro->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($nro);

                //     $xCpl = $dom->createElement('xCpl');
                //     $xCpl->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($xCpl);

                //     $xBairro = $dom->createElement('xBairro');
                //     $xBairro->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($xBairro);

                //     $cMun = $dom->createElement('cMun');
                //     $cMun->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($cMun);

                //     $xMun = $dom->createElement('xMun');
                //     $xMun->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($xMun);

                //     $UF = $dom->createElement('UF');
                //     $UF->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($UF);

                //     $CEP = $dom->createElement('CEP');
                //     $CEP->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($CEP);

                //     $cPais = $dom->createElement('cPais');
                //     $cPais->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($cPais);

                //     $xPais = $dom->createElement('xPais');
                //     $xPais->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($xPais);

                //     $fone = $dom->createElement('fone');
                //     $fone->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($fone);

                //     $email = $dom->createElement('email');
                //     $email->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($email);

                //     $IE = $dom->createElement('IE');
                //     $IE->appendChild($dom->createTextNode(''));
                //     $entrega->appendChild($IE);
                // }
                // $infNFe->appendChild($entrega);

                foreach($itens as $item) {
                    $det = $dom->createElement('det'); { // Múltiplas ocorrências (máximo = 990)
                        $nItem = $dom->createElement('nItem');
                        $nItem->appendChild($dom->createTextNode($item['nItem']));
                        $det->appendChild($nItem);
    
                        $prod = $dom->createElement('prod'); {
                            $cProd = $dom->createElement('cProd');
                            $cProd->appendChild($dom->createTextNode($item['cProd']));
                            $prod->appendChild($cProd);
    
                            $cEAN = $dom->createElement('cEAN');
                            $cEAN->appendChild($dom->createTextNode($item['cEAN']));
                            $prod->appendChild($cEAN);
    
                            $xProd = $dom->createElement('xProd');
                            $xProd->appendChild($dom->createTextNode($item['xProd']));
                            $prod->appendChild($xProd);
    
                            $NCM = $dom->createElement('NCM');
                            $NCM->appendChild($dom->createTextNode($item['NCM']));
                            $prod->appendChild($NCM);
    
                            if(is_array($item['NVE']) && count($item['NVE']) > 0) {
                                foreach($item['NVE'] as $nve) {
                                    $NVE = $dom->createElement('NVE');
                                    $NVE->appendChild($dom->createTextNode($nve));
                                    $prod->appendChild($NVE);
                                }
                            }
    
                            // $x = $dom->createElement('x'); {
                            //     $CEST = $dom->createElement('CEST');
                            //     $CEST->appendChild($dom->createTextNode(''));
                            //     $x->appendChild($CEST);
    
                            //     $indEscala = $dom->createElement('indEscala');
                            //     $indEscala->appendChild($dom->createTextNode(''));
                            //     $x->appendChild($indEscala);
    
                            //     $CNPJFab = $dom->createElement('CNPJFab');
                            //     $CNPJFab->appendChild($dom->createTextNode(''));
                            //     $x->appendChild($CNPJFab);
                            // }
                            // $prod->appendChild($x);
    
                            $cBenef = $dom->createElement('cBenef');
                            $cBenef->appendChild($dom->createTextNode($item['cBenef']));
                            $prod->appendChild($cBenef);
    
                            $EXTIPI = $dom->createElement('EXTIPI');
                            $EXTIPI->appendChild($dom->createTextNode($item['EXTIPI']));
                            $prod->appendChild($EXTIPI);
    
                            $CFOP = $dom->createElement('CFOP');
                            $CFOP->appendChild($dom->createTextNode($item['CFOP']));
                            $prod->appendChild($CFOP);
    
                            $uCom = $dom->createElement('uCom');
                            $uCom->appendChild($dom->createTextNode($item['uCom']));
                            $prod->appendChild($uCom);
    
                            $qCom = $dom->createElement('qCom');
                            $qCom->appendChild($dom->createTextNode($item['qCom']));
                            $prod->appendChild($qCom);
    
                            $vUnCom = $dom->createElement('vUnCom');
                            $vUnCom->appendChild($dom->createTextNode($item['vUnCom']));
                            $prod->appendChild($vUnCom);
    
                            $vProd = $dom->createElement('vProd');
                            $vProd->appendChild($dom->createTextNode($item['vProd']));
                            $prod->appendChild($vProd);
    
                            $cEANTrib = $dom->createElement('cEANTrib');
                            $cEANTrib->appendChild($dom->createTextNode($item['cEANTrib']));
                            $prod->appendChild($cEANTrib);
    
                            $uTrib = $dom->createElement('uTrib');
                            $uTrib->appendChild($dom->createTextNode($item['uTrib']));
                            $prod->appendChild($uTrib);
    
                            $qTrib = $dom->createElement('qTrib');
                            $qTrib->appendChild($dom->createTextNode($item['qTrib']));
                            $prod->appendChild($qTrib);
    
                            $vUnTrib = $dom->createElement('vUnTrib');
                            $vUnTrib->appendChild($dom->createTextNode($item['vUnTrib']));
                            $prod->appendChild($vUnTrib);
    
                            if($item['vFrete'] > 0) {
                                $vFrete = $dom->createElement('vFrete');
                                $vFrete->appendChild($dom->createTextNode($item['vFrete']));
                                $prod->appendChild($vFrete);
                            }
    
                            if($item['vSeg'] > 0) {
                                $vSeg = $dom->createElement('vSeg');
                                $vSeg->appendChild($dom->createTextNode($item['vSeg']));
                                $prod->appendChild($vSeg);
                            }
    
                            if($item['vDesc'] > 0) {
                                $vDesc = $dom->createElement('vDesc');
                                $vDesc->appendChild($dom->createTextNode($item['vDesc']));
                                $prod->appendChild($vDesc);
                            }
    
                            if($item['vOutro'] > 0) {
                                $vOutro = $dom->createElement('vOutro');
                                $vOutro->appendChild($dom->createTextNode($item['vOutro']));
                                $prod->appendChild($vOutro);
                            }
    
                            $indTot = $dom->createElement('indTot');
                            $indTot->appendChild($dom->createTextNode($item['indTot']));
                            $prod->appendChild($indTot);
    
                            $xPed = $dom->createElement('xPed');
                            $xPed->appendChild($dom->createTextNode(''));
                            $prod->appendChild($xPed);
    
                            $nItemPed = $dom->createElement('nItemPed');
                            $nItemPed->appendChild($dom->createTextNode(''));
                            $prod->appendChild($nItemPed);
    
                            $nFCI = $dom->createElement('nFCI');
                            $nFCI->appendChild($dom->createTextNode(''));
                            $prod->appendChild($nFCI);
                        }
                        $det->appendChild($prod);
                    }
                    $infNFe->appendChild($det);
                }
            }
            $NFe->appendChild($infNFe);
        }

        $dom->appendChild($NFe);

        // salva o arquivo
        // $dom->save(__DIR__.'exemplo.xml');

        $ret = $dom->saveXML();

        return $ret;
    }

    private function calcularModulo11($numero) {
        $numero = strrev($numero); // Inverte o número para facilitar o cálculo
    
        $soma = 0;
        $multiplicador = 2;
    
        for ($i = 0; $i < strlen($numero); $i++) {
            $digito = intval($numero[$i]);
            $soma += $digito * $multiplicador;
    
            $multiplicador++;
            if ($multiplicador > 9) {
                $multiplicador = 2;
            }
        }
    
        $resto = $soma % 11;
    
        if ($resto <= 1) {
            return 0;
        } else {
            return 11 - $resto;
        }
    }

    private function api() {
        $url = "";
        $soapUser = "";
        $soapSenha = "";

        $xml = "";
        $headers = "";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $soapUser . ":" . $soapSenha);

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
    }

    private function exemplo() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // TAG PRINCIPAL
        $principal = $dom->createElement('EnviarLoteRpsEnvio');

        // EnviarLoteRpsEnvio->LoteRps
        $LoteRps = $dom->createElement('LoteRps'); {

            // EnviarLoteRpsEnvio->LoteRps->NumeroLote
            $NumeroLote = $dom->createElement('NumeroLote');
            $NumeroLote->appendChild($dom->createTextNode(14608));
            $LoteRps->appendChild($NumeroLote);

            // EnviarLoteRpsEnvio->LoteRps->Cnpj
            $Cnpj = $dom->createElement('Cnpj');
            $Cnpj->appendChild($dom->createTextNode(91933119000920));
            $LoteRps->appendChild($Cnpj);

            // EnviarLoteRpsEnvio->LoteRps->InscricaoMunicipal
            $InscricaoMunicipal = $dom->createElement('InscricaoMunicipal');
            $InscricaoMunicipal->appendChild($dom->createTextNode(29342821));
            $LoteRps->appendChild($InscricaoMunicipal);

            // EnviarLoteRpsEnvio->LoteRps->QuantidadeRps
            $QuantidadeRps = $dom->createElement('QuantidadeRps');
            $QuantidadeRps->appendChild($dom->createTextNode(1));
            $LoteRps->appendChild($QuantidadeRps);


            // EnviarLoteRpsEnvio->LoteRps->ListaRps
            $ListaRps = $dom->createElement('ListaRps'); {

                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps
                $Rps = $dom->createElement('Rps'); {

                    // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps
                    $InfRps = $dom->createElement('InfRps'); {

                        // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->IdentificacaoRps
                        $IdentificacaoRps = $dom->createElement('IdentificacaoRps'); {

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->IdentificacaoRps->Numero
                            $Numero = $dom->createElement('Numero');
                            $Numero->appendChild($dom->createTextNode(14608));
                            $IdentificacaoRps->appendChild($Numero);

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->IdentificacaoRps->Serie
                            $Serie = $dom->createElement('Serie');
                            $Serie->appendChild($dom->createTextNode('ABCDZ'));
                            $IdentificacaoRps->appendChild($Serie);

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->IdentificacaoRps->Tipo
                            $Tipo = $dom->createElement('Tipo');
                            $Tipo->appendChild($dom->createTextNode(1));
                            $IdentificacaoRps->appendChild($Tipo);


                        }

                        // INCLUI IdentificacaoRps DENTRO DA TAG InfRps
                        $InfRps->appendChild($IdentificacaoRps);

                        // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->DataEmissao
                        $DataEmissao = $dom->createElement('DataEmissao');
                        $DataEmissao->appendChild($dom->createTextNode('2023-04-17T16:48:45'));
                        $InfRps->appendChild($DataEmissao);

                        // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->NaturezaOperacao
                        $NaturezaOperacao = $dom->createElement('NaturezaOperacao');
                        $NaturezaOperacao->appendChild($dom->createTextNode(1));
                        $InfRps->appendChild($NaturezaOperacao);

                        
                        // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->OptanteSimplesNacional
                        $OptanteSimplesNacional = $dom->createElement('OptanteSimplesNacional');
                        $OptanteSimplesNacional->appendChild($dom->createTextNode(2));
                        $InfRps->appendChild($OptanteSimplesNacional);

                        // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->IncentivadorCultural
                        $IncentivadorCultural = $dom->createElement('IncentivadorCultural');
                        $IncentivadorCultural->appendChild($dom->createTextNode(2));
                        $InfRps->appendChild($IncentivadorCultural);

                        // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Status
                        $Status = $dom->createElement('Status');
                        $Status->appendChild($dom->createTextNode(1));
                        $InfRps->appendChild($Status);

                        // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico
                        $Servico = $dom->createElement('Servico'); {
                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->Valores
                            $Valores = $dom->createElement('Valores'); {
                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->Valores->ValorServicos
                                $ValorServicos = $dom->createElement('ValorServicos');
                                $ValorServicos->appendChild($dom->createTextNode(2000.00));
                                $Valores->appendChild($ValorServicos);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->Valores->ValorDeducoes
                                $ValorDeducoes = $dom->createElement('ValorDeducoes');
                                $ValorDeducoes->appendChild($dom->createTextNode(0.00));
                                $Valores->appendChild($ValorDeducoes);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->Valores->ValorPis
                                $ValorPis = $dom->createElement('ValorPis');
                                $ValorPis->appendChild($dom->createTextNode(13));
                                $Valores->appendChild($ValorPis);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->Valores->ValorCofins
                                $ValorCofins = $dom->createElement('ValorCofins');
                                $ValorCofins->appendChild($dom->createTextNode(60));
                                $Valores->appendChild($ValorCofins);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->Valores->ValorInss
                                $ValorInss = $dom->createElement('ValorInss');
                                $ValorInss->appendChild($dom->createTextNode(0.00));
                                $Valores->appendChild($ValorInss);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->Valores->ValorIr
                                $ValorIr = $dom->createElement('ValorIr');
                                $ValorIr->appendChild($dom->createTextNode(30));
                                $Valores->appendChild($ValorIr);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->Valores->ValorCsll
                                $ValorCsll = $dom->createElement('ValorCsll');
                                $ValorCsll->appendChild($dom->createTextNode(20));
                                $Valores->appendChild($ValorCsll);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->Valores->IssRetido
                                $IssRetido = $dom->createElement('IssRetido');
                                $IssRetido->appendChild($dom->createTextNode(2));
                                $Valores->appendChild($IssRetido);
                            }

                            // INCLUI Valores DENTRO DA TAG Servico
                            $Servico->appendChild($Valores);

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->ItemListaServico
                            $ItemListaServico = $dom->createElement('ItemListaServico');
                            $ItemListaServico->appendChild($dom->createTextNode(17.01));
                            $Servico->appendChild($ItemListaServico);

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->CodigoTributacaoMunicipio
                            $CodigoTributacaoMunicipio = $dom->createElement('CodigoTributacaoMunicipio');
                            $CodigoTributacaoMunicipio->appendChild($dom->createTextNode(170100100));
                            $Servico->appendChild($CodigoTributacaoMunicipio);

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->Discriminacao
                            $Discriminacao = $dom->createElement('Discriminacao');
                            $Discriminacao->appendChild($dom->createTextNode('TESTE'));
                            $Servico->appendChild($Discriminacao);

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Servico->CodigoMunicipio
                            $CodigoMunicipio = $dom->createElement('CodigoMunicipio');
                            $CodigoMunicipio->appendChild($dom->createTextNode(4314902));
                            $Servico->appendChild($CodigoMunicipio);
                        }

                        // INCLUI Servico DENTRO DA TAG InfRps
                        $InfRps->appendChild($Servico);

                        // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Prestador
                        $Prestador = $dom->createElement('Prestador'); {
                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Prestador->Cnpj
                            $Cnpj = $dom->createElement('Cnpj');
                            $Cnpj->appendChild($dom->createTextNode(91933119000920));
                            $Prestador->appendChild($Cnpj);

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Prestador->InscricaoMunicipal
                            $InscricaoMunicipal = $dom->createElement('InscricaoMunicipal');
                            $InscricaoMunicipal->appendChild($dom->createTextNode(29342821));
                            $Prestador->appendChild($InscricaoMunicipal);
                        }

                        // INCLUI Prestador DENTRO DA TAG InfRps
                        $InfRps->appendChild($Prestador);

                        // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador
                        $Tomador = $dom->createElement('Tomador'); {
                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->IdentificacaoTomador
                            $IdentificacaoTomador = $dom->createElement('IdentificacaoTomador'); {
                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->IdentificacaoTomador->CpfCnpj
                                $CpfCnpj = $dom->createElement('CpfCnpj'); {
                                    // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->IdentificacaoTomador->CpfCnpj->Cnpj
                                    $Cnpj = $dom->createElement('Cnpj');
                                    $Cnpj->appendChild($dom->createTextNode('07718633006977'));
                                    $CpfCnpj->appendChild($Cnpj);
                                }

                                // INCLUI CpfCnpj DENTRO DA TAG IdentificacaoTomador
                                $IdentificacaoTomador->appendChild($CpfCnpj);
                            }

                            // INCLUI IdentificacaoTomador DENTRO DA TAG Tomador
                            $Tomador->appendChild($IdentificacaoTomador);

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->RazaoSocial
                            $RazaoSocial = $dom->createElement('RazaoSocial');
                            $RazaoSocial->appendChild($dom->createTextNode('UNIDASUL DISTRIBUIDORA ALIMENTICIA S/A'));
                            $Tomador->appendChild($RazaoSocial);

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->Endereco
                            $Endereco = $dom->createElement('Endereco'); {
                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->Endereco->Endereco
                                $End = $dom->createElement('Endereco');
                                $End->appendChild($dom->createTextNode('AV INDEPENDENCIA, 9005 PAV B - SALA B1'));
                                $Endereco->appendChild($End);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->Endereco->Numero
                                $Numero = $dom->createElement('Numero');
                                $Numero->appendChild($dom->createTextNode(0));
                                $Endereco->appendChild($Numero);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->Endereco->Bairro
                                $Bairro = $dom->createElement('Bairro');
                                $Bairro->appendChild($dom->createTextNode('NOVO ESTEIO'));
                                $Endereco->appendChild($Bairro);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->Endereco->CodigoMunicipio
                                $CodigoMunicipio = $dom->createElement('CodigoMunicipio');
                                $CodigoMunicipio->appendChild($dom->createTextNode(4307708));
                                $Endereco->appendChild($CodigoMunicipio);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->Endereco->Uf
                                $Uf = $dom->createElement('Uf');
                                $Uf->appendChild($dom->createTextNode('RS'));
                                $Endereco->appendChild($Uf);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->Endereco->Cep
                                $Cep = $dom->createElement('Cep');
                                $Cep->appendChild($dom->createTextNode(93270010));
                                $Endereco->appendChild($Cep);
                            }

                            // INCLUI Endereco DENTRO DA TAG Tomador
                            $Tomador->appendChild($Endereco);

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->Contato
                            $Contato = $dom->createElement('Contato'); {
                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->Contato->Telefone
                                $Telefone = $dom->createElement('Telefone');
                                $Telefone->appendChild($dom->createTextNode(5134589714));
                                $Contato->appendChild($Telefone);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->InfRps->Tomador->Contato->Email
                                $Email = $dom->createElement('Email');
                                $Email->appendChild($dom->createTextNode('nfse@unidasul.com.br'));
                                $Contato->appendChild($Email);
                            }

                            // INCLUI Contato DENTRO DA TAG Tomador
                            $Tomador->appendChild($Contato);
                        }

                        // INCLUI Tomador DENTRO DA TAG InfRps
                        $InfRps->appendChild($Tomador);
                    }

                    // Inclui InfRps dentro da tag Rps
                    $Rps->appendChild($InfRps);

                    // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->Signature
                    $Signature = $dom->createElement('Signature'); {
                        // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->Signature->SignedInfo
                        $SignedInfo = $dom->createElement('SignedInfo'); {
                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->Signature->SignedInfo->CanonicalizationMethod
                            $CanonicalizationMethod = $dom->createElement('CanonicalizationMethod');
                            // Inclui CanonicalizationMethod dentro da tag SignedInfo
                            $SignedInfo->appendChild($CanonicalizationMethod);

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->Signature->SignedInfo->SignatureMethod
                            $SignatureMethod = $dom->createElement('SignatureMethod');
                            // Inclui SignatureMethod dentro da tag SignedInfo
                            $SignedInfo->appendChild($SignatureMethod);

                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->Signature->SignedInfo->Reference
                            $Reference = $dom->createElement('Reference'); {
                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->Signature->SignedInfo->Reference->Transforms
                                $Transforms = $dom->createElement('Transforms'); {
                                    // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->Signature->SignedInfo->Reference->Transforms->Transform
                                    $Transform = $dom->createElement('Transform');
                                    // Inclui Transform dentro da tag Transforms
                                    $Transforms->appendChild($Transform);
                                }

                                // Inclui Transforms dentro da tag Reference
                                $Reference->appendChild($Transforms);

                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->Signature->SignedInfo->Reference->DigestValue
                                $DigestValue = $dom->createElement('DigestValue');
                                $DigestValue->appendChild($dom->createTextNode('IVhKK9iNzjtba7VAXgNrx6BmvKU='));
                                $Reference->appendChild($DigestValue);
                            }
                            // Inclui Reference dentro da tag SignedInfo
                            $SignedInfo->appendChild($Reference);
                        }

                        // Inclui SignedInfo dentro da tag Signature
                        $Signature->appendChild($SignedInfo);

                        // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->Signature->SignatureValue
                        $SignatureValue = $dom->createElement('SignatureValue');
                        $SignatureValue->appendChild($dom->createTextNode('jD9GRT6/QzsWuwd+l73ATxk9tXpxdBt1gZgMYhA0Lfz/HXdfnRInZnLNsP5b8CD+Kj6PPL7vebS1d2Supj7a3fD9fyABOt/lDRlZ63LNJ21D4xyEenf/BabODj8P/rPj4A1yXiTTuMyY2SiQ7aj2VscWvbW+n1vYar3pD8QuU+TgeiYcn1u4Juu6HVnCJBW26Y/33BQ3ZQj0iaG78RaIXb0w4O76cpIeoxZQZFHJU9yg03dra/mDprWX7+xvAi/Omv7E/Im3it92sepfbEshHyYVJddAJ5omISmZa8dLL5Pu4pSn9SuXRdgz1kucqmHTN9ga2up3/Fvnudia6yum7A=='));
                        $Signature->appendChild($SignatureValue);

                        // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->Signature->KeyInfo
                        $KeyInfo = $dom->createElement('KeyInfo'); {
                            // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->Signature->KeyInfo->X509Data
                            $X509Data = $dom->createElement('X509Data'); {
                                // EnviarLoteRpsEnvio->LoteRps->ListaRps->Rps->Signature->KeyInfo->X509Data->X509Certificate
                                $X509Certificate = $dom->createElement('X509Certificate');
                                $X509Certificate->appendChild($dom->createTextNode('Bag AttributeslocalKeyID: 01 00 00 00subject=C = BR, O = ICP-Brasil, ST = SP, L = SAO PAULO, OU = Secretaria da Receita Federal do Brasil - RFB, OU = RFB e-CNPJ A1, OU = 10187921000169, OU = videoconferencia, CN = MARPA CONSULTORIA E ASSESSORIA EMPRESARIAL LTDA:91933119000172issuer=C = BR, O = ICP-Brasil, OU = Secretaria da Receita Federal do Brasil - RFB, CN = AC SAFEWEB RFB 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'));
                                $X509Data->appendChild($X509Certificate);
                            }

                            // Inclui X509Data dentro da tag KeyInfo
                            $KeyInfo->appendChild($X509Data);
                        }

                        // Inclui KeyInfo dentro da tag Signature
                        $Signature->appendChild($KeyInfo);
                    }

                    // Inclui Signature dentro da tag Rps
                    $Rps->appendChild($Signature);
                }

                // Inclui Rps dentro da tag ListaRps
                $ListaRps->appendChild($Rps);

            }

            // Inclui ListaRps dentro da tag LoteRps
            $LoteRps->appendChild($ListaRps);
        }

        // Inclui LoteRps dentro da tag principal
        $principal->appendChild($LoteRps);

        // EnviarLoteRpsEnvio->Signature
        $Signature = $dom->createElement('Signature'); {
            // EnviarLoteRpsEnvio->Signature->SignedInfo
            $SignedInfo = $dom->createElement('SignedInfo'); {
                // EnviarLoteRpsEnvio->Signature->SignedInfo->CanonicalizationMethod
                $CanonicalizationMethod = $dom->createElement('CanonicalizationMethod');
                // Inclui CanonicalizationMethod dentro da tag SignedInfo
                $SignedInfo->appendChild($CanonicalizationMethod);

                // EnviarLoteRpsEnvio->Signature->SignedInfo->SignatureMethod
                $SignatureMethod = $dom->createElement('SignatureMethod');
                // Inclui SignatureMethod dentro da tag SignedInfo
                $SignedInfo->appendChild($SignatureMethod);

                // EnviarLoteRpsEnvio->Signature->SignedInfo->Reference
                $Reference = $dom->createElement('Reference'); {
                    // EnviarLoteRpsEnvio->Signature->SignedInfo->Reference->Transforms
                    $Transforms = $dom->createElement('Transforms'); {
                        // EnviarLoteRpsEnvio->Signature->SignedInfo->Reference->Transforms->Transform
                        $Transform = $dom->createElement('Transform');
                        // Inclui Transform dentro da tag Transforms
                        $Transforms->appendChild($Transform);
                    }
                    // Inclui Transforms dentro da tag Reference
                    $Reference->appendChild($Transforms);

                    // EnviarLoteRpsEnvio->Signature->SignedInfo->Reference->DigestMethod
                    $DigestMethod = $dom->createElement('DigestMethod');
                    // Inclui DigestMethod dentro da tag Reference
                    $Reference->appendChild($DigestMethod);

                    // EnviarLoteRpsEnvio->Signature->SignedInfo->Reference->DigestValue
                    $DigestValue = $dom->createElement('DigestValue');
                    $DigestValue->appendChild($dom->createTextNode('wCmx9vvgL0JdQwkWjdPIgVHF/Ko='));
                    $Reference->appendChild($DigestValue);
                }
                // Inclui Reference dentro da tag SignedInfo
                $SignedInfo->appendChild($Reference);
            }

            // Inclui SignedInfo dentro da tag Signature
            $Signature->appendChild($SignedInfo);

            // EnviarLoteRpsEnvio->Signature->SignatureValue
            $SignatureValue = $dom->createElement('SignatureValue');
            $SignatureValue->appendChild($dom->createTextNode('xa77Jr+T6Ak/uMPglzkDd/bTDzRnOn5RbFvIKTAEkb8asHYqSPLQ1t+JHPq8mTPYqOKl5giGg5e779R82FuXMu2hiV+fAbU09BNZpFzHl1P2VVf4/tVV5vnx82bdcA44thrfqVDuwHs3JlHiOMo+44wSnVTxcSiGV4ojjRbaascvLqcHLrQzuWf//37FMok8UebExKPhADBtsAM1gj+eyzrJyqYl6jD1Z6u9kkn6LxFswICiYHc6JbRAmotN9SFtuF6pWGwcZhGY3ZnsDmJ9/LpDdwjibcRkKyNb1EigH/7OLgexHqZidWkdi1s6Vnrm8rU/Z+swTK7aUU7efjQs8A=='));
            $Signature->appendChild($SignatureValue);

            // EnviarLoteRpsEnvio->Signature->KeyInfo
            $KeyInfo = $dom->createElement('KeyInfo'); {
                // EnviarLoteRpsEnvio->Signature->KeyInfo->X509Data
                $X509Data = $dom->createElement('X509Data'); {
                    // EnviarLoteRpsEnvio->Signature->KeyInfo->X509Data->X509Certificate
                    $X509Certificate = $dom->createElement('X509Certificate');
                    $X509Certificate->appendChild($dom->createTextNode('Bag AttributeslocalKeyID: 01 00 00 00subject=C = BR, O = ICP-Brasil, ST = SP, L = SAO PAULO, OU = Secretaria da Receita Federal do Brasil - RFB, OU = RFB e-CNPJ A1, OU = 10187921000169, OU = videoconferencia, CN = MARPA CONSULTORIA E ASSESSORIA EMPRESARIAL LTDA:91933119000172issuer=C = BR, O = ICP-Brasil, OU = Secretaria da Receita Federal do Brasil - RFB, CN = AC SAFEWEB RFB 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'));
                    $X509Data->appendChild($X509Certificate);
                }
                // Inclui X509Data dentro da tag KeyInfo
                $KeyInfo->appendChild($X509Data);
            }
            // Inclui KeyInfo dentro da tag Signature
            $Signature->appendChild($KeyInfo);
        }

        // Inclui Signature dentro da tag principal
        $principal->appendChild($Signature);

        // USUÁRIO
        // $userNodeValue = $dom->createTextNode('Rafa');
        // $userNode = $dom->createElement('user');
        // $userNode->appendChild($userNodeValue);
        // $principal->appendChild($userNode);

        $dom->appendChild($principal);

        echo "<p>XML: " . $dom->saveXML() . "</p>";
    }
}