# Roadmap do Amigo Secreto ABS

Priorizar login, isolamento dos grupos e privacidade do sorteio. O organizador vê e gerencia exclusivamente os grupos que criou. O Super Admin tem uma visão global, incluindo grupos antigos sem responsável identificado. O participante continua com acesso individual pelo convite.

## Permissões por papel

| Ação | Organizador | Super Admin | Participante |
| --- | --- | --- | --- |
| Listar e consultar grupos | Somente os que criou | Todos os grupos e anos | Dashboard, participantes e andamento do próprio grupo |
| Criar grupo | Sim, vinculado à própria conta | Sim, vinculado à própria conta | — |
| Alterar grupo, participantes e restrições | Somente nos grupos que criou e conforme o estado do sorteio | Nos grupos que criou. A gestão de grupos alheios exige uma permissão explícita em fase futura | Atualizar os próprios dados permitidos no portal |
| Importar participantes | Entre grupos que criou, antes do sorteio | Entre grupos que criou, antes do sorteio | — |
| Realizar sorteio | Somente nos grupos que criou | Somente nos grupos que criou | — |
| Consultar pares | Revelação coletiva na data e hora definidas, nos grupos que criou | A visão global mostra o andamento. Acesso a pares de terceiros exige uma regra explícita | Próprio resultado após o sorteio e pares do grupo após a revelação coletiva |

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

Em **Grupos → Editar → Participantes → Convite**, o criador pode gerar, consultar, substituir ou revogar um convite. O link, o QR e o código dos convites novos ficam disponíveis nessa tela durante a validade de 30 dias. Entregue-os diretamente ao participante. Substituir ou revogar invalida as sessões vinculadas ao código anterior.

Em **Meu Amigo Secreto → Perdi meu convite**, o participante pode recuperar pelo e-mail que o organizador cadastrou. O link de recuperação vale 15 minutos e pode ser usado uma vez. O convite vencido pode ser renovado por esse fluxo, desde que ainda não tenha sido revogado. Para reenviar o código de um convite antigo que só guardava o hash, gere um novo pelo painel. A entrega do e-mail usa o transporte e a fila configurados na instalação.

QRs do fluxo antigo passam a orientar a solicitar um novo convite. Códigos privados do PWA já emitidos ganham 30 dias de validade a partir da migração. O participante pode escolher se recebe avisos por e-mail. As inscrições de push ficam associadas à preferência por dispositivo.

### Teste manual no navegador

1. Como organizador, gere um convite em **Grupos → Editar → Participantes → Convite**. Em uma janela anônima, abra o link ou escaneie o QR, toque em **Entrar** e confira o nome do participante. Antes do sorteio, o portal deve mostrar o estado de espera. Após o sorteio, ele deve mostrar somente o par dessa pessoa e a sugestão de presente, quando cadastrada.
2. Reabra **Convite** como organizador e confira o mesmo link. Em outra sessão, use **Perdi meu convite** com o e-mail cadastrado e confirme o acesso pelo link recebido. Navegue entre o dashboard, o sorteio e o portal individual. As ações de gestão ficam com o organizador, e os pares completos só aparecem após o horário de revelação.
3. Clique em **Revogar convite** na sessão do organizador. Recarregue o portal na janela anônima e tente reutilizar o link. O resultado deve ficar indisponível e o portal deve orientar a solicitar um novo convite.
4. Em um grupo de teste com e-mail e push configurados, desative os avisos por e-mail de um participante, mantenha os de outro e realize o sorteio. Confira a preferência e verifique se os avisos entregues apontam para **Meu Amigo Secreto** com texto genérico.

Commit sugerido — `git c "feat: unifica a revelação com convites privados"`

## Fase 4 Regras do sorteio e histórico

- [x] Validar se existe uma combinação possível com as restrições atuais antes de confirmar o sorteio. Explicar quando a configuração impede a formação dos pares.
- [x] Impedir alterações de participantes e restrições depois de confirmar. Garantir no banco um destinatário por participante e um participante por destinatário em cada sorteio.
- [x] Definir a data e hora da revelação coletiva. Enviar notificações após a confirmação no banco e registrar os eventos de sorteio, cancelamento autorizado e notificações.

Em **Grupos → Gerenciar → Revelação coletiva**, agende a data e hora no horário de Brasília. Sem horário explícito, vale a meia-noite da data do evento. Se o grupo também estiver sem data do evento, a revelação coletiva fica bloqueada até o agendamento. O resultado individual fica disponível pelo convite após confirmar o sorteio.

O criador pode cancelar um sorteio ativo antes da revelação coletiva, com motivo obrigatório. O cancelamento retira o resultado do portal, reinicia os estados de presente e libera a lista para ajustes e um novo sorteio. O histórico fica no painel do grupo e registra confirmações, cancelamentos e o processamento dos avisos. Grupos com histórico ficam protegidos contra exclusão.

### Teste manual no navegador

1. Em um grupo fictício com três participantes, configure Ana para não tirar Bia e Bia para não tirar Ana. As duas só poderão tirar a terceira pessoa. Clique em **Sortear Amigo Secreto** e confirme que o painel informa o conflito.
2. Remova uma das restrições, agende a revelação coletiva para alguns minutos à frente e realize o sorteio. Confira o bloqueio da lista, a confirmação no histórico e a consulta individual pelo convite.
3. Abra **Ver Resultado do Sorteio** antes do horário e após o horário. Antes, a tela mostra o andamento. Depois, ela abre os pares do sorteio ativo.
4. Em outro grupo fictício, com a revelação ainda no futuro, cancele com um motivo e confira o histórico, o bloqueio do resultado anterior no portal e a possibilidade de ajustar a lista e sortear novamente.

Commit sugerido — `git c "feat: valida restrições e protege o histórico do sorteio"`

## Fase 5 Validação e preparação da publicação

- [x] Alinhar a suíte de autenticação e perfil ao fluxo adotado. Confirmar isolamento entre grupos, convites e ações de escrita.
- [x] Desativar Rifas e PIX por configuração na instalação dedicada ao Amigo Secreto, preservando o código e os dados. A opção `AMIGO_SECRETO_RIFAS_ENABLED` fica desativada por padrão.
- [x] Conferir no Chrome a importação do ano anterior, o upload de fotos, os botões **Voltar**, o cache público do PWA e a sincronização offline ao reabrir pela página inicial.
- [x] Documentar a [preparação da publicação](README.md#preparação-da-publicação), incluindo HTTPS, proxy, migrações e filas.
- [ ] Confirmar abertura pelo ícone e entrega real de push em Android e iPhone usando homologação HTTPS e o [planejamento do PWA](PLANEJAMENTO-PWA.md).

A validação local usou a suíte completa no Docker e um fluxo no Chrome com banco isolado e dados fictícios. O Docker retornou HTTP 200 para `/up`, o manifesto, o service worker e o portal. Rifas, PIX e a administração de Rifas retornaram HTTP 404.

### Teste manual no navegador

1. Entre como organizador, crie um grupo e importe participantes do ano anterior. Adicione um participante com foto JPEG e use **Voltar ao grupo** na edição, na importação e no convite.
2. Abra o convite em outra sessão, confirme o sorteio e confira se o portal mostra apenas o resultado individual. Com o portal aberto, desconecte, marque o presente como comprado e feche a janela. Reconecte e reabra pela página inicial. A mensagem **Sincronizado** deve aparecer. Em **Application → Cache Storage**, confira que o cache contém só `offline.html` e o ícone.
3. Em homologação HTTPS, instale em Chrome para Android e Safari no iPhone. Abra pelo ícone, entre com o convite de um grupo de teste ainda sem sorteio e ative as notificações. Coloque o app em segundo plano, realize o sorteio e confira a chegada do aviso, o texto genérico e a abertura de **Meu Amigo Secreto** ao tocar.

Commit sugerido — `git c "test: valida permissões e fluxos do amigo secreto"`
