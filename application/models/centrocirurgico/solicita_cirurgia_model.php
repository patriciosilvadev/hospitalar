<?php

require_once APPPATH . 'models/base/BaseModel.php';

//require_once APPPATH . 'models/base/ConvertXml.php';



class solicita_cirurgia_model extends BaseModel {

    var $_internacao_motivosaida_id = null;
    var $_localizacao = null;
    var $_nome = null;

    function solicita_cirurgia_model($internacao_motivosaida_id = null) {
        parent::Model();
        if (isset($internacao_motivosaida_id)) {
            $this->instanciar($internacao_motivosaida_id);
        }
    }

    private function instanciar($internacao_motivosaida_id) {
        if ($internacao_motivosaida_id != 0) {

            $this->db->select('internacao_motivosaida_id,
                            nome');
            $this->db->from('tb_solicitacao_cirurgia');
            $this->db->where('ativo', 'true');
            $this->db->where('internacao_motivosaida_id', $internacao_motivosaida_id);
            $query = $this->db->get();
            $return = $query->result();
            $this->_internacao_motivosaida_id = $internacao_motivosaida_id;
            $this->_nome = $return[0]->nome;
        }
    }

    function listamotivosaida($args = array()) {
        $this->db->select(' internacao_motivosaida_id,
                            nome');
        $this->db->from('tb_solicitacao_cirurgia');
        $this->db->where('ativo', 't');
        if ($args) {
            if (isset($args['nome']) && strlen($args['nome']) > 0) {
                $this->db->where('nome ilike', "%" . $args['nome'] . "%", 'left');
            }
        }
        return $this->db;
    }

    function listamotivosaidapacientes() {
        $this->db->select('nome,
                internacao_motivosaida_id');
        $this->db->from('tb_solicitacao_cirurgia');
        $this->db->where('ativo', 't');
        $return = $this->db->get();
        return $return->result();
    }

    function listaleitomotivosaida() {
        $this->db->select('internacao_leito_id,
                           nome,
                           tipo,
                           condicao,
                           enfermaria_id,
                           ativo');
        $this->db->from('tb_internacao_leito');
        $return = $this->db->get();
        return $return->result();
    }

    function grauparticipacao() {
        $this->db->select("grau_participacao_id as grau_id, 
                           codigo || ' - ' || descricao as grau_participacao,
                           codigo");
        $this->db->from('tb_grau_participacao ec');
        $this->db->where('ec.ativo', 't');
        $this->db->orderby('ec.codigo');
        $return = $this->db->get();
        return $return->result();
    }

    function listaleitocirugia() {
        $this->db->select('internacao_leito_id,
                           nome,
                           tipo,
                           condicao,
                           enfermaria_id,
                           ativo');
        $this->db->from('tb_internacao_leito');
        $this->db->where('condicao', 'Cirurgico');
        $this->db->where('ativo', 't');
        $return = $this->db->get();
        return $return->result();
    }

    function listasolicitacao($args = array()) {

        $this->db->select(' p.paciente_id,
                            p.nome,
                            sc.procedimento_id,
                            sc.solicitacao_cirurgia_id,
                            pt.descricao,
                            sc.data_prevista');
        $this->db->from('tb_solicitacao_cirurgia sc');
        $this->db->where('sc.ativo', 't');
        $this->db->where('sc.excluido', 'f');
        $this->db->join('tb_paciente p', 'p.paciente_id = sc.paciente_id ');
        $this->db->join('tb_procedimento_convenio pc', 'pc.procedimento_convenio_id = sc.procedimento_id ');
        $this->db->where('pc.ativo', 't');
        $this->db->join('tb_procedimento_tuss pt', 'pt.procedimento_tuss_id = pc.procedimento_tuss_id ');
        $this->db->where('pt.ativo', 't');

        if ($args) {
            if (isset($args['nome']) && strlen($args['nome']) > 0) {
                $this->db->where('nome ilike', "%" . $args['nome'] . "%", 'left');
            }
        }
        return $this->db;
    }

    function listarprocedimentosagrupador($agrupador) {
        $this->db->select('procedimento_tuss_id as procedimento_id');
        $this->db->from('tb_procedimentos_agrupados');
        $this->db->where('ativo', 't');
        $this->db->where('agrupador_id', $agrupador);
        $return = $this->db->get();
        return $return->result();
    }

    function verificasolicitacaoprocedimentorepetidos() {
        $this->db->select('');
        $this->db->from('tb_solicitacao_cirurgia_procedimento');
        $this->db->where('ativo', 't');
        $this->db->where('solicitacao_cirurgia_id', $_POST['solicitacao_id']);
        $this->db->where('procedimento_tuss_id', $_POST['procedimentoID']);
        $return = $this->db->get();
        return $return->result();
    }

    function mostrarsaidapaciente($internacao_id) {

        $this->db->select('i.internacao_id,
                           p.nome as paciente,
                           m.nome as motivosaida,
                           i.motivo_saida,
                           m.internacao_motivosaida_id,
                           p.paciente_id,
                           i.data_internacao,
                           i.observacao_saida,
                           i.leito,
                           p.sexo,
                           p.nascimento');
        $this->db->from('tb_internacao i, tb_paciente p, tb_operador o,tb_solicitacao_cirurgia m');
        $this->db->where('i.internacao_id', $internacao_id);
        $this->db->where('p.paciente_id = i.paciente_id');
        $this->db->where('o.operador_id = i.medico_id');

        // $this->db->where('m.internacao_motivosaida_id = i.motivo_saida ');

        $return = $this->db->get();
        return $return->result();
    }

    function excluirsolicitacaocirurgia($solicitacao_id) {
        $this->db->set('excluido', 't');
        $this->db->where('solicitacao_cirurgia_id', $solicitacao_id);
        $this->db->update('tb_solicitacao_cirurgia');
    }

    function excluirsolicitacaoprocedimento($solicitacao_procedimento_id) {
        $this->db->set('ativo', 'f');
        $this->db->where('solicitacao_cirurgia_procedimento_id', $solicitacao_procedimento_id);
        $this->db->update('tb_solicitacao_cirurgia_procedimento');
    }

    function gravarsolicitacaocirurgia() {

        $horario = date("Y-m-d H:i:s");
        $operador_id = $this->session->userdata('operador_id');

        $this->db->set('internacao_id', $_POST['internacao_id']);
        $this->db->set('procedimento_id', $_POST['procedimentoID']);
        $this->db->set('data_solicitacao', $horario);
        $this->db->set('operador_solicitacao', $operador_id);
        $this->db->insert('tb_solicitacao_cirurgia');
    }

    function solicitacirurgia($internacao_id) {

        $this->db->select('p.nome as paciente,
                           i.internacao_id,
                           p.paciente_id,
                           i.data_internacao,
                           i.leito,
                           p.sexo,
                           p.nascimento,
                           ');
        $this->db->from('tb_internacao i');
        $this->db->join('tb_paciente p', 'p.paciente_id = i.paciente_id ');
        $this->db->where('i.internacao_id', $internacao_id);
        $this->db->where('i.ativo', 't');
        $this->db->where('p.paciente_id = i.paciente_id');

        $return = $this->db->get();
        return $return->result();
    }

    function carregarsolicitacaoprocedimento($convenio_id) {

        $this->db->select('pc.procedimento_convenio_id,
                           pc.valortotal,
                           pt.codigo,
                           pt.nome');
        $this->db->from('tb_procedimento_convenio pc');
        $this->db->join('tb_procedimento_tuss pt', 'pt.procedimento_tuss_id = pc.procedimento_tuss_id', 'left');
        $this->db->where('pt.grupo', 'CIRURGICO');
        $this->db->where('pc.ativo', 'true');
        $this->db->where('pc.convenio_id', $convenio_id);
        $return = $this->db->get();
        return $return->result();
    }

    function carregarsolicitacaoagrupador($convenio_id) {


        $this->db->select('an.agrupador_id, an.nome');
        $this->db->from('tb_agrupador_procedimento_nome an');
        $this->db->where('convenio_id', $convenio_id);
        $this->db->where('an.ativo', 't');

        $return = $this->db->get();
        return $return->result();
    }

    function solicitacirurgiaconsulta($exame_id) {

        $this->db->select('p.nome as paciente,
                           p.paciente_id');
        $this->db->from('tb_exames e');
        $this->db->join('tb_paciente p', 'p.paciente_id = e.paciente_id', 'left');
        $this->db->where('e.exames_id', $exame_id);

        $return = $this->db->get();
        return $return->result();
    }

    function listamotivosaidaautocomplete($parametro = null) {
        $this->db->select('internacao_motivosaida_id,
                            nome,
                            localizacao');
        $this->db->from('tb_solicitacao_cirurgia');
        $this->db->where('ativo', 'true');
        if ($parametro != null) {
            $this->db->where('nome ilike', "%" . $parametro . "%");
        }
        $return = $this->db->get();
        return $return->result();
    }

    function listarsolicitacaosprocedimentos($solicitacao_id) {
        $this->db->select('scp.solicitacao_cirurgia_procedimento_id as solicitacao_procedimento_id,
                           c.nome as convenio,
                           pt.nome');
        $this->db->from('tb_solicitacao_cirurgia_procedimento scp');
        $this->db->join('tb_procedimento_convenio pc', 'pc.procedimento_convenio_id = scp.procedimento_tuss_id', 'left');
        $this->db->join('tb_procedimento_tuss pt', 'pt.procedimento_tuss_id = pc.procedimento_tuss_id', 'left');
        $this->db->join('tb_convenio c', 'c.convenio_id = pc.convenio_id', 'left');
        $this->db->where('scp.ativo', 'true');
        $this->db->where('scp.solicitacao_cirurgia_id', $solicitacao_id);
        $return = $this->db->get();
        return $return->result();
    }

    function excluirmotivosaida($internacao_motivosaida_id) {


        $this->db->set('ativo', 'f');
        $this->db->where('internacao_motivosaida_id', $internacao_motivosaida_id);
        $this->db->update('tb_solicitacao_cirurgia');
    }

    function gravarsaida() {

        $horario = date("Y-m-d H:i:s");
        $operador_id = $this->session->userdata('operador_id');

        //Tabela internação alteração
        if ($_POST['motivosaida'] == 'transferencia') {
            $this->db->set('ativo', 'f');
            $this->db->set('hospital_transferencia', $_POST['hospital']);
            $this->db->set('observacao_saida', $_POST['observacao']);
            $this->db->set('data_atualizacao', $horario);
            $this->db->set('data_saida', $horario);
            $this->db->set('operador_atualizacao', $operador_id);
            $this->db->where('paciente_id', $_POST['idpaciente']);
            $this->db->update('tb_internacao');
        } else {
            $this->db->set('ativo', 'f');
            $this->db->set('motivo_saida', $_POST['motivosaida']);
            $this->db->set('observacao_saida', $_POST['observacao']);
            $this->db->set('data_atualizacao', $horario);
            $this->db->set('data_saida', $horario);
            $this->db->set('operador_atualizacao', $operador_id);
            $this->db->where('paciente_id', $_POST['idpaciente']);
            $this->db->update('tb_internacao');
        }
        //Tabela Ocupação alteração
        $this->db->set('data_atualizacao', $horario);
        $this->db->set('operador_atualizacao', $operador_id);
        $this->db->set('ocupado', 'f');
        $this->db->where('paciente_id', $_POST['idpaciente']);
        $this->db->update('tb_internacao_ocupacao');

        //Tabela internacao_leito

        $this->db->set('data_atualizacao', $horario);
        $this->db->set('operador_atualizacao', $operador_id);
        $this->db->set('ativo', 't');
        $this->db->where('internacao_leito_id', $_POST['leito']);
        $this->db->update('tb_internacao_leito');
    }

    function gravarmotivosaida() {

        try {
            $this->db->set('nome', $_POST['nome']);

            $horario = date("Y-m-d H:i:s");
            $operador_id = $this->session->userdata('operador_id');


            // $this->db->set('paciente_id',$_POST['txtPacienteId'] );

            if ($_POST['internacao_motivosaida_id'] == "") {// insert
                $this->db->set('data_cadastro', $horario);
                $this->db->set('operador_cadastro', $operador_id);
                $this->db->insert('tb_solicitacao_cirurgia');
                $erro = $this->db->_error_message();
                if (trim($erro) != "") { // erro de banco
                    return false;
                } else
                    $internacao_motivosaida_id = $this->db->insert_id();
            }
            else { // update
                $internacao_motivosaida_id = $_POST['internacao_motivosaida_id'];
                $this->db->set('data_atualizacao', $horario);
                $this->db->set('operador_atualizacao', $operador_id);
                $this->db->where('internacao_motivosaida_id', $internacao_motivosaida_id);
                $this->db->update('tb_solicitacao_cirurgia');
            }


            return $internacao_motivosaida_id;
        } catch (Exception $exc) {
            return false;
        }
    }

    function listarprocedimentoscirurgia($solicitacao_id) {
        $this->db->select('pt.nome,
                           pc.procedimento_tuss_id,
                           c.nome as convenio,
                           pt.codigo');
        $this->db->from('tb_solicitacao_cirurgia_procedimento cp');
        $this->db->join('tb_procedimento_convenio pc', 'pc.procedimento_convenio_id = cp.procedimento_tuss_id', 'left');
        $this->db->join('tb_procedimento_tuss pt', 'pt.procedimento_tuss_id = pc.procedimento_tuss_id', 'left');
        $this->db->join('tb_convenio c', 'c.convenio_id = pc.convenio_id', 'left');
        $this->db->where('solicitacao_cirurgia_id', $solicitacao_id);
        $this->db->where('cp.ativo', 'true');

        $return = $this->db->get();
        return $return->result();
    }

    function burcarempresa() {
        $empresa = $this->session->userdata('empresa_id');
        $this->db->select('razao_social,
                           cnpj,
                           logradouro,
                           numero,
                           bairro,
                           telefone');
        $this->db->from('tb_empresa');
        $this->db->where('empresa_id', $empresa);

        $return = $this->db->get();
        return $return->result();
    }

    function listardadossolicitacaoautorizar($solicitacao_id) {
        $this->db->select('sc.paciente_id,
                           p.nome as paciente,
                           p.celular,
                           p.telefone,
                           sc.data_prevista,
                           sc.solicitacao_cirurgia_id,
                           sc.via,
                           sc.guia_id,
                           h.nome as hospital,
                           h.valor_taxa,
                           c.nome as convenio,
                           o.nome as solicitante');
        $this->db->from('tb_solicitacao_cirurgia sc');
        $this->db->join('tb_paciente p', 'p.paciente_id = sc.paciente_id', 'left');
        $this->db->join('tb_hospital h', 'h.hospital_id = sc.hospital_id', 'left');
        $this->db->join('tb_convenio c', 'c.convenio_id = sc.convenio', 'left');
        $this->db->join('tb_operador o', 'o.operador_id = sc.medico_solicitante', 'left');
        $this->db->join('tb_solicitacao_orcamento so', 'so.solicitacao_cirurgia_id = sc.solicitacao_cirurgia_id', 'left');
        $this->db->where('sc.solicitacao_cirurgia_id', $solicitacao_id);

        $return = $this->db->get();
        return $return->result();
    }

    function listardadossolicitacaoorcamentoimpressao($solicitacao_id) {
        $this->db->select('sc.paciente_id,
                           p.nome as paciente,
                           p.celular,
                           sc.leito,
                           p.telefone,
                           h.nome as hospital,
                           h.valor_taxa,
                           c.nome as convenio,
                           o.nome as solicitante');
        $this->db->from('tb_solicitacao_cirurgia sc');
        $this->db->join('tb_paciente p', 'p.paciente_id = sc.paciente_id', 'left');
        $this->db->join('tb_hospital h', 'h.hospital_id = sc.hospital_id', 'left');
        $this->db->join('tb_convenio c', 'c.convenio_id = sc.convenio', 'left');
        $this->db->join('tb_operador o', 'o.operador_id = sc.medico_solicitante', 'left');
        $this->db->where('sc.solicitacao_cirurgia_id', $solicitacao_id);

        $return = $this->db->get();
        return $return->result();
    }

    function listardadossolicitacaoorcamento($solicitacao_id) {
        $this->db->select('sc.paciente_id,
                           p.nome as paciente,
                           p.celular,
                           p.telefone,
                           h.nome as hospital,
                           c.nome as convenio,
                           o.nome as solicitante');
        $this->db->from('tb_solicitacao_cirurgia sc');
        $this->db->join('tb_paciente p', 'p.paciente_id = sc.paciente_id', 'left');
        $this->db->join('tb_hospital h', 'h.hospital_id = sc.hospital_id', 'left');
        $this->db->join('tb_convenio c', 'c.convenio_id = sc.convenio', 'left');
        $this->db->join('tb_operador o', 'o.operador_id = sc.medico_solicitante', 'left');
        $this->db->where('sc.solicitacao_cirurgia_id', $solicitacao_id);

        $return = $this->db->get();
        return $return->result();
    }

    function listarprocedimentosolicitacaocirurgica($solicitacao_id) {
        $this->db->select('pt.nome as procedimento,
                           pt.codigo,
                           pc.valortotal,
                           pc.procedimento_convenio_id,
                           scp.solicitacao_cirurgia_procedimento_id');
        $this->db->from('tb_solicitacao_cirurgia sc');
        $this->db->join('tb_solicitacao_cirurgia_procedimento scp', 'scp.solicitacao_cirurgia_id = sc.solicitacao_cirurgia_id', 'left');
        $this->db->join('tb_procedimento_convenio pc', 'pc.procedimento_convenio_id = scp.procedimento_tuss_id', 'left');
        $this->db->join('tb_procedimento_tuss pt', 'pt.procedimento_tuss_id = pc.procedimento_tuss_id', 'left');
        $this->db->where('sc.solicitacao_cirurgia_id', $solicitacao_id);
        $this->db->where('scp.ativo', 't');

        $return = $this->db->get();
        return $return->result();
    }

    function listarequipe($equipe_id) {
        $this->db->select('ec.equipe_cirurgia_id,
                           ec.nome');
        $this->db->from('tb_equipe_cirurgia ec');
        $this->db->where('ec.equipe_cirurgia_id', $equipe_id);
//        $this->db->where('ec.ativo', 't');

        $return = $this->db->get();
        return $return->result();
    }

    function listarequipeoperadores($solicitacaocirurgia_id) {
        $this->db->select('ec.equipe_cirurgia_operadores_id,
                           gp.descricao as funcao,
                           o.nome as medico');
        $this->db->from('tb_equipe_cirurgia_operadores ec');
        $this->db->join('tb_operador o', 'o.operador_id = ec.operador_responsavel', 'left');
        $this->db->join('tb_grau_participacao gp', 'gp.codigo = ec.funcao', 'left');
        $this->db->where('ec.solicitacao_cirurgia_id', $solicitacaocirurgia_id);
        $this->db->where('ec.ativo', 't');
        $this->db->where('gp.ativo', 't');

        $return = $this->db->get();
        return $return->result();
    }

    function gravarnovasolicitacao() {

        try {

            $horario = date("Y-m-d H:i:s");
            $data = date("Y-m-d");
            $operador_id = $this->session->userdata('operador_id');
            $empresa_id = $this->session->userdata('empresa_id');

            $this->db->set('data_prevista', date("Y-m-d", strtotime(str_replace('/', '-', $_POST['data_prevista']))));
            $this->db->set('leito', $_POST['leito']);
            $this->db->set('paciente_id', $_POST['txtNomeid']);
            $this->db->set('medico_solicitante', $_POST['medicoagenda']);
            $this->db->set('convenio', $_POST['convenio']);
            $this->db->set('hospital_id', $_POST['hospital_id']);
            if (isset($_POST['orcamento'])) {
                $this->db->set('orcamento', 'true');
            } else {
                $this->db->set('orcamento', 'false');
            }
            $this->db->set('data_cadastro', $horario);
            $this->db->set('operador_cadastro', $operador_id);
            $this->db->insert('tb_solicitacao_cirurgia');
            $erro = $this->db->_error_message();
            if (trim($erro) != "") { // erro de banco
                return -1;
            }

            $solicitacao_id = $this->db->insert_id();
            return $solicitacao_id;
        } catch (Exception $exc) {
            return -1;
        }
    }

    function gravarsolicitacaoprocedimento() {

        try {
//            var_dump($_POST['procedimento_id']);die;

            $horario = date("Y-m-d H:i:s");
            $operador_id = $this->session->userdata('operador_id');

            $this->db->set('solicitacao_cirurgia_id', $_POST['solicitacao_id']);
            $this->db->set('procedimento_tuss_id', $_POST['procedimentoID']);
            $this->db->set('data_cadastro', $horario);
            $this->db->set('operador_cadastro', $operador_id);
            $this->db->insert('tb_solicitacao_cirurgia_procedimento');
            $erro = $this->db->_error_message();
            if (trim($erro) != "") { // erro de banco
                return false;
            }
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    function excluiritemorcamento($orcamento_id) {

        try {
            $horario = date("Y-m-d H:i:s");
            $operador_id = $this->session->userdata('operador_id');

            $this->db->set('ativo', 'f');
            $this->db->set('data_atualizacao', $horario);
            $this->db->set('operador_atualizacao', $operador_id);
            $this->db->where('solicitacao_cirurgia_orcamento_id', $orcamento_id);
            $this->db->update('tb_solicitacao_cirurgia_orcamento');
            $erro = $this->db->_error_message();
            if (trim($erro) != "") { // erro de banco
                return false;
            }
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    function excluiritemequipe($cirurgia_operadores_id) {

        $horario = date("Y-m-d H:i:s");
        $operador_id = $this->session->userdata('operador_id');

        $this->db->set('ativo', 'f');
        $this->db->set('data_atualizacao', $horario);
        $this->db->set('operador_atualizacao', $operador_id);
        $this->db->where('equipe_cirurgia_operadores_id', $cirurgia_operadores_id);
//        $this->db->where('equipe_cirurgia_id', $equipe_id);
        $this->db->update('tb_equipe_cirurgia_operadores');
    }

    function excluiroperadorequipecirurgica($guia_id, $funcao_id) {

        $horario = date("Y-m-d H:i:s");
        $operador_id = $this->session->userdata('operador_id');

        $this->db->set('ativo', 'f');
        $this->db->set('data_atualizacao', $horario);
        $this->db->set('operador_atualizacao', $operador_id);
        $this->db->where('funcao', $funcao_id);
        $this->db->where('guia_id', $guia_id);
//        $this->db->where('equipe_cirurgia_id', $equipe_id);
        $this->db->update('tb_agenda_exame_equipe');
    }
    
    function excluiroperadorequipecirurgicaeditar($guia_id, $funcao_id, $solicitacao_id) {

        $horario = date("Y-m-d H:i:s");
        $operador_id = $this->session->userdata('operador_id');

        $this->db->set('ativo', 'f');
        $this->db->set('data_atualizacao', $horario);
        $this->db->set('operador_atualizacao', $operador_id);
        $this->db->where('funcao', $funcao_id);
        $this->db->where('guia_id', $guia_id);
//        $this->db->where('equipe_cirurgia_id', $equipe_id);
        $this->db->update('tb_agenda_exame_equipe');
        
        $this->db->set('ativo', 'f');
        $this->db->set('data_atualizacao', $horario);
        $this->db->set('operador_atualizacao', $operador_id);
        $this->db->where('funcao', $funcao_id);
        $this->db->where('solicitacao_cirurgia_id', $solicitacao_id);
//        $this->db->where('equipe_cirurgia_id', $equipe_id);
        $this->db->update('tb_equipe_cirurgia_operadores');
    }

    function listarmedicocirurgiaautocomplete($parametro = null) {
        $this->db->select('operador_id,
                           nome');
        $this->db->from('tb_operador');
        $this->db->where('consulta', 'true');
        $this->db->where('ativo', 'true');
        $this->db->orderby('nome');
        if ($parametro != null) {
            $this->db->where('nome ilike', "%" . $parametro . "%");
        }
        $return = $this->db->get();
        return $return->result();
    }

    function verificaorcamento($solicitacao_id) {
        $this->db->select('grau_participacao,
                           procedimento_tuss_id,
                           operador_responsavel,
                           valor,
                           solicitacao_cirurgia_orcamento_id');
        $this->db->from('tb_solicitacao_cirurgia_orcamento');
        $this->db->where('ativo', 'true');
        $this->db->where('solicitacao_cirurgia_id', $solicitacao_id);
        $return = $this->db->get();
        return $return->result();
    }

    function buscarnomesimpressao($solicitacao_id) {
        $this->db->select('o.nome as medico,
                           p.nome as paciente,
                           c.nome as convenio');
        $this->db->from('tb_solicitacao_cirurgia sc');
        $this->db->join('tb_operador o', 'o.operador_id = sc.medico_agendado', 'left');
        $this->db->join('tb_paciente p', 'p.paciente_id = sc.paciente_id', 'left');
        $this->db->join('tb_convenio c', 'c.convenio_id = sc.convenio', 'left');
        $this->db->where('sc.ativo', 'true');
        $this->db->where('sc.solicitacao_cirurgia_id', $solicitacao_id);
        $return = $this->db->get();
        return $return->result();
    }

    function listarprocedimentoorcamentofuncao($cirurgia_procedimento_id) {
        $this->db->select('o.nome as medico,
                           soe.valor,
                           gp.codigo,
                           gp.descricao');
        $this->db->from('tb_solicitacao_orcamento_equipe soe');
        $this->db->join('tb_operador o', 'o.operador_id = soe.operador_responsavel', 'left');
        $this->db->join('tb_grau_participacao gp', 'gp.codigo = soe.funcao', 'left');
        $this->db->where('soe.solicitacao_cirurgia_procedimento_id', $cirurgia_procedimento_id);
        $this->db->where('soe.ativo', 'true');
        $this->db->where('gp.ativo', 'true');
        $return = $this->db->get();
        return $return->result();
    }

    function impressaoorcamento($solicitacao_id) {
        $this->db->select('pt.nome as procedimento,
                           scp.solicitacao_cirurgia_procedimento_id as cirurgia_procedimento_id,
                           scp.valor');
        $this->db->from('tb_solicitacao_cirurgia_procedimento scp');
        $this->db->join('tb_procedimento_convenio pc', 'pc.procedimento_convenio_id = scp.procedimento_tuss_id', 'left');
        $this->db->join('tb_procedimento_tuss pt', 'pt.procedimento_tuss_id = pc.procedimento_tuss_id', 'left');
        $this->db->where('scp.ativo', 'true');
        $this->db->where('scp.solicitacao_cirurgia_id', $solicitacao_id);
        $return = $this->db->get();
        return $return->result();
    }

    function gravarsolicitacaorcamentoitens($orcamento_id) {

        try {

            $horario = date("Y-m-d H:i:s");
            $operador_id = $this->session->userdata('operador_id');

            // Trazendo os procedimentos
            $this->db->select(' horario_especial,
                                valor,
                                solicitacao_cirurgia_procedimento_id');
            $this->db->from('tb_solicitacao_cirurgia_procedimento');
            $this->db->where("ativo", 't');
            $this->db->where('solicitacao_cirurgia_id', $_POST['txtsolcitacao_id']);
            $this->db->orderby('valor DESC');
            $procedimentos = $this->db->get()->result();

//            echo "<pre>";
            // Trazendo a lista com todos os integrantes da equipe cirurgica
            $this->db->select('leito');
            $this->db->from('tb_solicitacao_cirurgia');
            $this->db->where('solicitacao_cirurgia_id', $_POST['txtsolcitacao_id']);
            $solicitacao = $this->db->get()->result();

            // Trazendo a lista com todos os integrantes da equipe cirurgica
            $this->db->select('funcao, gp.descricao, operador_responsavel');
            $this->db->from('tb_equipe_cirurgia_operadores eco');
            $this->db->join('tb_grau_participacao gp', 'gp.grau_participacao_id = eco.funcao', 'left');
            $this->db->where('eco.ativo', 't');
            $this->db->where('solicitacao_cirurgia_id', $_POST['txtsolcitacao_id']);
            $equipe = $this->db->get()->result();

            // Trazendo o valor dos percentuais configurados
            $this->db->select(' leito_enfermaria,
                                leito_apartamento,
                                mesma_via,
                                via_diferente,
                                horario_especial,
                                valor,
                                valor_base');
            $this->db->from('tb_centrocirurgico_percentual_outros cpo');
            $this->db->where("ativo", 't');
            $query = $this->db->get();
            $return = $query->result();

            foreach ($return as $value) {

                if ($value->horario_especial == 't') {
                    $horario_especial = ($value->valor / 100);
                    continue;
                }

                if ($value->leito_enfermaria == 't') {
                    if ($value->mesma_via == 't') {
                        $enfermaria_mesma_via['maior'] = (float) $value->valor / 100;
                        $enfermaria_mesma_via['base'] = (float) $value->valor_base / 100;
                    } else {
                        $enfermaria_via_diferente['maior'] = (float) $value->valor / 100;
                        $enfermaria_via_diferente['base'] = (float) $value->valor_base / 100;
                    }
                } else {
                    if ($value->mesma_via == 't') {
                        $apartamento_mesma_via['maior'] = (float) $value->valor / 100;
                        $apartamento_mesma_via['base'] = (float) $value->valor_base / 100;
                    } else {
                        $apartamento_via_diferente['maior'] = (float) $value->valor / 100;
                        $apartamento_via_diferente['base'] = (float) $value->valor_base / 100;
                    }
                }
            }

            $valMedico = 0;

            foreach ($equipe as $value) {
                $i = 0;

                foreach ($procedimentos as $item) {
                    $valor = (float) $item->valor;
                    $valProcedimento = ( $item->horario_especial == 't' ) ? ($valor) + ($valor * $horario_especial) : $valor;

                    if ($solicitacao[0]->leito == 'ENFERMARIA') {// LEITO DE ENFERMARIA
                        if ($_POST['via'] == 'D') {// VIA DIFERENTE
                            if ($i == 0) {
                                $valMedicoProc = $valProcedimento * $enfermaria_via_diferente['maior'];
                            } else {
                                $valMedicoProc = ($valProcedimento * $enfermaria_via_diferente['base']);
                            }
                        } elseif ($_POST['via'] == 'M') {// MESMA VIA
                            if ($i == 0) {
                                $valMedicoProc = $valProcedimento * $enfermaria_mesma_via['maior'];
                            } else {
                                $valMedicoProc = ($valProcedimento * $enfermaria_mesma_via['base']);
                            }
                        }
                    } else { //APARTAMENTO
                        if ($_POST['via'] == 'D') {// VIA DIFERENTE
                            if ($i == 0) {
                                $valMedicoProc = $valProcedimento * $apartamento_via_diferente['maior'];
                            } else {
                                $valMedicoProc = ($valProcedimento * $apartamento_via_diferente['base']);
                            }
                        } elseif ($_POST['via'] == 'M') {// MESMA VIA
                            if ($i == 0) {
                                $valMedicoProc = $valProcedimento * $apartamento_mesma_via['maior'];
                            } else {
                                $valMedicoProc = $valProcedimento * $apartamento_mesma_via['base'];
                            }
                        }
                    }

                    //VALOR DO CIRURGIAO/ANESTESISTA
                    $valMedico = $valMedicoProc;

                    if ((int) $value->funcao != 0) {
                        $this->db->select('valor');
                        $this->db->from('tb_centrocirurgico_percentual_funcao');
                        $this->db->where("funcao", $value->funcao);
                        $query = $this->db->get();
                        $return2 = $query->result();

                        $this->db->select('valor');
                        $this->db->from('tb_centrocirurgico_percentual_funcao');
                        $this->db->where("funcao", 0);
                        $query_0 = $this->db->get();
                        $return_0 = $query_0->result();
                        //DEFININDO OS VALORES
                        $val_cirurgiao = number_format($valMedico * ($return_0[0]->valor / 100), 2, '.', '');
                        $val = number_format($val_cirurgiao * ($return2[0]->valor / 100), 2, '.', '');
                    } else {
                        $this->db->select('valor');
                        $this->db->from('tb_centrocirurgico_percentual_funcao');
                        $this->db->where("funcao", $value->funcao);
                        $query = $this->db->get();
                        $return2 = $query->result();
                        $val = number_format($valMedico * ($return2[0]->valor / 100), 2, '.', '');
                    }
//                    var_dump($val);
//                    die;
                    $horario = date("Y-m-d H:i:s");
                    $operador_id = $this->session->userdata('operador_id');

                    $this->db->set('operador_responsavel', $value->operador_responsavel);
                    $this->db->set('solicitacao_orcamento_id', $orcamento_id);
                    $this->db->set('solicitacao_cirurgia_procedimento_id', $item->solicitacao_cirurgia_procedimento_id);
                    $this->db->set('valor', $val);
                    $this->db->set('funcao', $value->funcao);

                    $this->db->set('data_cadastro', $horario);
                    $this->db->set('operador_cadastro', $operador_id);
                    $this->db->insert('tb_solicitacao_orcamento_equipe');

                    $i++;
                }
            }
//            die;
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    function gravarprocedimentosolicitacaocirurgica($guia_id) {

        try {

            $this->db->set('guia_id', $guia_id);
            $this->db->where('solicitacao_cirurgia_id', $_POST['txtsolcitacao_id']);
            $this->db->update('tb_solicitacao_cirurgia');

            $horario = date("Y-m-d H:i:s");
            $data = date("Y-m-d");
            $operador_id = $this->session->userdata('operador_id');
            $empresa_id = $this->session->userdata('empresa_id');

            $this->db->select('ag.via, ag.leito');
            $this->db->from('tb_ambulatorio_guia ag');
            $this->db->where("ag.ambulatorio_guia_id", $guia_id);
            $query = $this->db->get()->result();
            $guia = $query[0];

            // Trazendo a lista com todos os integrantes da equipe cirurgica
            $this->db->select('funcao, gp.codigo, operador_responsavel');
            $this->db->from('tb_equipe_cirurgia_operadores eco');
            $this->db->join('tb_grau_participacao gp', 'gp.grau_participacao_id = eco.funcao', 'left');
            $this->db->where('eco.ativo', 't');
            $this->db->where('solicitacao_cirurgia_id', $_POST['txtsolcitacao_id']);
            $equipe = $this->db->get()->result();

            // Trazendo o valor dos percentuais configurados
            $this->db->select(' leito_enfermaria,
                                leito_apartamento,
                                mesma_via,
                                via_diferente,
                                horario_especial,
                                valor,
                                valor_base');
            $this->db->from('tb_centrocirurgico_percentual_outros cpo');
            $this->db->where("ativo", 't');
            $query = $this->db->get();
            $return = $query->result();
            foreach ($return as $value) {

                if ($value->horario_especial == 't') {
                    $horario_especial = ($value->valor / 100);
                    continue;
                }

                if ($value->leito_enfermaria == 't') {
                    if ($value->mesma_via == 't') {
                        $enfermaria_mesma_via['maior'] = (float) $value->valor / 100;
                        $enfermaria_mesma_via['base'] = (float) $value->valor_base / 100;
                    } else {
                        $enfermaria_via_diferente['maior'] = (float) $value->valor / 100;
                        $enfermaria_via_diferente['base'] = (float) $value->valor_base / 100;
                    }
                } else {
                    if ($value->mesma_via == 't') {
                        $apartamento_mesma_via['maior'] = (float) $value->valor / 100;
                        $apartamento_mesma_via['base'] = (float) $value->valor_base / 100;
                    } else {
                        $apartamento_via_diferente['maior'] = (float) $value->valor / 100;
                        $apartamento_via_diferente['base'] = (float) $value->valor_base / 100;
                    }
                }
            }

            $this->db->select('a.agenda_exames_id,
                                a.data,
                                a.guia_id,
                                a.tipo,
                                a.horario_especial,
                                a.procedimento_tuss_id,
                                a.valor_total,
                                pt.nome as procedimento,
                                c.nome as convenio,
                                a.observacoes');
            $this->db->from('tb_agenda_exames a');
            $this->db->join('tb_procedimento_convenio pc', 'pc.procedimento_convenio_id = a.procedimento_tuss_id', 'left');
            $this->db->join('tb_procedimento_tuss pt', 'pt.procedimento_tuss_id = pc.procedimento_tuss_id', 'left');
            $this->db->join('tb_convenio c', 'c.convenio_id = pc.convenio_id', 'left');
            $this->db->where("a.guia_id", $guia_id);
            $this->db->orderby("a.valor_total DESC");
            $procedimentos = $this->db->get()->result();

            $valMedico = 0;
            foreach ($equipe as $item) {
                for ($i = 0; $i < count($procedimentos); $i++) {
                    $valor = (float) $procedimentos[$i]->valor_total;
                    $valProcedimento = ($procedimentos[$i]->horario_especial == 't') ? ($valor) + ($valor * $horario_especial) : $valor;

                    if ($guia->leito == 'ENFERMARIA') {// LEITO DE ENFERMARIA
                        if ($guia->via == 'D') {// VIA DIFERENTE
                            if ($i == 0) {
                                $valMedicoProc = $valProcedimento * $enfermaria_via_diferente['maior'];
                            } else {
                                $valMedicoProc = ($valProcedimento * $enfermaria_via_diferente['base']);
                            }
                        } elseif ($guia->via == 'M') {// MESMA VIA
                            if ($i == 0) {
                                $valMedicoProc = $valProcedimento * $enfermaria_mesma_via['maior'];
                            } else {
                                $valMedicoProc = ($valProcedimento * $enfermaria_mesma_via['base']);
                            }
                        }
                    } else { //APARTAMENTO
                        if ($guia->via == 'D') {// VIA DIFERENTE
                            if ($i == 0) {
                                $valMedicoProc = $valProcedimento * $apartamento_via_diferente['maior'];
                            } else {
                                $valMedicoProc = ($valProcedimento * $apartamento_via_diferente['base']);
                            }
                        } elseif ($guia->via == 'M') {// MESMA VIA
                            if ($i == 0) {
                                $valMedicoProc = $valProcedimento * $apartamento_mesma_via['maior'];
                            } else {
                                $valMedicoProc = $valProcedimento * $apartamento_mesma_via['base'];
                            }
                        }
                    }

                    //VALOR DO CIRURGIAO/ANESTESISTA
                    $valMedico = $valMedicoProc;

                    if ((int) $item->codigo != 0) {
                        $this->db->select('valor');
                        $this->db->from('tb_centrocirurgico_percentual_funcao');
                        $this->db->where("funcao", $item->codigo);
                        $query = $this->db->get();
                        $return2 = $query->result();

                        $this->db->select('valor');
                        $this->db->from('tb_centrocirurgico_percentual_funcao');
                        $this->db->where("funcao", 0);
                        $query = $this->db->get();
                        $return_0 = $query->result();

                        //DEFININDO OS VALORES
                        $val_cirurgiao = number_format($valMedico * ($return_0[0]->valor / 100), 2, '.', '');
                        $val = number_format($val_cirurgiao * ($return2[0]->valor / 100), 2, '.', '');
                    } else {
                        $this->db->select('valor');
                        $this->db->from('tb_centrocirurgico_percentual_funcao');
                        $this->db->where("funcao", (int) $item->codigo);
                        $query = $this->db->get();
                        $return2 = $query->result();
                        $val = number_format($valMedico * ($return2[0]->valor / 100), 2, '.', '');
                    }

                    $horario = date("Y-m-d H:i:s");
                    $operador_id = $this->session->userdata('operador_id');

                    $this->db->set('data_cadastro', $horario);
                    $this->db->set('operador_cadastro', $operador_id);
                    $this->db->set('operador_responsavel', $item->operador_responsavel);
                    $this->db->set('agenda_exames_id', $procedimentos[$i]->agenda_exames_id);
                    $this->db->set('guia_id', $procedimentos[$i]->guia_id);
                    $this->db->set('valor', $val);
                    $this->db->set('funcao', $item->codigo);
                    $this->db->insert('tb_agenda_exame_equipe');
                }
            }

            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    function gravareditarcirurgia() {

        try {
            // Trazendo a lista com todos os integrantes da equipe cirurgica
            $this->db->select('leito, convenio, paciente_id, guia_id');
            $this->db->from('tb_solicitacao_cirurgia');
            $this->db->where('solicitacao_cirurgia_id', $_POST['txtsolcitacao_id']);
            $solicitacao = $this->db->get()->result();

            $horario = date("Y-m-d H:i:s");
            $data = date("Y-m-d");
            $operador_id = $this->session->userdata('operador_id');
            $empresa_id = $this->session->userdata('empresa_id');


            $this->db->set('via', $_POST['via']);
            $this->db->set('data_prevista', $_POST['txtdata']);
            $this->db->set('situacao', 'FATURAMENTO_PENDENTE');
            $this->db->where('solicitacao_cirurgia_id', $_POST['txtsolcitacao_id']);
            $this->db->update('tb_solicitacao_cirurgia');

            $this->db->set('inicio', $_POST['hora']);
            $this->db->set('data', $_POST['txtdata']);
            $this->db->where('guia_id', $solicitacao[0]->guia_id);
            $this->db->update('tb_agenda_exames');


            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    function gravarguiasolicitacaocirurgica() {

        try {

            $horario = date("Y-m-d H:i:s");
            $data = date("Y-m-d");
            $operador_id = $this->session->userdata('operador_id');
            $empresa_id = $this->session->userdata('empresa_id');

            // Trazendo os procedimentos
            $this->db->select(' horario_especial,
                                valor,
                                desconto,
                                solicitacao_cirurgia_procedimento_id,
                                procedimento_tuss_id');
            $this->db->from('tb_solicitacao_cirurgia_procedimento');
            $this->db->where("ativo", 't');
            $this->db->where('solicitacao_cirurgia_id', $_POST['txtsolcitacao_id']);
            $this->db->orderby('valor DESC');
            $procedimentos = $this->db->get()->result();

            // Trazendo a lista com todos os integrantes da equipe cirurgica
            $this->db->select('leito, convenio, paciente_id');
            $this->db->from('tb_solicitacao_cirurgia');
            $this->db->where('solicitacao_cirurgia_id', $_POST['txtsolcitacao_id']);
            $solicitacao = $this->db->get()->result();

            /* GRAVANDO OS PROCEDIMENTOS NA GUIA */
            $this->db->set('via', $_POST['via']);
            $this->db->set('empresa_id', $empresa_id);
            $this->db->set('equipe', 't');
            $this->db->set('tipo', 'CIRURGICO');
            $this->db->set('data_criacao', $data);
            $this->db->set('leito', $solicitacao[0]->leito);
            $this->db->set('convenio_id', $solicitacao[0]->convenio);
            $this->db->set('paciente_id', $solicitacao[0]->paciente_id);
            $this->db->set('data_cadastro', $horario);
            $this->db->set('operador_cadastro', $operador_id);
            $this->db->insert('tb_ambulatorio_guia');
            $ambulatorio_guia_id = $this->db->insert_id();

            foreach ($procedimentos as $item) {
                $this->db->set('operador_autorizacao', $operador_id);
                $this->db->set('empresa_id', $empresa_id);
                $this->db->set('tipo', 'CIRURGICO');
                $this->db->set('ativo', 'f');
                $this->db->set('cancelada', 'f');
                $this->db->set('confirmado', 't');
                $this->db->set('valor', $item->valor);
                $this->db->set('valor_total', ( $item->valor - ($item->valor * $item->desconto) / 100));

                if ($_POST['formapamento'] != '') {
                    $this->db->set('valor1', ( $item->valor - ($item->valor * $item->desconto) / 100));
                    $this->db->set('forma_pagamento', $_POST['formapamento']);
                    $this->db->set('operador_faturamento', $operador_id);
                    $this->db->set('data_faturamento', $horario);
                }

                $this->db->set('situacao', 'OK');
                $this->db->set('quantidade', 1);
                $this->db->set('data', date("Y-m-d", strtotime(str_replace('/', '-', $_POST['txtdata']))));
                $this->db->set('inicio', $_POST['hora']);
                $this->db->set('procedimento_tuss_id', $item->procedimento_tuss_id);
                $this->db->set('guia_id', $ambulatorio_guia_id);

                if ($item->horario_especial == 't') {
                    $this->db->set('horario_especial', 't');
                } else {
                    $this->db->set('horario_especial', 'f');
                }

                $this->db->set('data_autorizacao', $horario);
                $this->db->set('paciente_id', $solicitacao[0]->paciente_id);

                $this->db->set('data_cadastro', $horario);
                $this->db->set('operador_cadastro', $operador_id);
                $this->db->insert('tb_agenda_exames');
                $agenda_exames_id = $this->db->insert_id();

                $this->db->set('agenda_exames_id', $agenda_exames_id);
                $this->db->where('solicitacao_cirurgia_procedimento_id', $item->solicitacao_cirurgia_procedimento_id);
                $this->db->update('tb_solicitacao_cirurgia_procedimento');
            }

            return $ambulatorio_guia_id;
        } catch (Exception $exc) {
            return false;
        }
    }

    function autorizarsolicitacaocirurgica() {

        try {

            $horario = date("Y-m-d H:i:s");
            $operador_id = $this->session->userdata('operador_id');

            $this->db->set('autorizado', 't');
            $this->db->set('via', $_POST['via']);
            $this->db->set('data_prevista', $_POST['txtdata']);
            $this->db->set('situacao', 'FATURAMENTO_PENDENTE');
            $this->db->where('solicitacao_cirurgia_id', $_POST['txtsolcitacao_id']);
            $this->db->update('tb_solicitacao_cirurgia');

            $_POST['desconto'] = (float) $_POST['desconto'];

            return $_POST['txtsolcitacao_id'];
        } catch (Exception $exc) {
            return false;
        }
    }

    function gravarsolicitacaorcamento() {

        try {

            $horario = date("Y-m-d H:i:s");
            $operador_id = $this->session->userdata('operador_id');

            $this->db->set('via', $_POST['via']);
            $this->db->set('situacao', 'ORCAMENTO_COMPLETO');
            $this->db->set('orcamento_completo', 't');
            $this->db->where('solicitacao_cirurgia_id', $_POST['txtsolcitacao_id']);
            $this->db->update('tb_solicitacao_cirurgia');

            $_POST['desconto'] = (float) $_POST['desconto'];

            $this->db->set('solicitacao_cirurgia_id', $_POST['txtsolcitacao_id']);
            $this->db->set('data_cadastro', $horario);
            $this->db->set('operador_cadastro', $operador_id);
            $this->db->insert('tb_solicitacao_orcamento');
            $orcamento_id = $this->db->insert_id();

            foreach ($_POST['cirurgia_procedimento_id'] as $key => $item) {
                $valor = (float) $_POST['valor'][$key];

                $this->db->set('horario_especial', (isset($_POST['horEspecial'][$key]) ? 't' : 'f'));
                $this->db->set('valor', $valor);
                $this->db->where('solicitacao_cirurgia_procedimento_id', $_POST['cirurgia_procedimento_id'][$key]);
                $this->db->update('tb_solicitacao_cirurgia_procedimento');
            }

            return $orcamento_id;
        } catch (Exception $exc) {
            return false;
        }
    }

    function gravarequipe() {

        try {

            $horario = date("Y-m-d H:i:s");
            $operador_id = $this->session->userdata('operador_id');

            $this->db->set('nome', $_POST['nome']);
            $this->db->set('data_cadastro', $horario);
            $this->db->set('operador_cadastro', $operador_id);
            $this->db->insert('tb_equipe_cirurgia');
            $equipe_id = $this->db->insert_id();
            if (trim($erro) != "") { // erro de banco
                return -1;
            }

            return $equipe_id;
        } catch (Exception $exc) {
            return -1;
        }
    }

}

?>
