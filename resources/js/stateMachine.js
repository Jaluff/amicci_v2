/**
 * StateMachine UI Component
 *
 * Escucha clics en cualquier elemento con [data-transition]:
 *   <button data-model-type="dispatch"
 *           data-model-id="{{ $dispatch->id }}"
 *           data-transition="En viaje"
 *           data-comment="Opcional">
 *       Marcar En viaje
 *   </button>
 *
 * Muestra un Toast con el resultado (éxito o error).
 * Al éxito, recarga la página para reflejar los cambios de estado en cascada.
 */

import $ from 'jquery';

const StateMachineUI = (function ($) {

    const TRANSITION_URL = window.stateMachineConfig?.transitionUrl || '/status/transition';

    // ── Toast (usa toastr que ya está cargado globalmente en app.js) ─────────

    function showToast(message, type = 'success') {
        if (window.toastr) {
            window.toastr[type === 'error' ? 'error' : 'success'](message);
        } else {
            alert(message);
        }
    }

    // ── Botón de transición ────────────────────────────────────────────────────

    function handleTransitionClick($btn) {
        const modelType = $btn.data('model-type');
        const modelId = $btn.data('model-id');
        const status = $btn.data('transition');
        const comment = $btn.data('comment') || null;

        if (!modelType || !modelId || !status) {
            console.error('[StateMachine] Faltan atributos data- en el botón:', $btn[0]);
            return;
        }

        // Deshabilitar botón mientras procesa
        $btn.prop('disabled', true).addClass('opacity-60 cursor-not-allowed');
        const originalText = $btn.html();
        $btn.html('<svg class="animate-spin w-4 h-4 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Procesando...');

        $.ajax({
            url: TRANSITION_URL,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                model_type: modelType,
                model_id: modelId,
                status: status,
                comment: comment,
            },
            success: function (response) {
                showToast(response.message || 'Estado actualizado.', 'success');
                // Recargar para reflejar los cambios en cascada
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                const data = xhr.responseJSON;
                const msg = data?.message || 'Ocurrió un error al cambiar el estado.';
                showToast(msg, 'error');

                // Restaurar botón
                $btn.prop('disabled', false).removeClass('opacity-60 cursor-not-allowed').html(originalText);
            }
        });
    }

    // ── Init ───────────────────────────────────────────────────────────────────

    function init() {
        // Delegación de eventos para elementos dinámicos
        $(document).on('click', '[data-transition]', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const modelType = $btn.data('model-type');
            const status = $btn.data('transition');

            // Confirmación si el elemento lo requiere
            const confirmMsg = $btn.data('confirm');
            if (confirmMsg && !confirm(confirmMsg)) return;

            handleTransitionClick($btn);
        });
    }

    return { init, showToast };

})($);

$(document).ready(function () {
    StateMachineUI.init();
});

// Exportar para uso en otros módulos JS
window.StateMachineUI = StateMachineUI;
