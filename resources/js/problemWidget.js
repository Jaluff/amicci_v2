import $ from 'jquery';

/**
 * DocumentProblem UI Widget
 *
 * Maneja el widget de "Problema" en cualquier vista de documento.
 * Uso en Blade:
 *   <div id="problem-widget"
 *        data-model-type="dispatch"
 *        data-model-id="{{ $dispatch->id }}"
 *        data-has-active="{{ $dispatch->hasActiveProblem() ? 'true' : 'false' }}">
 *   </div>
 */
const ProblemWidget = (function ($) {

    const PROBLEM_URL = '/documents/problem';

    function init() {
        const $widget = $('#problem-widget');
        if (!$widget.length) return;

        // Enviar nuevo registro de problema
        $(document).on('submit', '#problem-form', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('[type="submit"]');
            const isActive = $form.find('[name="is_active"]').val() === '1';
            const comment = $form.find('[name="comment"]').val().trim();

            if (!comment) {
                toastr.warning('Ingresá un comentario antes de guardar.');
                return;
            }

            $btn.prop('disabled', true).text('Guardando...');

            $.ajax({
                url: PROBLEM_URL,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    model_type: $widget.data('model-type'),
                    model_id: $widget.data('model-id'),
                    is_active: isActive ? 1 : 0,
                    comment: comment,
                },
                success: function (res) {
                    toastr.success(res.message);
                    // Recargar para reflejar el nuevo estado del problema
                    setTimeout(() => window.location.reload(), 800);
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message
                        || xhr.responseJSON?.errors?.comment?.[0]
                        || 'Error al guardar el problema.';
                    toastr.error(msg);
                    $btn.prop('disabled', false).text('Guardar');
                },
            });
        });
    }

    return { init };

})($);

$(document).ready(function () {
    ProblemWidget.init();
});

window.ProblemWidget = ProblemWidget;
