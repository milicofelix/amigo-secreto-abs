# Roadmap do Amigo Secreto ABS

Priorizar login, isolamento dos grupos e privacidade do sorteio. O organizador vê e gerencia exclusivamente os grupos que criou. O Super Admin tem uma visão global, incluindo grupos antigos sem responsável identificado. O participante continua com acesso individual pelo convite.

## Permissões por papel

| Ação | Organizador | Super Admin | Participante |
| --- | --- | --- | --- |
| Listar e consultar grupos | Somente os que criou | Todos os grupos e anos | Grupo do próprio convite, pelo portal |
| Criar grupo | Sim, vinculado à própria conta | Sim, vinculado à própria conta | — |
| Alterar grupo, participantes e restrições | Somente nos grupos que criou e conforme o estado do sorteio | Nos grupos que criou. A gestão de grupos alheios exige uma permissão explícita em fase futura | Atualizar os próprios dados permitidos no portal |
| Importar participantes | Entre grupos que criou, antes do sorteio | Entre grupos que criou, antes do sorteio | — |
| Realizar sorteio | Somente nos grupos que criou | Somente nos grupos que criou | — |
| Consultar pares | Revelação coletiva na data e hora definidas, nos grupos que criou | A visão global mostra o andamento. Acesso a pares de terceiros exige uma regra explícita | Somente o próprio resultado após o sorteio |

## Fase 1 Login e isolamento dos grupos

- [x] Habilitar login, logout e recuperação de senha do Organizador. A criação de contas fica sob controle administrativo na primeira versão.
- [x] Vincular cada grupo ao usuário autenticado no momento da criação. Aplicar permissões no servidor aos painéis, listagens, menus, importações e operações de gestão.
- [x] Conferir no servidor se o participante e a restrição pertencem ao grupo da ação. Substituir o código de exclusão enviado ao navegador por autorização no servidor.
- [x] Inventariar os grupos antigos no MySQL local. Os três grupos estavam sem criador cadastrado e seguem preservados.
- [x] Fazer backup antes da vinculação e confirmar quem criou cada grupo antigo. Os grupos sem vínculo confirmado ficam reservados para a visão do Super Admin na Fase 2.

### Preparação das contas

Execute `docker compose exec app php artisan amigo-secreto:organizador SEU_EMAIL` para criar a conta. O comando solicita nome e senha no terminal. Para habilitar uma conta existente e manter sua senha, acrescente `--enable-existing`.

Após o backup e a confirmação da autoria, vincule cada grupo antigo com `docker compose exec app php artisan amigo-secreto:vincular-grupo ID_DO_GRUPO SEU_EMAIL --confirm-creator`. O comando só aceita grupos sem vínculo e exige uma conta de organizador habilitada.

Para consultar os IDs dos grupos sem vínculo pelo terminal, execute:

```bash
docker compose exec app php artisan tinker --execute='dump(\App\Models\AmigoSecreto\Group::whereNull("user_id")->get(["id", "name", "event_date"])->toArray());'
```

O ambiente local usa e-mail em log por padrão. Configure o transporte de e-mail e a URL pública do app para que a recuperação envie links utilizáveis em produção.

### Teste manual no navegador

1. Crie dois organizadores pelo comando acima. Em janelas separadas, entre em `http://localhost:8085/login`. Cada conta cria um grupo e confere que a listagem e o seletor mostram apenas o próprio grupo.
2. Tente abrir o endereço de edição do grupo da outra conta. A aplicação deve recusar o acesso. Após sair, abrir a área de gestão deve redirecionar para o login.
3. Abra a página inicial em uma janela sem sessão e escolha **Entrar com meu convite**. O portal individual deve carregar sem mostrar a lista administrativa.
4. Em um ambiente com e-mail configurado, use **Esqueci minha senha**, abra o link recebido e entre com a nova senha.

Commit sugerido — `git c "feat: protege a gestão de grupos com login e permissões"`

## Fase 2 Super Admin e visão global

- [ ] Criar o papel **Super Admin** com atribuição restrita e painel separado. Restringir as páginas e consultas globais a esse papel.
- [ ] Listar todos os grupos com filtros por ano, criador e situação do sorteio. Identificar grupos antigos sem vínculo e registrar a confirmação de criadores no histórico administrativo.
- [ ] Mostrar na visão global informações de gestão, como quantidade de participantes e situação do sorteio. Manter contatos, convites e pares sujeitos às permissões específicas.

### Teste manual no navegador

1. Entre como Super Admin e confira os grupos das duas contas e de anos diferentes. Use os filtros e confira se um grupo sem vínculo aparece identificado.
2. Entre como Organizador e tente abrir o painel global. A aplicação deve recusar o acesso e manter a lista restrita aos grupos criados pela conta.

Commit sugerido — `git c "feat: adiciona painel global do super admin"`

## Fase 3 Convites e privacidade

- [ ] Unificar o acesso individual em **Meu Amigo Secreto**, com convites revogáveis e prazo de validade. Entregar o QR ou link individual de forma privada.
- [ ] Restringir contatos, fotos, listas de desejos e registros de acesso às telas e aos papéis que precisam desses dados.
- [ ] Usar avisos genéricos por e-mail e push, com link para o portal. Preservar as preferências de notificações do participante.

### Teste manual no navegador

1. Em uma janela separada, abra um convite válido e confirme que o acesso fica vinculado ao participante correto. Após o sorteio, confira que ele vê somente seu próprio par.
2. Revogue o convite e repita o acesso. O portal deve recusar o convite e orientar a pessoa a falar com o organizador.

Commit sugerido — `git c "feat: unifica a revelação com convites privados"`

## Fase 4 Regras do sorteio e histórico

- [ ] Validar se existe uma combinação possível com as restrições atuais antes de confirmar o sorteio. Explicar quando a configuração impede a formação dos pares.
- [ ] Impedir alterações de participantes e restrições depois de confirmar. Garantir no banco um destinatário por participante e um participante por destinatário em cada sorteio.
- [ ] Definir a data e hora da revelação coletiva. Enviar notificações após a confirmação no banco e registrar os eventos de sorteio, cancelamento autorizado e notificações.

### Teste manual no navegador

1. Monte um grupo fictício com restrições impossíveis. O painel deve rejeitar a configuração e manter o grupo sem resultado.
2. Ajuste as restrições, realize o sorteio e confira o bloqueio da lista. A revelação coletiva deve ficar disponível na hora configurada.

Commit sugerido — `git c "feat: valida restrições e protege o histórico do sorteio"`

## Fase 5 Validação e preparação da publicação

- [ ] Alinhar a suíte de autenticação e perfil ao fluxo adotado e incluir testes de acesso entre grupos. Cobrir rotas web, convites e ações de escrita.
- [ ] Desativar Rifas e PIX por configuração na instalação dedicada ao Amigo Secreto, preservando o código e os dados.
- [ ] Conferir a importação do ano anterior, o upload de fotos, os botões **Voltar**, o cache público do PWA e a sincronização offline. Validar a entrega real de push em Android e iPhone conforme o [planejamento do PWA](PLANEJAMENTO-PWA.md).

### Teste manual no navegador

1. Em um ambiente de teste, percorra login, criação de grupo, importação do ano anterior, convite, sorteio e consulta pelo participante.
2. Repita a abertura pelo ícone, a perda de conexão e a reconexão no PWA. Confira a entrega de um aviso de sorteio em dispositivos de teste.

Commit sugerido — `git c "test: valida permissões e fluxos do amigo secreto"`
