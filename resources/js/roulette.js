import { landingOffset, landingRotation } from './roulette-math';

const SPIN_MS = 5200;
const CONFETTI_COLORS = ['#FFD23E', '#3EF0FF', '#FF5C8A', '#5CFF9D', '#8B7CFF', '#FF8A3D'];

document.querySelectorAll('[data-roulette]').forEach((root) => {
    const wheel = root.querySelector('[data-roulette-wheel]');
    const segments = [...root.querySelectorAll('[data-suggestion-id]')];
    const button = root.querySelector('[data-roulette-spin]');
    const result = root.querySelector('[data-roulette-result]');
    const error = root.querySelector('[data-roulette-error]');
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    let rotation = 0;

    if (!button || segments.length === 0) {
        return;
    }

    button.addEventListener('click', async () => {
        button.disabled = true;
        button.textContent = 'Spinning…';
        error.hidden = true;
        result.hidden = true;
        wheel.removeAttribute('data-won');
        segments.forEach((segment) => segment.removeAttribute('data-winner'));

        try {
            const winner = await fetchWinner(root.dataset.roulette);
            const index = segments.findIndex((segment) => Number(segment.dataset.suggestionId) === winner.suggestion_id);

            if (index === -1) {
                throw new Error('The suggestions changed. Reload the page and spin again.');
            }

            rotation = landingRotation({ current: rotation, index, count: segments.length, offset: landingOffset(segments.length) });
            await spinTo(wheel, rotation, reducedMotion.matches ? 900 : SPIN_MS);

            wheel.dataset.won = '';
            segments[index].dataset.winner = '';
            showWinner(result, winner);

            if (!reducedMotion.matches) {
                celebrate(root);
            }
        } catch (exception) {
            error.textContent = exception.message;
            error.hidden = false;
        } finally {
            button.disabled = false;
            button.textContent = 'Spin again';
        }
    });
});

async function fetchWinner(url) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    });
    const body = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(body.message ?? 'The wheel jammed. Please try again.');
    }

    return body;
}

function spinTo(wheel, degrees, duration) {
    return new Promise((resolve) => {
        // Fast start, long smooth slowdown.
        wheel.style.transition = `transform ${duration}ms cubic-bezier(0.12, 0.72, 0.16, 1)`;
        wheel.style.transform = `rotate(${degrees}deg)`;

        // Segments fade in/out too and their transitionend bubbles up; only the wheel's own spin counts.
        wheel.addEventListener('transitionend', function done(event) {
            if (event.target === wheel && event.propertyName === 'transform') {
                wheel.removeEventListener('transitionend', done);
                resolve();
            }
        });
    });
}

function showWinner(result, winner) {
    const image = result.querySelector('[data-winner-cover]');
    const placeholder = result.querySelector('[data-winner-placeholder]');

    result.querySelector('[data-winner-name]').textContent = winner.name;
    result.querySelector('[data-winner-link]').href = `/games/${winner.igdb_id}`;
    image.hidden = !winner.cover_url;
    placeholder.hidden = Boolean(winner.cover_url);
    placeholder.textContent = winner.name;

    if (winner.cover_url) {
        image.src = winner.cover_url;
        image.alt = `Cover of ${winner.name}`;
    }

    result.hidden = false;
}

function celebrate(root) {
    const layer = document.createElement('div');
    layer.className = 'confetti';
    layer.setAttribute('aria-hidden', 'true');

    for (let i = 0; i < 90; i++) {
        const piece = document.createElement('span');
        piece.style.setProperty('--x', `${Math.random() * 100}%`);
        piece.style.setProperty('--drift', `${(Math.random() - 0.5) * 160}px`);
        piece.style.setProperty('--spin', `${(Math.random() - 0.5) * 1080}deg`);
        piece.style.setProperty('--delay', `${Math.random() * 250}ms`);
        piece.style.setProperty('--duration', `${1400 + Math.random() * 1200}ms`);
        piece.style.background = CONFETTI_COLORS[i % CONFETTI_COLORS.length];
        layer.append(piece);
    }

    root.closest('dialog')?.append(layer) ?? root.append(layer);
    setTimeout(() => layer.remove(), 3200);
}
