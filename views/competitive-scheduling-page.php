<?php

    $data = "
        var gestor = {};

        gestor.calendario = {
            datas_disponiveis: [05-10-2023, 06-10-2023, 07-10-2023],
            ano_inicio: 2023,
            ano_fim: 2024
        };
    ";

    wp_add_inline_script( 'competitive-scheduling-admin', $data, $position = 'after' );
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__( 'Competitive Scheduling', 'competitive-scheduling' ); ?></h1>
    <div id="_gestor-interface-simples"><div class="ui attached icon message">
        <i class="calendar alternate icon"></i>
        <div class="content">
            <div class="header">
                Instruções
            </div>
            <p>Selecione uma data das disponíveis para visualizar a lista dos atendimentos e modifique as opções abaixo conforme convir.</p>
        </div>
    </div>
    <form class="ui form attached fluid segment" method="post" id="formAgendamentos">
        <div class="two fields">
            <div class="field">
                <label>Data dos Agendamentos</label>
                <input type="hidden" name="data" class="agendamentoData">
                <div class="ui calendar"></div>
            </div>
            <div class="field">
                <label>Estado dos Agendamentos</label>
                <div class="ui dropdown selection" tabindex="0"><select name="estado" class="noselection">
                    <option value="">Selecione um estado...</option>
                    <option value="pre">Pré-Agendamentos</option>
                    <option value="aguardando">Aguardando Confirmação</option>
                    <option value="confirmados">Confirmados</option>
                    <option value="finalizados">Finalizados</option>
                </select><i class="dropdown icon"></i><div class="default text">Selecione um estado...</div><div class="menu transition hidden" tabindex="-1"><div class="item" data-value="pre" data-text="Pré-Agendamentos" style="">Pré-Agendamentos</div><div class="item" data-value="aguardando" data-text="Aguardando Confirmação">Aguardando Confirmação</div><div class="item" data-value="confirmados" data-text="Confirmados">Confirmados</div><div class="item" data-value="finalizados" data-text="Finalizados">Finalizados</div></div></div>
            </div>
        </div>
        <div class="escondido resultados">
            <div class="ui basic fitted segment">
                <span class="dataSelecionada">
                    <div class="ui primary large label">
                        <i class="calendar check icon"></i>
                        <span class="dataSelecionadaValor"></span>
                    </div>
                </span>
                &nbsp;
                <span class="totalPessoas escondido">
                    <div class="ui green large label">
                        <i class="users icon"></i>
                        Total: 
                        <span class="totalValor">0</span>
                    </div>
                </span>
                &nbsp;
                <a class="ui orange large label imprimirBtn escondido">
                    <i class="print icon"></i>
                    Imprimir
                </a>
            </div>
            <div class="tabelaPessoas escondido">
                <!-- tabela-pessoas < --><table class="ui unstackable celled very compact table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <!-- th-senha < --><th>Senha</th><!-- th-senha > -->
                            <th>Acompanhantes</th>
                            <!-- th-email < --><th>Email</th><!-- th-email > -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- cel-agendamento < --><tr>
                        <td>@[[nome]]@</td>
                        <!-- td-senha < --><td>@[[senha]]@</td><!-- td-senha > -->
                        <td>
                            <!-- td-acompanhantes < --><table class="ui definition very compact table">
                                <tbody>
                                    <!-- cel-acompanhante < --><tr>
                                        <td class="nowrap" style="width: 80px;">Acompanhante @[[num]]@</td>
                                        <td>@[[acompanhante]]@</td>
                                    </tr><!-- cel-acompanhante > -->
                                </tbody>
                            </table><!-- td-acompanhantes > -->
                        </td>
                        <!-- td-email < --><td>
                            <!-- enviado < --><div class="ui green large label">
                                <i class="paper plane icon"></i>
                                Enviado
                            </div><!-- enviado > -->
                            <!-- nao-enviado < --><div class="ui yellow large label nowrap">
                                <i class="exclamation triangle icon"></i>
                                Não Enviado
                            </div><!-- nao-enviado > -->
                        </td><!-- td-email > -->
                        </tr><!-- cel-agendamento > -->
                    </tbody>
                </table><!-- tabela-pessoas > -->
            </div>
        </div>
    </form></div>
    
</div>
