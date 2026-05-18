export const partyAjaxConfig = {
    ajax: {
        url: '/parties/ajax-search',
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
                q: params.term
            };
        },
        processResults: function (data) {
            return {
                results: data.results
            };
        },
        cache: true
    }
};
