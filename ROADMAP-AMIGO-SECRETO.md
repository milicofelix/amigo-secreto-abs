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

- [x] Criar o papel **Super Admin** com atribuição restrita e painel separado. Restringir as páginas e consultas globais a esse papel.
- [x] Listar todos os grupos com filtros por ano, criador e situação do sorteio. Identificar grupos antigos sem vínculo e registrar a confirmação de criadores no histórico administrativo.
- [x] Mostrar na visão global informações de gestão, como quantidade de participantes e situação do sorteio. Manter contatos, convites e pares sujeitos às permissões específicas.

### Habilitar o Super Admin

Execute `docker compose exec app php artisan amigo-secreto:super-admin SEU_EMAIL` para conceder a permissão a uma conta de organizador habilitada. Acrescente `--revoke` para revogar a permissão.

O histórico registra as confirmações feitas pelo comando `amigo-secreto:vincular-grupo` a partir desta fase.

### Teste manual no navegador

1. Entre como Super Admin e abra `http://localhost:8085/super-admin/grupos`. Confira os grupos das duas contas e de anos diferentes. Combine os filtros e selecione **Sem criador vinculado** para localizar os grupos antigos sem responsável.
2. Em outra sessão, entre como Organizador e tente abrir o painel global. A aplicação deve responder com 403. Em **Meus grupos**, a conta deve ver somente os grupos que criou.
3. Em um banco de teste, vincule um grupo antigo pelo terminal. Recarregue o painel global e confira se a confirmação aparece no histórico.

Commit sugerido — `git c "feat: adiciona painel global do super admin"`

## Fase 3 Convites e privacidade

- [x] Unificar o acesso individual em **Meu Amigo Secreto**, com convites revogáveis e prazo de validade. Entregar o QR ou link individual de forma privada.
- [x] Restringir contatos, fotos, listas de desejos e registros de acesso às telas e aos papéis que precisam desses dados.
- [x] Usar avisos genéricos por e-mail e push, com link para o portal. Preservar as preferências de notificações do participante.

Em **Grupos → Editar → Participantes → Convite**, o criador pode gerar, substituir ou revogar um convite. O link, o QR e o código são exibidos na emissão e valem por 30 dias. Entregue-os diretamente ao participante. Substituir ou revogar invalida as sessões vinculadas ao código anterior.

QRs do fluxo antigo passam a orientar a solicitar um novo convite. Códigos privados do PWA já emitidos ganham 30 dias de validade a partir da migração. O participante pode escolher se recebe avisos por e-mail. As inscrições de push ficam associadas à preferência por dispositivo.

### Teste manual no navegador

1. Como organizador, gere um convite em **Grupos → Editar → Participantes → Convite**. Em uma janela anônima, abra o link ou escaneie o QR, toque em **Entrar** e confira o nome do participante. Antes do sorteio, o portal deve mostrar o estado de espera. Após o sorteio, ele deve mostrar somente o par dessa pessoa e a sugestão de presente, quando cadastrada.
2. Clique em **Revogar convite** na sessão do organizador. Recarregue o portal na janela anônima e tente reutilizar o link. O resultado deve ficar indisponível e o portal deve orientar a solicitar um novo convite.
3. Em um grupo de teste com e-mail e push configurados, desative os avisos por e-mail de um participante, mantenha os de outro e realize o sorteio. Confira a preferência e verifique se os avisos entregues apontam para **Meu Amigo Secreto** com texto genérico.

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
