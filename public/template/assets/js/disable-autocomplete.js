(function () {
    function disableAutocomplete(root) {
        if (root.matches && root.matches('input')) {
            root.setAttribute('autocomplete', 'off');
        }

        if (root.querySelectorAll) {
            root.querySelectorAll('input').forEach(function (input) {
                input.setAttribute('autocomplete', 'off');
            });
        }

        if (root.matches && root.matches('form')) {
            root.setAttribute('autocomplete', 'off');
        }

        if (root.querySelectorAll) {
            root.querySelectorAll('form').forEach(function (form) {
                form.setAttribute('autocomplete', 'off');
            });
        }
    }

    disableAutocomplete(document);

    new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    disableAutocomplete(node);
                }
            });
        });
    }).observe(document.documentElement, { childList: true, subtree: true });
})();
