(() => {
    const panel = document.getElementById('pwa-participant');
    function showInviteFromLink() {
        const fragment = new URLSearchParams(location.hash.slice(1));
        const entry = document.getElementById('participant-entry');
        if (!entry || !fragment.has('convite')) return false;

        history.replaceState(history.state, '', location.pathname + location.search);
        entry.hidden = false;
        const session = document.getElementById('participant-session');
        if (session) session.hidden = true;

        const input = document.getElementById('participant-code');
        const code = fragment.get('convite');
        if (input && /^(?:[A-Za-z0-9]{24}|[A-Za-z0-9]{48})$/.test(code)) {
            input.value = code;
        }
        return true;
    }

    window.addEventListener('hashchange', showInviteFromLink);
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) location.reload();
    });
    if (showInviteFromLink() || !panel) return;

    const { participant, drawId, csrf, inviteScope, statusUrl, pushUrl, pushKey } = panel.dataset;
    const request = (url, method, data) => fetch(url, {
        method,
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify(data),
    });
    const postStatus = (field) => request(statusUrl, 'POST', { participant_id: participant, draw_id: drawId, field, value: 1 });
    const status = document.getElementById('pwa-sync-status');

    const shared = panel.querySelector('[data-gift-status]');
    shared?.addEventListener('click', async () => {
        shared.disabled = true;
        try {
            const response = await postStatus('shared_gift_idea');
            if (!response.ok) throw new Error();
            shared.textContent = 'Ideia de presente compartilhada';
            status.textContent = 'Ideia marcada como compartilhada.';
        } catch {
            shared.disabled = false;
            status.textContent = 'Não foi possível confirmar a ação. Confira a conexão e seu acesso.';
        }
    });

    const gift = document.getElementById('pwa-gift');
    if (gift) {
        const retry = document.getElementById('pwa-sync-retry');
        const login = document.getElementById('pwa-sync-login');
        const retention = 7 * 24 * 60 * 60 * 1000;
        let syncing = false;
        let recorded = gift.dataset.recorded === 'yes';

        function database() {
            return new Promise((resolve, reject) => {
                const open = indexedDB.open('amigo-secreto-sync', 1);
                open.onupgradeneeded = () => open.result.createObjectStore('gift-pending');
                open.onsuccess = () => resolve(open.result);
                open.onerror = () => reject(open.error);
                open.onblocked = () => reject(new Error('Fila local ocupada'));
            });
        }

        async function queue(mode, operation) {
            const db = await database();
            try {
                return await new Promise((resolve, reject) => {
                    const transaction = db.transaction('gift-pending', mode);
                    const action = operation(transaction.objectStore('gift-pending'));
                    transaction.oncomplete = () => resolve(action.result);
                    transaction.onabort = () => reject(transaction.error);
                });
            } finally {
                db.close();
            }
        }

        const put = (item) => queue('readwrite', (store) => store.put(item, participant));
        const clear = () => queue('readwrite', (store) => store.delete(participant));

        async function sync(manual = false) {
            if (syncing) return;
            syncing = true;
            retry.hidden = true;
            login.hidden = true;
            gift.disabled = true;
            try {
                const pending = await queue('readonly', (store) => store.get(participant));
                if (!pending) {
                    gift.disabled = recorded;
                    return;
                }
                if (recorded) {
                    await clear();
                    status.textContent = 'Sincronizado.';
                    return;
                }
                if (pending.inviteScope !== inviteScope || pending.drawId !== drawId) {
                    await clear();
                    gift.disabled = false;
                    status.textContent = 'O acesso ou sorteio mudou. Marque o presente novamente se ainda for necessário.';
                    return;
                }
                if (Date.now() - pending.createdAt > retention) {
                    await clear();
                    gift.disabled = false;
                    status.textContent = 'A ação pendente expirou. Marque novamente se ainda for necessário.';
                    return;
                }
                if (pending.blocked && !manual) {
                    status.textContent = 'Precisa de atenção. Confira a situação antes de tentar novamente.';
                    retry.hidden = false;
                    return;
                }

                status.textContent = 'Pendente de sincronização.';
                let response;
                try {
                    response = await postStatus('bought_gift');
                } catch {
                    retry.hidden = false;
                    return;
                }

                if (response.ok) {
                    recorded = true;
                    gift.textContent = 'Presente marcado como comprado';
                    await clear();
                    status.textContent = 'Sincronizado.';
                } else if ([401, 419].includes(response.status)) {
                    status.textContent = 'Verifique seu acesso para sincronizar a ação pendente.';
                    login.hidden = false;
                } else if (response.status === 429 || response.status >= 500) {
                    retry.hidden = false;
                } else {
                    await put({ ...pending, blocked: true });
                    retry.hidden = false;
                    status.textContent = 'Precisa de atenção. O servidor recusou a atualização.';
                }
            } catch {
                gift.disabled = recorded;
                status.textContent = 'O armazenamento offline está indisponível neste navegador.';
            } finally {
                syncing = false;
            }
        }

        gift.addEventListener('click', async () => {
            gift.disabled = true;
            try {
                await put({ createdAt: Date.now(), inviteScope, drawId });
                await sync();
            } catch {
                gift.disabled = false;
                status.textContent = 'Não foi possível guardar a ação neste dispositivo.';
            }
        });
        retry.addEventListener('click', () => sync(true));
        window.addEventListener('online', () => sync());
        sync();
    }

    const toggle = document.getElementById('pwa-push-toggle');
    if (!toggle || !pushKey) return;
    const pushStatus = document.getElementById('pwa-push-status');
    if (!window.isSecureContext || !('Notification' in window)
        || !('serviceWorker' in navigator) || !('PushManager' in window)) {
        pushStatus.textContent = 'Notificações indisponíveis neste navegador. No iPhone, abra pelo ícone da Tela de Início.';
        return;
    }

    let registration;
    let subscription;

    function showPushState() {
        toggle.textContent = subscription ? 'Desativar notificações' : 'Ativar notificações';
        toggle.disabled = !subscription && Notification.permission === 'denied';
    }

    navigator.serviceWorker.ready.then(async (ready) => {
        registration = ready;
        subscription = await ready.pushManager.getSubscription();
        showPushState();
        if (Notification.permission === 'denied') {
            pushStatus.textContent = 'Permissão negada. Você pode alterá-la nas configurações do navegador.';
        }
    }).catch(() => {
        pushStatus.textContent = 'Não foi possível preparar as notificações neste dispositivo.';
    });

    toggle.addEventListener('click', async () => {
        toggle.disabled = true;
        try {
            if (subscription) {
                const response = await request(pushUrl, 'DELETE', { endpoint: subscription.endpoint });
                if (!response.ok) throw new Error();
                await subscription.unsubscribe();
                subscription = null;
                pushStatus.textContent = 'Notificações desativadas neste dispositivo.';
            } else if (await Notification.requestPermission() === 'granted') {
                const created = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: pushKey,
                });
                try {
                    const response = await request(pushUrl, 'POST', created.toJSON());
                    if (!response.ok) throw new Error();
                    subscription = created;
                    pushStatus.textContent = 'Notificações ativadas neste dispositivo.';
                } catch (error) {
                    await created.unsubscribe();
                    throw error;
                }
            } else {
                pushStatus.textContent = 'A permissão de notificações não foi concedida.';
            }
        } catch {
            pushStatus.textContent = 'Não foi possível atualizar as notificações. Confira a conexão e seu acesso.';
        } finally {
            showPushState();
        }
    });
})();
