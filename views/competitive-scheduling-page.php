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
                <div class="ui calendar"><div class="calendar" tabindex="0"><table class="ui celled center aligned unstackable table day seven column"><thead><tr><th colspan="7"><span class="link">Outubro 2023</span><span class="prev link"><i class="chevron left icon"></i></span><span class="next link"><i class="chevron right icon"></i></span></th></tr><tr><th>D</th><th>S</th><th>T</th><th>Q</th><th>Q</th><th>S</th><th>S</th></tr></thead><tbody><tr><td class="link disabled">1</td><td class="link disabled">2</td><td class="link inverted blue active today">3</td><td class="link disabled">4</td><td class="link inverted blue">5</td><td class="link disabled">6</td><td class="link disabled">7</td></tr><tr><td class="link disabled">8</td><td class="link disabled">9</td><td class="link inverted blue">10</td><td class="link disabled">11</td><td class="link inverted blue">12</td><td class="link disabled">13</td><td class="link disabled">14</td></tr><tr><td class="link disabled">15</td><td class="link disabled">16</td><td class="link inverted blue">17</td><td class="link disabled">18</td><td class="link inverted blue">19</td><td class="link disabled">20</td><td class="link disabled">21</td></tr><tr><td class="link disabled">22</td><td class="link disabled">23</td><td class="link inverted blue">24</td><td class="link disabled">25</td><td class="link inverted blue">26</td><td class="link disabled">27</td><td class="link disabled">28</td></tr><tr><td class="link disabled">29</td><td class="link disabled">30</td><td class="link inverted blue">31</td><td class="link disabled">1</td><td class="link disabled">2</td><td class="link disabled">3</td><td class="link disabled">4</td></tr><tr><td class="link disabled">5</td><td class="link disabled">6</td><td class="link disabled">7</td><td class="link disabled">8</td><td class="link disabled">9</td><td class="link disabled">10</td><td class="link disabled">11</td></tr></tbody></table></div></div>
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
