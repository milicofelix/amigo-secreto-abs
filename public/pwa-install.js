(() => {
    const panel = document.getElementById('pwa-install');
    if (!panel || !window.isSecureContext) {
        return;
    }

    const installButton = document.getElementById('pwa-install-button');
    const iphoneInstructions = document.getElementById('pwa-install-iphone');
    const standalone = window.matchMedia('(display-mode: standalone)');
    const isAppleMobile = /iPhone|iPad|iPod/.test(navigator.userAgent)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    let installPrompt = null;
    let installed = false;

    function updateControls() {
        const isStandalone = standalone.matches || navigator.standalone === true;
        const canPrompt = !installed && !isStandalone && installPrompt !== null;
        const showInstructions = !installed && !isStandalone && isAppleMobile && !canPrompt;

        installButton.hidden = !canPrompt;
        iphoneInstructions.hidden = !showInstructions;
        panel.hidden = !canPrompt && !showInstructions;
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        installPrompt = event;
        updateControls();
    });

    installButton.addEventListener('click', async () => {
        if (!installPrompt) {
            return;
        }

        const prompt = installPrompt;
        installPrompt = null;
        updateControls();

        try {
            await prompt.prompt();
            const { outcome } = await prompt.userChoice;
            if (outcome === 'accepted') {
                installed = true;
                updateControls();
            }
        } catch (error) {
            console.warn('Não foi possível abrir a instalação do app.', error);
        }
    });

    window.addEventListener('appinstalled', () => {
        installed = true;
        installPrompt = null;
        updateControls();
    });

    standalone.addEventListener('change', updateControls);
    updateControls();
})();
