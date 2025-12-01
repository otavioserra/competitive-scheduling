# Competitive Scheduling

## Descrição

O **Competitive Scheduling** é um plugin para WordPress que permite agendamento competitivo de eventos contra outros usuários. É ideal para locais com mais pessoas interessadas nos mesmos horários do que vagas disponíveis. Em casos onde há menos vagas do que pessoas interessadas, o plugin ajuda a agendar eventos de forma justa baseada em sorteios aleatórios.

Este plugin suporta tanto o uso de shortcodes (para editores clássicos) quanto blocos Gutenberg (para editores modernos), oferecendo flexibilidade na integração com seu site WordPress.

## Funcionalidades

- **Agendamento Competitivo**: Usuários competem por slots de tempo limitados.
- **Sorteios Aleatórios**: Quando há mais interessados do que vagas, o sistema realiza sorteios justos.
- **Pré-agendamentos e Confirmações**: Suporte a pré-agendamentos que precisam ser confirmados dentro de um período específico.
- **Cupons de Prioridade**: Sistema de cupons para dar prioridade a certos usuários.
- **Interface Amigável**: Usa Fomantic UI para uma experiência visual moderna.
- **Internacionalização**: Suporte completo a traduções (arquivos .pot, .po, .mo incluídos).
- **Bloco Gutenberg**: Integração nativa com o editor de blocos do WordPress.
- **Shortcodes**: Compatibilidade com editores clássicos via shortcodes.
- **E-mails Automáticos**: Envio de notificações para agendamentos, confirmações e cancelamentos.
- **Painel Admin**: Interface administrativa para gerenciar agendamentos, configurações e cupons.
- **API REST**: Endpoints para integração com outras aplicações.

## Requisitos

- WordPress 5.6 ou superior
- PHP 7.0 ou superior
- MySQL 5.6 ou superior

## Instalação

1. Baixe o plugin do repositório ou faça o upload do arquivo ZIP.
2. No painel WordPress, vá para **Plugins > Adicionar Novo**.
3. Clique em **Enviar Plugin** e selecione o arquivo ZIP.
4. Ative o plugin após a instalação.
5. Configure as opções em **Competitive Scheduling > Options**.

### Instalação Manual

1. Descompacte o arquivo ZIP do plugin.
2. Faça upload da pasta `competitive-scheduling` para o diretório `wp-content/plugins/`.
3. Ative o plugin no painel WordPress.

### Dependências

O plugin inclui as seguintes dependências:
- Fomantic UI (versão 2.9.0) para estilos e componentes UI.
- jQuery Mask Plugin para máscaras de entrada.
- Tailwind CSS para estilos adicionais (usado no bloco Gutenberg).

## Configuração

Após a ativação, acesse **Competitive Scheduling > Options** para configurar:
- Informações do estabelecimento.
- Configurações de e-mail.
- Limites de agendamentos por ciclo.
- Períodos de confirmação.
- Opções de debug.

### Cupons de Prioridade

Crie cupons de prioridade em **Competitive Scheduling > Priority Coupons** para dar vantagens a usuários específicos.

## Uso

### Via Shortcode (Editor Clássico)

Use o shortcode `[competitive_scheduling]` em qualquer página ou post para exibir o formulário de agendamento.

Para a versão pública (sem login obrigatório): `[competitive_scheduling_public]`.

Exemplo:
```
[competitive_scheduling id="1,2,3" orderby="date"]
```

Parâmetros:
- `id`: IDs dos agendamentos separados por vírgula.
- `orderby`: Ordem dos agendamentos (padrão: 'date').

### Via Bloco Gutenberg (Editor Moderno)

1. No editor Gutenberg, adicione um novo bloco.
2. Procure por "Competitive Scheduling".
3. Insira o bloco na página.
4. Configure as opções no painel lateral do bloco.

O bloco usa o mesmo backend do shortcode, garantindo consistência.

## Estrutura do Projeto

- `assets/`: CSS e JS personalizados.
- `build/`: Arquivos compilados do bloco Gutenberg.
- `includes/`: Classes principais (database, authentication, interfaces, etc.).
- `languages/`: Arquivos de tradução (.pot, .po, .mo).
- `pages/`: Páginas admin e públicas.
- `post-types/`: Tipos de post personalizados (Priority Coupons).
- `settings/`: Configurações do plugin.
- `shortcodes/`: Implementação dos shortcodes.
- `src/`: Código fonte do bloco Gutenberg (JS, SCSS).
- `vendor/`: Dependências externas (Fomantic UI, etc.).
- `views/`: Templates PHP para as interfaces.

## Desenvolvimento

### Build do Bloco Gutenberg

Para desenvolver o bloco Gutenberg:
1. Instale as dependências: `npm install`
2. Inicie o modo de desenvolvimento: `npm start`
3. Para build de produção: `npm run build`

### Traduções

O plugin suporta internacionalização. Para adicionar traduções:
1. Use o arquivo `languages/competitive-scheduling.pot` como base.
2. Crie arquivos .po e .mo para idiomas específicos.
3. Use ferramentas como Poedit para editar traduções.

## FAQ

**P: O plugin funciona com temas personalizados?**
R: Sim, o plugin é independente de temas e usa suas próprias folhas de estilo.

**P: Como personalizar os e-mails?**
R: As mensagens de e-mail podem ser configuradas nas opções do plugin.

**P: É possível integrar com outros plugins de login?**
R: Sim, o plugin verifica se o Ultimate Member está ativo e redireciona para sua página de login.

**P: Como lidar com conflitos de horários?**
R: O sistema de sorteios garante justiça quando há mais interessados do que vagas.

## Suporte

Para suporte, visite o repositório no GitHub ou entre em contato com o autor: Otávio Campos de Abreu Serra (otavio.serra@ageone.com.br).

## Contribuição

Contribuições são bem-vindas! Siga estes passos:
1. Fork o repositório.
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`).
3. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`).
4. Push para a branch (`git push origin feature/nova-feature`).
5. Abra um Pull Request.

## Licença

Este plugin é licenciado sob a GPL v2 ou posterior. Veja o arquivo LICENSE para mais detalhes.

## Changelog

### Versão 1.1.4
- Melhorias na interface do usuário.
- Correções de bugs.
- Suporte aprimorado ao bloco Gutenberg.

Para o changelog completo, veja o arquivo CHANGELOG.md.