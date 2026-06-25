/**
 * Login Page
 */

import { login } from '../auth.js';
import { render } from '../ui.js';
import { navigate } from '../router.js';

export function renderLoginPage() {
    render(`
        <div class="login-page">
            <div class="login-logo">🚚</div>
            <h1 class="login-title">Amicci Repartos</h1>
            <p class="login-subtitle">Ingresá con tu cuenta de repartidor</p>

            <form class="login-form" id="login-form">
                <div id="login-error" class="form-error hidden"></div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        class="form-input"
                        placeholder="tu@email.com"
                        autocomplete="email"
                        inputmode="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        class="form-input"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="login-btn">
                    Iniciar Sesión
                </button>
            </form>
        </div>
    `);

    // Bind form submit
    document.getElementById('login-form').addEventListener('submit', handleLogin);
}

async function handleLogin(e) {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const btn = document.getElementById('login-btn');
    const errorEl = document.getElementById('login-error');

    if (!email || !password) return;

    // Disable button and show loading
    btn.disabled = true;
    btn.textContent = 'Ingresando...';
    errorEl.classList.add('hidden');

    const result = await login(email, password);

    if (result.success) {
        navigate('/deliveries');
    } else {
        errorEl.textContent = result.error;
        errorEl.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Iniciar Sesión';
    }
}
