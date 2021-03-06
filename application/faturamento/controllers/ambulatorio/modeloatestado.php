<?php

require_once APPPATH . 'controllers/base/BaseController.php';

/**
 * Esta classe é o controler de Servidor. Responsável por chamar as funções e views, efetuando as chamadas de models
 * @author Equipe de desenvolvimento APH
 * @version 1.0
 * @copyright Prefeitura de Fortaleza
 * @access public
 * @package Model
 * @subpackage GIAH
 */
class Modeloatestado extends BaseController {

    function Modeloatestado() {
        parent::Controller();
        $this->load->model('ambulatorio/modeloatestado_model', 'modeloatestado');
        $this->load->model('seguranca/operador_model', 'operador_m');
        $this->load->model('ambulatorio/procedimento_model', 'procedimento');
        $this->load->library('mensagem');
        $this->load->library('utilitario');
        $this->load->library('pagination');
        $this->load->library('validation');
    }

    function index() {
        $this->pesquisar();
    }

    function pesquisar($args = array()) {

        $this->loadView('ambulatorio/modeloatestado-lista', $args);

//            $this->carregarView($data);
    }

    function carregarmodeloatestado($exame_modeloatestado_id) {
        $obj_modeloatestado = new modeloatestado_model($exame_modeloatestado_id);
        $data['obj'] = $obj_modeloatestado;
        $data['medicos'] = $this->operador_m->listarmedicos();
        $data['procedimentos'] = $this->procedimento->listarprocedimentos();
//        $this->load->View('ambulatorio/modeloatestado-form', $data);
        $this->load->View('ambulatorio/modeloatestado-form', $data);
    }

    function excluir($exame_modeloatestado_id) {
        if ($this->procedimento->excluir($exame_modeloatestado_id)) {
            $mensagem = 'Sucesso ao excluir a Modeloatestado';
        } else {
            $mensagem = 'Erro ao excluir a modeloatestado. Opera&ccedil;&atilde;o cancelada.';
        }

        $this->session->set_flashdata('message', $mensagem);
        redirect(base_url() . "ambulatorio/modeloatestado");
    }

    function gravar() {
        $exame_modeloatestado_id = $this->modeloatestado->gravar();
        if ($exame_modeloatestado_id == "-1") {
            $data['mensagem'] = 'Erro ao gravar a Modeloatestado. Opera&ccedil;&atilde;o cancelada.';
        } else {
            $data['mensagem'] = 'Sucesso ao gravar a Modeloatestado.';
        }
        $this->session->set_flashdata('message', $data['mensagem']);
        redirect(base_url() . "ambulatorio/modeloatestado");
    }

    private function carregarView($data = null, $view = null) {
        if (!isset($data)) {
            $data['mensagem'] = '';
        }

        if ($this->utilitario->autorizar(2, $this->session->userdata('modulo')) == true) {
            $this->load->view('header', $data);
            if ($view != null) {
                $this->load->view($view, $data);
            } else {
                $this->load->view('giah/servidor-lista', $data);
            }
        } else {
            $data['mensagem'] = $this->mensagem->getMensagem('login005');
            $this->load->view('header', $data);
            $this->load->view('home');
        }
        $this->load->view('footer');
    }

}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */
