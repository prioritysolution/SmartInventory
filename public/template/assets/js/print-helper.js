/**
 * Print HTML without leaving an about:blank browser tab.
 * Uses a hidden iframe, then removes it after the print dialog closes.
 */
(function (window, document) {
    function removeFrame(iframe) {
        if (iframe && iframe.parentNode) {
            iframe.parentNode.removeChild(iframe);
        }
    }

    window.siPrint = function (html) {
        if (!html) {
            return;
        }

        var old = document.getElementById('si-print-frame');
        if (old) {
            removeFrame(old);
        }

        var iframe = document.createElement('iframe');
        iframe.id = 'si-print-frame';
        iframe.setAttribute('aria-hidden', 'true');
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden;';
        document.body.appendChild(iframe);

        var win = iframe.contentWindow;
        var doc = win.document;
        var closed = false;

        var finish = function () {
            if (closed) {
                return;
            }
            closed = true;
            setTimeout(function () {
                removeFrame(iframe);
            }, 250);
        };

        doc.open();
        doc.write(html);
        doc.close();

        setTimeout(function () {
            try {
                if (win.addEventListener) {
                    win.addEventListener('afterprint', finish);
                }
                win.focus();
                win.print();
                finish();
            } catch (e) {
                finish();
            }
        }, 150);
    };
})(window, document);
