# Planejamento do PWA — Amigo Secreto ABS

A primeira versão deve poder ser instalada e, ao abrir pelo ícone, levar à seleção de grupos do ano atual. Sem conexão, o app apresenta uma tela offline. Consultar participantes, revelar resultados, criar grupos e realizar sorteios exige conexão.

Toda entrega deve incluir um roteiro de teste manual no navegador, com os passos e o resultado esperado. No computador, use `http://localhost:8085` com o Docker rodando. Para testar em um celular, use a URL HTTPS de um ambiente de teste acessível pelo aparelho.

## Fase 1 — Identidade e abertura

- [x] Criar o manifesto com nome, cores, ícones e URL de entrada.
- [x] Integrar o manifesto ao layout do Amigo Secreto.
- [x] Verificar que a URL de entrada abre a seleção de grupos do ano atual.

### Teste manual no navegador

1. Abra `http://localhost:8085/`. A página deve redirecionar para `/dashboard/select-group?year=<ano-atual>`, com o ano atual selecionado.
2. No Chrome ou Edge, abra o DevTools e acesse **Application → Manifest**. Confira o nome **Amigo Secreto ABS**, as cores e os ícones de 192 e 512 px.
3. Abra `http://localhost:8085/images/pwa-icon-180.png` em outra aba. O ícone da tela inicial do iPhone deve carregar.

Commit sugerido — `git c "feat: adiciona manifesto e ícones do PWA"`

## Fase 2 — Modo offline

- [x] Registrar o service worker e criar uma tela de fallback offline.
- [x] Limitar o cache à tela offline e ao ícone que ela usa. Páginas de grupos, participantes, resultados e links de revelação são buscados pela rede.
- [x] Verificar em teste automatizado a perda e a recuperação da conexão, o isolamento do cache e a remoção da versão anterior.

### Teste manual no navegador

1. Abra `http://localhost:8085/` com conexão. No Chrome ou Edge, abra **DevTools → Application → Service Workers** e espere o worker ficar ativo. Deixe **Bypass for network** desmarcado.
2. Visite uma página de grupo. Marque **Offline** no painel do service worker e recarregue normalmente. A tela **Você está sem conexão** deve aparecer com o logo e o botão **Tentar novamente**.
3. Desmarque **Offline** e clique em **Tentar novamente**. A mesma página do grupo deve carregar.
4. Em **Application → Cache Storage → amigo-secreto-offline-v1**, confira que constam apenas `/offline.html` e `/images/pwa-icon-192.png`.
5. Quando houver alterações nesses arquivos, incremente a versão do cache em `public/service-worker.js`. Após carregar a nova versão, confira no DevTools que o cache anterior do Amigo Secreto foi removido.

Commit sugerido — `git c "feat: adiciona tela offline e cache de arquivos públicos"`

## Fase 3 — Instalação

- [x] Exibir **Instalar app** na seleção de grupos quando o navegador disponibilizar o instalador.
- [x] Incluir instruções para adicionar o app à tela inicial do iPhone ou iPad.
- [ ] Confirmar em um dispositivo que o app abre pelo ícone após a instalação.

### Teste manual no navegador

1. Em uma janela normal do Chrome ou Edge, abra `http://localhost:8085/` com o app ainda por instalar. Na seleção de grupos, clique em **Instalar app** quando aparecer. O navegador deve abrir o diálogo nativo. Aceite a instalação. No Android, use uma URL HTTPS de teste no Chrome.
2. No iPhone ou iPad, abra uma URL HTTPS de teste no Safari e expanda **Instalar no iPhone ou iPad**. Use **Compartilhar → Adicionar à Tela de Início**, ative **Abrir como App da Web** se aparecer e toque em **Adicionar**.
3. Abra pelo novo ícone em cada dispositivo. O app deve abrir na seleção de grupos do ano atual, sem oferecer a instalação novamente dentro do app.

Commit sugerido — `git c "feat: adiciona opção de instalação do PWA"`

## Fase 4 — Validação

- [ ] Testar instalação, abertura pelo ícone e modo offline em Android e iPhone.
- [ ] Validar o retorno da conexão e a atualização do cache.
- [ ] Confirmar que o filtro de ano continua mostrando os grupos históricos sob demanda e que o modal aparece uma única vez após um sorteio bem-sucedido.
- [ ] Conferir que o cache do PWA não contém dados de participantes, resultados nem links de revelação.

### Teste manual no navegador

1. No Android e no iPhone, instale e abra pelo ícone. Desconecte o aparelho e abra novamente para conferir a tela offline. Restabeleça a conexão e confira que a seleção de grupos volta a carregar.
2. Escolha um ano anterior no filtro e confira a listagem. Em um grupo de teste, faça um sorteio e confirme que o modal aparece após o sucesso. Recarregue o resultado e confira que ele não se repete.
3. No Chrome ou Edge, após atualizar o service worker, confira a versão ativa e verifique em **Cache Storage** que a versão anterior foi removida e que páginas de participantes, resultados e revelações estão ausentes.

Commit sugerido — `git c "test: valida instalação e fluxos principais do PWA"`

## Versões futuras

Notificações push e sincronização offline ficam para uma etapa posterior.
