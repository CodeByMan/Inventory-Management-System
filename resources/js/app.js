document.documentElement.classList.add('js-enabled');

document.querySelectorAll('[data-landing-menu-toggle]').forEach((button) => {
    const menuId = button.getAttribute('aria-controls');
    const menu = menuId ? document.getElementById(menuId) : null;

    if (!menu) {
        return;
    }

    button.addEventListener('click', () => {
        const isOpen = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!isOpen));
        menu.classList.toggle('is-open', !isOpen);
    });

    menu.querySelectorAll('a, button').forEach((item) => {
        item.addEventListener('click', () => {
            button.setAttribute('aria-expanded', 'false');
            menu.classList.remove('is-open');
        });
    });
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    const inputId = button.getAttribute('aria-controls');
    const input = inputId ? document.getElementById(inputId) : null;
    const text = button.querySelector('[data-password-toggle-text]');

    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    button.addEventListener('click', () => {
        const shouldShow = input.type === 'password';
        input.type = shouldShow ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(shouldShow));
        button.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
        button.classList.toggle('is-visible', shouldShow);

        if (text) {
            text.textContent = shouldShow ? 'Hide password' : 'Show password';
        }

        input.focus({ preventScroll: true });
        input.setSelectionRange(input.value.length, input.value.length);
    });
});

document.querySelectorAll('form[data-confirm-message]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const message = form.getAttribute('data-confirm-message') || 'Continue with this action?';

        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });
});
