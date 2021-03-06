<?php

require_once APPPATH . 'controllers/base/BaseController.php';

class centrocirurgico extends BaseController {

    function __construct() {
        parent::__construct();
        $this->load->model('emergencia/solicita_acolhimento_model', 'acolhimento');
        $this->load->model('cadastro/paciente_model', 'paciente');
        $this->load->model('internacao/internacao_model', 'internacao_m');
        $this->load->model('internacao/unidade_model', 'unidade_m');
        $this->load->model('internacao/motivosaida_model', 'motivosaida');
        $this->load->model('internacao/enfermaria_model', 'enfermaria_m');
        $this->load->model('internacao/leito_model', 'leito_m');
        $this->load->model('seguranca/operador_model', 'operador_m');
        $this->load->model('ambulatorio/guia_model', 'guia');
        $this->load->model('ambulatorio/procedimentoplano_model', 'procedimentoplano');
        $this->load->model('internacao/solicitainternacao_model', 'solicitacaointernacao_m');
        $this->load->model('centrocirurgico/centrocirurgico_model', 'centrocirurgico_m');
        $this->load->model('centrocirurgico/solicita_cirurgia_model', 'solicitacirurgia_m');
        $this->load->library('utilitario');
    }

    public function index() {
        $this->pesquisar();
    }

    public function pesquisar($args = array()) {
        $this->loadView('centrocirurgico/listarsolicitacao');
    }

    public function pesquisarsaida($args = array()) {
        $this->loadView('internacao/listarsaida');
    }

    public function pesquisarunidade($args = array()) {
        $this->loadView('internacao/listarunidade');
    }

    public function pesquisarcirurgia($args = array()) {
        $this->loadView('centrocirurgico/listarcirurgia');
    }

    public function pesquisarsolicitacaointernacao($args = array()) {
        $this->loadView('internacao/listarsolicitacaointernacao');
    }

    public function pesquisarhospitais($args = array()) {
        $this->loadView('centrocirurgico/hospital-lista');
    }

    public function pesquisarequipecirurgica($args = array()) {
        $this->loadView('centrocirurgico/equipecirurgica-lista');
    }

    public function pesquisargrauparticipacao($args = array()) {
        $this->loadView('centrocirurgico/grauparticipacao-lista');
    }

    function solicitacirurgia($internacao_id) {
        $data['paciente'] = $this->solicitacirurgia_m->solicitacirurgia($internacao_id);
        $this->loadView('centrocirurgico/solicitacirurgia', $data);
    }

    function gravarsolicitacaocirurgia() {

        if ($this->solicitacirurgia_m->gravarsolicitacaocirurgia()) {
            $data['mensagem'] = 'Erro ao efetuar solicitação de cirurgia';
        } else {
            $data['mensagem'] = 'Solicitação de Cirurgia efetuada com Sucesso';
        }

        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisar");
    }

    function autorizarcirurgia() {
        $this->centrocirurgico_m->autorizarcirurgia();
        $data['mensagem'] = 'Autorizacao efetuada com Sucesso';
        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisar");
    }

    function importarquivos($solicitacao_cirurgia_id) {
        $this->load->helper('directory');

        if (!is_dir("./upload/centrocirurgico")) {
            mkdir("./upload/centrocirurgico");
            $destino = "./upload/centrocirurgico";
            chmod($destino, 0777);
        }
        if (!is_dir("./upload/centrocirurgico/$solicitacao_cirurgia_id")) {
            mkdir("./upload/centrocirurgico/$solicitacao_cirurgia_id");
            $destino = "./upload/centrocirurgico/$solicitacao_cirurgia_id";
            chmod($destino, 0777);
        }

        $data['arquivo_pasta'] = directory_map("./upload/centrocirurgico/$solicitacao_cirurgia_id/");
        //        $data['arquivo_pasta'] = directory_map("/home/vivi/projetos/clinica/upload/consulta/$paciente_id/");
        if ($data['arquivo_pasta'] != false) {
            sort($data['arquivo_pasta']);
        }
        $data['solicitacao_cirurgia_id'] = $solicitacao_cirurgia_id;
        $this->loadView('centrocirurgico/importacao-imagemcentrocirurgico', $data);
    }

    function importarimagemcentrocirurgico() {
        $solicitacao_cirurgia_id = $_POST['paciente_id'];

        for ($i = 0; $i < count($_FILES['arquivos']['name']); $i++) {
            $_FILES['userfile']['name'] = $_FILES['arquivos']['name'][$i];
            $_FILES['userfile']['type'] = $_FILES['arquivos']['type'][$i];
            $_FILES['userfile']['tmp_name'] = $_FILES['arquivos']['tmp_name'][$i];
            $_FILES['userfile']['error'] = $_FILES['arquivos']['error'][$i];
            $_FILES['userfile']['size'] = $_FILES['arquivos']['size'][$i];

            if (!is_dir("./upload/centrocirurgico/$solicitacao_cirurgia_id")) {
                mkdir("./upload/centrocirurgico/$solicitacao_cirurgia_id");
                $destino = "./upload/centrocirurgico/$solicitacao_cirurgia_id";
                chmod($destino, 0777);
            }

            //        $config['upload_path'] = "/home/vivi/projetos/clinica/upload/consulta/" . $paciente_id . "/";
            $config['upload_path'] = "./upload/centrocirurgico/" . $solicitacao_cirurgia_id . "/";
            $config['allowed_types'] = 'gif|jpg|BMP|png|jpeg|pdf|doc|docx|xls|xlsx|ppt|zip|rar|xml|txt';
            $config['max_size'] = '0';
            $config['overwrite'] = FALSE;
            $config['encrypt_name'] = FALSE;
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload()) {
                $error = array('error' => $this->upload->display_errors());
            } else {
                $error = null;
                $data = array('upload_data' => $this->upload->data());
            }
        }
//        var_dump($error); die;


        $data['solicitacao_cirurgia_id'] = $solicitacao_cirurgia_id;
        redirect(base_url() . "centrocirurgico/centrocirurgico/importarquivos/$solicitacao_cirurgia_id");
    }

    function excluirarquivocentrocirurgico($solicitacao_cirurgia_id, $nome) {

        if (!is_dir("./uploadopm/centrocirurgico")) {
            if (!is_dir("./uploadopm/centrocirurgico")) {
                mkdir("./uploadopm/centrocirurgico");
            }
            mkdir("./uploadopm/centrocirurgico");
            $destino = "./uploadopm/centrocirurgico/";
            chmod($destino, 0777);
        }
        
        if (!is_dir("./uploadopm/centrocirurgico/$solicitacao_cirurgia_id")) {
            if (!is_dir("./uploadopm/centrocirurgico")) {
                mkdir("./uploadopm/centrocirurgico");
            }
            mkdir("./uploadopm/centrocirurgico/$solicitacao_cirurgia_id");
            $destino = "./uploadopm/centrocirurgico/$solicitacao_cirurgia_id";
            chmod($destino, 0777);
        }

        $origem = "./upload/centrocirurgico/$solicitacao_cirurgia_id/$nome";
        $destino = "./uploadopm/centrocirurgico/$solicitacao_cirurgia_id/$nome";
        copy($origem, $destino);
        unlink($origem);
        redirect(base_url() . "centrocirurgico/centrocirurgico/importarquivos/$solicitacao_cirurgia_id");
    }

    function excluirequipecirurgica($equipe_cirurgia_id) {
        $this->centrocirurgico_m->excluirequipecirurgica($equipe_cirurgia_id);
        $data['mensagem'] = 'Equipe excluida com Sucesso';
        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisarequipecirurgica");
    }

    function excluirsolicitacaocirurgia($solicitacao_id) {
        $this->solicitacirurgia_m->excluirsolicitacaocirurgia($solicitacao_id);
        $data['mensagem'] = 'Solicitacao excluida com sucesso';
        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisar");
    }

    function excluirsolicitacaoprocedimento($solicitacao_procedimento_id, $solicitacao) {
        $this->solicitacirurgia_m->excluirsolicitacaoprocedimento($solicitacao_procedimento_id);
        $data['mensagem'] = 'Procedimento removido com sucesso';
        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/carregarsolicitacao/$solicitacao");
    }

    function novo($paciente_id) {
        $data['paciente'] = $this->paciente->listardados($paciente_id);

        $horario = date(" Y-m-d H:i:s");

        $hour = substr($horario, 11, 3);
        $minutes = substr($horario, 15, 2);
        $seconds = substr($horario, 18, 4);

        $this->loadView('emergencia/solicitacoes-paciente', $data);
    }

    function novograuparticipacao() {
        $this->loadView('centrocirurgico/grauparticipacao-form');
    }

    function editarpercentualoutros($percentual_id) {
        $data['percentual_id'] = $percentual_id;
        $data['percentual'] = $this->centrocirurgico_m->carregarpercentualoutros($percentual_id);
        $this->loadView('centrocirurgico/configurarpercentuaisoutros-form', $data);
    }

    function editarpercentualfuncao($percentual_id) {
        $data['percentual_id'] = $percentual_id;
        $data['percentual'] = $this->centrocirurgico_m->carregarpercentualfuncao($percentual_id);
        $this->loadView('centrocirurgico/configurarpercentuais-form', $data);
    }

    function editarhorarioespecial($percentual_id) {
        $data['percentual_id'] = $percentual_id;
        $data['percentual'] = $this->centrocirurgico_m->carregarpercentualfuncao($percentual_id);
        $this->loadView('centrocirurgico/configurarhorarioespecial-form', $data);
    }

    function configurarpercentuais() {
        $data['funcao'] = $this->centrocirurgico_m->listarpercentualfuncao();
        $data['percentual'] = $this->centrocirurgico_m->listarpercentualoutros();
        $this->loadView('centrocirurgico/configurarpercentuais-lista', $data);
    }

    function atribuirpadraopercentualans() {
        $this->centrocirurgico_m->atribuirpadraopercentualans();
        $data['mensagem'] = 'Percentual alterado com sucesso.';
        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/configurarpercentuais");
    }

    function carregar($emergencia_solicitacao_acolhimento_id) {
        $obj_paciente = new paciente_model($emergencia_solicitacao_acolhimento_id);
        $data['obj'] = $obj_paciente;
        $this->loadView('emergencia/solicita-acolhimento-ficha', $data);
    }

    function carregarunidade($internacao_unidade_id) {
        $obj_paciente = new unidade_model($internacao_unidade_id);
        $data['obj'] = $obj_paciente;
        $this->loadView('internacao/cadastrarunidade', $data);
    }

    function carregarmotivosaida($internacao_motivosaida_id) {
        $obj_paciente = new motivosaida_model($internacao_motivosaida_id);
        $data['obj'] = $obj_paciente;
        $this->loadView('internacao/cadastrarmotivosaida', $data);
    }

    function carregarenfermaria($internacao_enfermaria_id) {
        $obj_paciente = new enfermaria_model($internacao_enfermaria_id);
        $data['obj'] = $obj_paciente;
        $this->loadView('internacao/cadastrarenfermaria', $data);
    }

    function carregarleito($internacao_leito_id) {
        $obj_paciente = new leito_model($internacao_leito_id);
        $data['obj'] = $obj_paciente;
        $this->loadView('internacao/cadastrarleito', $data);
    }

    function mostraautorizarcirurgia($solicitacao_id) {
        $data['solicitacao_id'] = $solicitacao_id;
        $data['solicitacao'] = $this->solicitacirurgia_m->listardadossolicitacaoautorizar($solicitacao_id);
        $data['forma_pagamento'] = $this->guia->formadepagamento();
        $data['procedimentos'] = $this->solicitacirurgia_m->listarprocedimentosolicitacaocirurgica($solicitacao_id);
        $this->loadView('centrocirurgico/autorizarcirurgia', $data);
    }

    function editarcirurgia($solicitacao_id) {
        $data['solicitacao_id'] = $solicitacao_id;
        $data['solicitacao'] = $this->solicitacirurgia_m->listardadossolicitacaoautorizar($solicitacao_id);
        $data['forma_pagamento'] = $this->guia->formadepagamento();
        $data['procedimentos'] = $this->solicitacirurgia_m->listarprocedimentosolicitacaocirurgica($solicitacao_id);
        $this->loadView('centrocirurgico/editarcirurgia', $data);
    }

    function impressaoorcamento($solicitacao_id) {
        $data['solicitacao_id'] = $solicitacao_id;
        $data['empresa'] = $this->solicitacirurgia_m->burcarempresa();
        $data['procedimentos'] = $this->solicitacirurgia_m->impressaoorcamento($solicitacao_id);
        $data['solicitacao'] = $this->solicitacirurgia_m->listardadossolicitacaoorcamentoimpressao($solicitacao_id);
//        echo "<pre>"; var_dump($data['solicitacao']); die;
        $this->load->view('centrocirurgico/impressaoorcamento', $data);
    }

    function adicionarprocedimentos($solicitacao) {
        $data['solicitacao'] = $solicitacao;
        redirect(base_url() . "centrocirurgico/centrocirurgico/carregarsolicitacao/$solicitacao");
    }

    function gravarpercentualhorarioespecial() {
        $this->centrocirurgico_m->gravarpercentualhorarioespecial();

        $data['mensagem'] = 'Percentual gravado com sucesso.';
        $this->session->set_flashdata('message', $data['mensagem']);

        redirect(base_url() . "centrocirurgico/centrocirurgico/configurarpercentuais");
    }

    function gravarpercentualoutros() {
        $this->centrocirurgico_m->gravarpercentualoutros();

        $data['mensagem'] = 'Percentual gravado com sucesso.';
        $this->session->set_flashdata('message', $data['mensagem']);

        redirect(base_url() . "centrocirurgico/centrocirurgico/configurarpercentuais");
    }

    function gravarpercentualfuncao() {
        $this->centrocirurgico_m->gravarpercentualfuncao();

        $data['mensagem'] = 'Percentual gravado com sucesso.';
        $this->session->set_flashdata('message', $data['mensagem']);

        redirect(base_url() . "centrocirurgico/centrocirurgico/configurarpercentuais");
    }

    function finalizarequipecirurgica($solicitacaocirurgia_id) {
        $this->centrocirurgico_m->finalizarequipecirurgica($solicitacaocirurgia_id);
        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisar");
    }

    function gravarguiacirurgicaequipe() {
        $guia_id = $_POST['txtambulatorioguiaid'];

        $data['guia'] = $this->guia->instanciarguia($guia_id);
        $data['procedimentos'] = $this->centrocirurgico_m->listarprocedimentosguiacirurgica($guia_id);
        $funcao = $this->centrocirurgico_m->listarfuncaoexameequipe($guia_id);
//        echo '<pre>';
//        var_dump($funcao);
//        die;

        if (count($funcao) == 0) {

            $data['mensagem'] = 'Função gravada com sucesso.';
            $this->centrocirurgico_m->gravarguiacirurgicaequipe($data['procedimentos'], $data['guia'][0]);
        } else {
            $data['mensagem'] = 'Função já existente.';
        }

        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/cadastrarequipeguiacirurgica/$guia_id");
    }

    function gravarguiacirurgicaequipeeditar($guia_id, $solicitacao_id) {
//        var_dump($guia_id); die;
        $data['guia'] = $this->guia->instanciarguia($guia_id);
        $data['procedimentos'] = $this->centrocirurgico_m->listarprocedimentosguiacirurgica($guia_id);
        $funcao = $this->centrocirurgico_m->listarfuncaoexameequipe($guia_id);
//        echo '<pre>';
//        var_dump($funcao);
//        die;

        if (count($funcao) == 0) {
            $data['mensagem'] = 'Função gravada com sucesso.';
            $this->centrocirurgico_m->gravarguiacirurgicaequipeeditar($data['procedimentos'], $data['guia'][0], $solicitacao_id);
        } else {
            $data['mensagem'] = 'Função já existente.';
        }

        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/cadastrarequipeguiacirurgicasolicitacao/$solicitacao_id/$guia_id");
    }

    function procedimentocirurgicovalor($agenda_exames_id) {
        $data['valor'] = $this->centrocirurgico_m->procedimentocirurgicovalor($agenda_exames_id);
//        var_dump($data['valor']); die;

        $this->load->View('ambulatorio/procedimentocirurgicovalor-form', $data);
    }

    function gravarprocedimentocirurgicovalor($agenda_exames_id) {
        $this->centrocirurgico_m->gravarprocedimentocirurgicovalor($agenda_exames_id);
//        var_dump($data['valor']); die;

        redirect(base_url() . "seguranca/operador/pesquisarrecepcao");
    }

    function cadastrarequipeguiacirurgica($guia) {

        $data['guia_id'] = $guia;
        $data['guia'] = $this->guia->instanciarguia($guia);

        $data['medicos'] = $this->operador_m->listarmedicos();
        $data['grau_participacao'] = $this->solicitacirurgia_m->grauparticipacao();
        $data['equipe_operadores'] = $this->centrocirurgico_m->listarequipeoperadores($guia);
        $this->loadView('centrocirurgico/equipeguiacirurgica-form', $data);
    }

    function cadastrarequipeguiacirurgicasolicitacao($solicitacao_cirurgia_id, $guia) {

        $data['guia_id'] = $guia;
        $data['solicitacao_id'] = $solicitacao_cirurgia_id;
        $data['guia'] = $this->guia->instanciarguia($guia);

        $data['medicos'] = $this->operador_m->listarmedicos();
        $data['grau_participacao'] = $this->solicitacirurgia_m->grauparticipacao();
        $data['equipe_operadores'] = $this->centrocirurgico_m->listarequipeoperadoreseditar($guia);
        $this->loadView('centrocirurgico/equipeguiacirurgicaeditar-form', $data);
    }

    function cadastrarequipe() {
        $this->loadView("centrocirurgico/equipecirurgica-form");
    }

    function excluirguiacirurgica($guia) {
        $this->centrocirurgico_m->excluirguiacirurgica($guia);

        $data['mensagem'] = 'Guia Cirurgica cancelada com sucesso';
        $this->session->set_flashdata('message', $data['mensagem']);

        redirect(base_url() . "ambulatorio/exame/faturamentomanual");
    }

    function finalizarcadastroequipecirurgica($guia) {
        $verifica = $this->centrocirurgico_m->finalizarcadastroequipecirurgica($guia);
        if ($verifica) {
            $data['mensagem'] = 'Equipe gravada com sucesso';
        } else {
            $data['mensagem'] = 'Erro ao finalizar equipe';
        }
        $this->session->set_flashdata('message', $data['mensagem']);

        redirect(base_url() . "ambulatorio/exame/faturamentomanual");
    }

//    function adicionarprocedimentosdecisao() {
////        if ($_POST['escolha'] == "SIM") {
//            $solicitacao = $_POST['solicitacao_id'];
//            redirect(base_url() . "centrocirurgico/centrocirurgico/carregarsolicitacao/$solicitacao");
////        } else {
////            redirect(base_url() . "centrocirurgico/centrocirurgico/centrocirurgico");
////        }
//    }

    function gravargrauparticipacao() {
        $solicitacao = $this->centrocirurgico_m->gravargrauparticipacao();
        if ($solicitacao == -1) {
            $data['mensagem'] = 'Erro ao Gravar. Esse Código ja foi cadastrado.';
        } else {
            $data['mensagem'] = 'Grau de partipação salvo com Sucesso';
        }
        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisargrauparticipacao");
    }

    function gravarnovasolicitacao() {
        if ($_POST["txtNomeid"] == "") {
            $data['mensagem'] = 'Paciente escolhido não é válido';
            $this->session->set_flashdata('message', $data['mensagem']);
            redirect(base_url() . "centrocirurgico/centrocirurgico/novasolicitacao/0");
        } else {
            $solicitacao = $this->solicitacirurgia_m->gravarnovasolicitacao();
            if ($solicitacao == -1) {
                $data['mensagem'] = 'Erro ao efetuar Solicitacao';
            } else {
                $data['mensagem'] = 'Solicitacao efetuada com Sucesso';
//            var_dump($solicitacao);
            }
            $this->session->set_flashdata('message', $data['mensagem']);
            redirect(base_url() . "centrocirurgico/centrocirurgico/adicionarprocedimentos/$solicitacao");
        }
    }

    function gravarsolicitacaoprocedimentos() {
        $solicitacao = $_POST['solicitacao_id'];

        if ($_POST['tipo'] == 'procedimento') {
            if ($_POST['procedimentoID'] != '') {
                $verifica = count($this->solicitacirurgia_m->verificasolicitacaoprocedimentorepetidos());
                if ($verifica == 0) {
                    if ($this->solicitacirurgia_m->gravarsolicitacaoprocedimento()) {
                        $data['mensagem'] = 'Procedimento adicionado com Sucesso';
                    } else {
                        $data['mensagem'] = 'Erro ao gravar Procedimento';
                    }
                    $this->session->set_flashdata('message', $data['mensagem']);
                    redirect(base_url() . "centrocirurgico/centrocirurgico/carregarsolicitacao/$solicitacao");
                }
            } else {
                $data['mensagem'] = 'Erro ao gravar Procedimento. Procedimento nao selecionado ou invalido.';
            }
        } elseif ($_POST['tipo'] == 'agrupador') {
            $procedimentos = $this->solicitacirurgia_m->listarprocedimentosagrupador($_POST['agrupador_id']);
            foreach ($procedimentos as $item) {
                $_POST['procedimentoID'] = $item->procedimento_id;
                $verifica = count($this->solicitacirurgia_m->verificasolicitacaoprocedimentorepetidos());
                if ($verifica == 0) {
                    $this->solicitacirurgia_m->gravarsolicitacaoprocedimento();
                }
            }
        }

        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/carregarsolicitacao/$solicitacao");
    }

    function carregarsolicitacao($solicitacao_id) {

        $data['solicitacao_id'] = $solicitacao_id;
        $data['dados'] = $this->centrocirurgico_m->listarsolicitacoes3($solicitacao_id);
        $data['procedimento'] = $this->solicitacirurgia_m->carregarsolicitacaoprocedimento($data['dados'][0]->convenio_id);
        $data['agrupador'] = $this->solicitacirurgia_m->carregarsolicitacaoagrupador($data['dados'][0]->convenio_id);
        $data['procedimentos'] = $this->solicitacirurgia_m->listarsolicitacaosprocedimentos($solicitacao_id);
        $this->loadView('centrocirurgico/solicitacaoprocedimentos-form', $data);
    }

    function carregarhospital($hospital_id) {

        $data['hospital_id'] = $hospital_id;
        $data['hospital'] = $this->centrocirurgico_m->instanciarhospitais($hospital_id);
//        echo "<pre>";var_dump($data['hospital'] );die;
        $this->loadView('centrocirurgico/hospital-form', $data);
    }

    function gravarequipeoperadores() {
        $solicitacao_id = $_POST['solicitacao_id'];

        $equipe_funcao = $this->centrocirurgico_m->listarequipeoperadoresfuncao();
        if (count($equipe_funcao) == 0) {
            $this->centrocirurgico_m->gravarequipeoperadores();
            $data['mensagem'] = 'Sucesso ao gravar função.';
        } else {
            $data['mensagem'] = 'Função já existente';
        }
        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/montarequipe/$solicitacao_id");
    }

    function finalizarcadastroprocedimentosguia($guia) {
        redirect(base_url() . "centrocirurgico/centrocirurgico/cadastrarequipeguiacirurgica/$guia");
    }

    function gravarhospital() {
        $hospital_id = $this->centrocirurgico_m->gravarhospital();
        if ($empresa_id == "-1") {
            $data['mensagem'] = 'Erro ao gravar Hospital. Opera&ccedil;&atilde;o cancelada.';
        } else {
            $data['mensagem'] = 'Sucesso ao gravar Hospital.';
        }
        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisarhospitais");
    }

    function excluirgrauparticipacao($grau_participacao_id) {
        $this->centrocirurgico_m->excluirgrauparticipacao($grau_participacao_id);
        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisargrauparticipacao");
    }

    function excluirhospital($hospital_id) {
        $this->centrocirurgico_m->excluirhospital($hospital_id);
        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisarhospitais");
    }

    function excluiritemorcamento($orcamento_id, $solicitacao_id, $convenio_id) {
        $this->solicitacirurgia_m->excluiritemorcamento($orcamento_id);
        redirect(base_url() . "centrocirurgico/centrocirurgico/solicitacarorcamento/$solicitacao_id/$convenio_id");
    }

    function excluiritemequipe($cirurgia_operadores_id, $equipe_id) {
        $this->solicitacirurgia_m->excluiritemequipe($cirurgia_operadores_id);
        redirect(base_url() . "centrocirurgico/centrocirurgico/montarequipe/$equipe_id");
    }

    function excluiroperadorequipecirurgica($guia_id, $funcao_id, $solicitacao_id) {
        $this->solicitacirurgia_m->excluiroperadorequipecirurgica($guia_id, $funcao_id, $solicitacao_id);
        redirect(base_url() . "centrocirurgico/centrocirurgico/cadastrarequipeguiacirurgica/$guia_id");
    }

    function excluiroperadorequipecirurgicaeditar($guia_id, $funcao_id, $solicitacao_id) {
        $this->solicitacirurgia_m->excluiroperadorequipecirurgicaeditar($guia_id, $funcao_id, $solicitacao_id);
        redirect(base_url() . "centrocirurgico/centrocirurgico/cadastrarequipeguiacirurgicasolicitacao/$solicitacao_id/$guia_id");
    }

    function liberar($solicitacao_id, $orcamento) {
        if ($this->centrocirurgico_m->liberarsolicitacao($solicitacao_id, $orcamento)) {
            $data['mensagem'] = "LIBERADO!";
        } else {
            $data['mensagem'] = "Falha ao realizar Liberação!";
        }
        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisar");
    }

    function orcamentopergunta($solicitacao_id, $convenio_id) {
        $data['solicitacao_id'] = $solicitacao_id;
        $data['convenio_id'] = $convenio_id;
        $teste = $this->centrocirurgico_m->verificasituacao($solicitacao_id);
        redirect(base_url() . "centrocirurgico/centrocirurgico/solicitacarorcamento/$solicitacao_id/$convenio_id");
    }

    function orcamentoescolha($solicitacao_id, $convenio_id) {
        if ($_POST['escolha'] == 'SIM') {
            $this->centrocirurgico_m->alterarsituacaoorcamento($solicitacao_id);
            redirect(base_url() . "centrocirurgico/centrocirurgico/solicitacarorcamento/$solicitacao_id/$convenio_id");
        } else {
            $this->centrocirurgico_m->alterarsituacaoorcamentodisnecessario($solicitacao_id);
            redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisar");
        }
    }

//    function novasolicitacaoconsulta($exame_id) {
//        $data['paciente'] = $this->solicitacirurgia_m->solicitacirurgiaconsulta($exame_id);
//        $data['medicos'] = $this->operador_m->listarmedicos();
//        $this->loadView('centrocirurgico/novasolicitacao', $data);
//    }

    function novasolicitacao($solicitacao_id, $laudo_id = null) {
        $data['solicitacao_id'] = $solicitacao_id;
        $data['hospitais'] = $this->centrocirurgico_m->listarhospitaissolicitacao();
        $data['medicos'] = $this->operador_m->listarmedicos();
        $data['convenio'] = $this->centrocirurgico_m->listarconveniocirurgiaorcamento();
        if ($laudo_id != null && $laudo_id != '0') {
            $data['laudo'] = $this->centrocirurgico_m->listarlaudosolicitacaocirurgica($laudo_id);
        }
        $this->loadView('centrocirurgico/novasolicitacao', $data);
    }

    function finalizarorcamento($solicitacao_id) {
        if ($this->centrocirurgico_m->finalizarrcamento($solicitacao_id)) {
            $data['mensagem'] = "Orçamento Finalizado";
        } else {
            $data['mensagem'] = "ERRO: Orçamento NÃO Finalizado";
        }
        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisar");
    }

    function solicitacarorcamento($solicitacao_id) {
        $data['solicitacao_id'] = $solicitacao_id;
        $data['solicitacao'] = $this->solicitacirurgia_m->listardadossolicitacaoorcamento($solicitacao_id);
        $data['procedimentos'] = $this->solicitacirurgia_m->listarprocedimentosolicitacaocirurgica($solicitacao_id);
        $this->loadView('centrocirurgico/solicitacarorcamento-form', $data);
    }

    function montarequipe($solicitacaocirurgia_id) {
        $data['solicitacaocirurgia_id'] = $solicitacaocirurgia_id;
        $data['medicos'] = $this->operador_m->listarmedicos();
//        $data['equipe'] = $this->solicitacirurgia_m->listarequipe($solicitacaocirurgia_id);
        $data['equipe_operadores'] = $this->solicitacirurgia_m->listarequipeoperadores($solicitacaocirurgia_id);
        $data['grau_participacao'] = $this->solicitacirurgia_m->grauparticipacao();
//        echo "<pre>";var_dump($data['equipe_operadores'] );die;
        $this->loadView('centrocirurgico/montarequipe-form', $data);
    }

    function gravarequipe() {
        $equipe_id = $this->solicitacirurgia_m->gravarequipe();
        redirect(base_url() . "centrocirurgico/centrocirurgico/montarequipe/$equipe_id");
    }

    function finalizarrequipe($solicitacao_id) {
        $this->centrocirurgico_m->finalizarequipe($solicitacao_id);
        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisar");
    }

    function autorizarsolicitacaocirurgica() {
        $this->solicitacirurgia_m->autorizarsolicitacaocirurgica();

        $guia_id = $this->solicitacirurgia_m->gravarguiasolicitacaocirurgica();

        if ($this->solicitacirurgia_m->gravarprocedimentosolicitacaocirurgica($guia_id)) {
            $data['mensagem'] = "Solicitação autorizada gravado com sucesso!";
        } else {
            $data['mensagem'] = "Erro ao gravar Orçamento. Opera&ccedil;&atilde;o cancelada.";
        }

        $this->session->set_flashdata('message', $data['mensagem']);

        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisar");
    }

    function gravareditarcirurgia() {

        if ($this->solicitacirurgia_m->gravareditarcirurgia()) {
            $data['mensagem'] = "Sucesso ao editar cirurgia!";
        } else {
            $data['mensagem'] = "Erro ao editar cirurgia. Opera&ccedil;&atilde;o cancelada.";
        }

        $this->session->set_flashdata('message', $data['mensagem']);

        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisarcirurgia");
    }

    function gravarsolicitacaorcamento() {
        $orcamento_id = $this->solicitacirurgia_m->gravarsolicitacaorcamento();

        if ($this->solicitacirurgia_m->gravarsolicitacaorcamentoitens($orcamento_id)) {
            $data['mensagem'] = "Orçamento gravado com sucesso!";
        } else {
            $data['mensagem'] = "Erro ao gravar Orçamento. Opera&ccedil;&atilde;o cancelada.";
        }

        $this->session->set_flashdata('message', $data['mensagem']);

        redirect(base_url() . "centrocirurgico/centrocirurgico/pesquisar");
    }

    function internacaoalta($internacao_id) {

        $data['resultado'] = $this->internacao_m->internacaoalta($internacao_id);
    }

}

?>
