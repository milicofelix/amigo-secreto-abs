# Planejamento do PWA — Amigo Secreto ABS

A primeira versão deve poder ser instalada e, ao abrir pelo ícone, levar à seleção de grupos do ano atual. Sem conexão, o app apresenta uma tela offline. Consultar participantes, revelar resultados, criar grupos e realizar sorteios exige conexão.

## Fase 1 — Identidade e abertura

- [ ] Criar o manifesto com nome, cores, ícones e URL de entrada.
- [ ] Integrar o manifesto ao layout do Amigo Secreto.
- [ ] Verificar que a URL de entrada abre a seleção de grupos do ano atual.

Commit sugerido — `git c "feat: adiciona manifesto e ícones do PWA"`

## Fase 2 — Modo offline

- [ ] Registrar o service worker e criar uma tela de fallback offline.
- [ ] Limitar o cache aos arquivos públicos necessários para essa tela, como ícones e estilos. Páginas de grupos, participantes, resultados e links de revelação devem ser buscados pela rede.
- [ ] Verificar o comportamento ao perder e recuperar a conexão e ao atualizar a versão do cache.

Commit sugerido — `git c "feat: adiciona tela offline e cache de arquivos públicos"`

## Fase 3 — Instalação

- [ ] Exibir a opção **Instalar app** quando o navegador oferecer essa ação ao site.
- [ ] Incluir uma orientação curta para adicionar o app à tela inicial no iPhone.
- [ ] Conferir que o app abre pelo ícone depois de instalado.

Commit sugerido — `git c "feat: adiciona opção de instalação do PWA"`

## Fase 4 — Validação

- [ ] Testar instalação, abertura pelo ícone e modo offline em Android e iPhone.
- [ ] Validar o retorno da conexão e a atualização do cache.
- [ ] Confirmar que o filtro de ano continua mostrando os grupos históricos sob demanda e que o modal aparece uma única vez após um sorteio bem-sucedido.
- [ ] Conferir que o cache do PWA não contém dados de participantes, resultados nem links de revelação.

Commit sugerido — `git c "test: valida instalação e fluxos principais do PWA"`

## Versões futuras

Notificações push e sincronização offline ficam para uma etapa posterior.
