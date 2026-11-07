(function () {
    'use strict';

    const style = document.createElement('style');
    style.textContent = `
        .required-field-blink {
            animation: required-field-blink 0.6s ease-in-out 6;
        }

        @keyframes required-field-blink {
            50% {
                outline: 3px solid #dc3545;
                outline-offset: 2px;
                background-color: #fff3f3;
            }
        }
    `;
    document.head.appendChild(style);

    let nextFieldSelector = null;

    function isVisible(element) {
        return element.getClientRects().length > 0 &&
            window.getComputedStyle(element).visibility !== 'hidden';
    }

    function fieldLabel(element) {
        const labels = Array.from(document.querySelectorAll('label')).filter(label =>
            label.htmlFor === element.id || label.contains(element)
        );
        const container = element.closest('.mb-3, .form-group, .form-floating, .input-group, .form-check');
        if (container) {
            labels.push(...container.querySelectorAll('label'));
        }
        return [...new Set(labels)].map(label => label.textContent || '').join(' ');
    }

    function isRequiredField(element) {
        return element.required ||
            element.getAttribute('aria-required') === 'true' ||
            /\*/.test(fieldLabel(element));
    }

    function isEmpty(element) {
        if (element.type === 'checkbox' || element.type === 'radio') {
            return !element.checked;
        }
        return !String(element.value || '').trim();
    }

    function visibleFocusTarget(element) {
        if (element.matches('select.select2-hidden-accessible')) {
            return element.nextElementSibling?.querySelector('.select2-selection') || element;
        }
        return element;
    }

    function focusAndBlink(element) {
        if (!element) return;

        const target = visibleFocusTarget(element);
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        window.requestAnimationFrame(() => {
            target.focus({ preventScroll: true });

            if (
                element instanceof HTMLInputElement &&
                /^(text|search|tel|url|password)$/i.test(element.type) &&
                element.value.length === 0
            ) {
                element.setSelectionRange(0, 0);
            }

            target.classList.remove('required-field-blink');
            void target.offsetWidth;
            target.classList.add('required-field-blink');
            window.setTimeout(() => target.classList.remove('required-field-blink'), 3600);
        });
    }

    function findBlankRequiredField(message) {
        const fields = Array.from(document.querySelectorAll('input, select, textarea'))
            .filter(element =>
                !element.disabled &&
                !element.readOnly &&
                element.type !== 'hidden' &&
                isRequiredField(element) &&
                isEmpty(element) &&
                isVisible(visibleFocusTarget(element))
            );

        if (!fields.length) return null;

        const messageWords = new Set((message || '').toLowerCase().match(/[a-z0-9]+/g) || []);
        let bestField = null;
        let bestScore = 0;

        fields.forEach(field => {
            const labelWords = fieldLabel(field).toLowerCase().match(/[a-z0-9]+/g) || [];
            const score = labelWords.reduce((total, word) =>
                total + (word.length > 2 && messageWords.has(word) ? 1 : 0), 0
            );
            if (score > bestScore) {
                bestField = field;
                bestScore = score;
            }
        });

        return bestField || fields[0];
    }

    function getAlertText(args) {
        const options = args[0];
        if (options && typeof options === 'object') {
            return {
                title: options.title || '',
                message: [options.text, options.html].filter(Boolean).join(' '),
                icon: options.icon || options.type || ''
            };
        }
        return {
            title: typeof options === 'string' ? options : '',
            message: typeof args[1] === 'string' ? args[1] : '',
            icon: typeof args[2] === 'string' ? args[2] : ''
        };
    }

    function isMissingFieldAlert(alert) {
        const text = `${alert.title} ${alert.message}`;
        return (alert.icon === 'error' || /\berror\b/i.test(alert.title)) &&
            /\b(required|select|enter|choose|blank|empty|provided)\b/i.test(text);
    }

    document.addEventListener('invalid', event => {
        const element = event.target;
        if (element instanceof HTMLElement) {
            window.setTimeout(() => focusAndBlink(element), 0);
        }
    }, true);

    window.SIRequiredFieldFeedback = {
        focus: selector => focusAndBlink(document.querySelector(selector)),
        setNextField: selector => {
            nextFieldSelector = selector;
        }
    };

    if (window.Swal && typeof window.Swal.fire === 'function' && !window.Swal.fire.__requiredFieldFeedback) {
        const originalFire = window.Swal.fire.bind(window.Swal);
        const wrappedFire = function (...args) {
            const alert = getAlertText(args);
            const explicitSelector = nextFieldSelector;
            nextFieldSelector = null;
            const result = originalFire(...args);

            if (explicitSelector || isMissingFieldAlert(alert)) {
                return result.then(response => {
                    if (explicitSelector) {
                        focusAndBlink(document.querySelector(explicitSelector));
                    } else if (isMissingFieldAlert(alert)) {
                        focusAndBlink(findBlankRequiredField(`${alert.title} ${alert.message}`));
                    }
                    return response;
                });
            }
            return result;
        };
        wrappedFire.__requiredFieldFeedback = true;
        window.Swal.fire = wrappedFire;
    }
})();
