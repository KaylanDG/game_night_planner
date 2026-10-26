import './roulette';

// Native <dialog> modals: [data-dialog-open="id"] opens, [data-dialog-close] closes.
// A dialog with [data-open] (e.g. after a validation error) opens on load.
document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-dialog-open]');

    if (opener) {
        // Openers can be links to a no-JS fallback page; stay here when the dialog exists.
        const dialog = document.getElementById(opener.dataset.dialogOpen);

        if (dialog) {
            event.preventDefault();
            dialog.showModal();
        }
    }

    if (event.target.closest('[data-dialog-close]')) {
        event.target.closest('dialog')?.close();
    }
});

document.querySelectorAll('dialog[data-open]').forEach((dialog) => dialog.showModal());

// Links like /sessions#new-session open that dialog on arrival.
const hashTarget = location.hash && document.getElementById(location.hash.slice(1));

if (hashTarget instanceof HTMLDialogElement && !hashTarget.open) {
    hashTarget.showModal();
}

// Show/hide password toggles: [data-password-toggle] flips every [data-password] in its form.
document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const fields = button.closest('form').querySelectorAll('[data-password]');
        const show = button.getAttribute('aria-pressed') !== 'true';

        fields.forEach((field) => (field.type = show ? 'text' : 'password'));
        button.setAttribute('aria-pressed', String(show));
        button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        button.textContent = show ? 'Hide' : 'Show';
    });
});

// Live password checklist on the register form.
document.querySelectorAll('[data-password-rules]').forEach((list) => {
    const form = list.closest('form');
    const password = form.querySelector('[name="password"]');
    const confirmation = form.querySelector('[name="password_confirmation"]');
    const min = Number(list.dataset.passwordRules);

    const update = () => {
        const states = {
            length: password.value.length >= min ? 'ok' : password.value ? 'bad' : 'idle',
            match: !confirmation.value ? 'idle' : password.value === confirmation.value ? 'ok' : 'bad',
        };

        list.querySelectorAll('[data-rule]').forEach((item) => {
            item.dataset.state = states[item.dataset.rule];
            item.querySelector('[data-mark]').textContent = { ok: '✓', bad: '×', idle: '·' }[item.dataset.state];
            item.querySelector('[data-sr]').textContent = item.dataset.state === 'ok' ? ' (done)' : ' (not yet)';
        });
    };

    password.addEventListener('input', update);
    confirmation.addEventListener('input', update);
    update();
});
