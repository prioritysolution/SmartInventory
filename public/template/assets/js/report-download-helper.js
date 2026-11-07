/**
 * Smart Inventory - Report ZIP Download & Print Limit Helper
 *
 * 1. Blocks printing when report rows exceed 1000 and alerts:
 *    "Your data is more than 1000 so go to download"
 * 2. Generates standard PKZIP archives containing formatted CSV & HTML
 *    for all searched records.
 */
(function (window, $) {
    'use strict';

    // CRC-32 Lookup Table
    const crcTable = (function () {
        let c;
        const table = new Uint32Array(256);
        for (let n = 0; n < 256; n++) {
            c = n;
            for (let k = 0; k < 8; k++) {
                c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1);
            }
            table[n] = c >>> 0;
        }
        return table;
    })();

    function crc32(bytes) {
        let crc = 0 ^ (-1);
        for (let i = 0; i < bytes.length; i++) {
            crc = (crc >>> 8) ^ crcTable[(crc ^ bytes[i]) & 0xFF];
        }
        return (crc ^ (-1)) >>> 0;
    }

    /**
     * Create a standard PKZIP archive byte array from a list of files.
     * @param {Array<{name: string, content: string|Uint8Array}>} files
     * @returns {Uint8Array}
     */
    function createZip(files) {
        const textEnc = new TextEncoder();
        const localParts = [];
        const centralParts = [];
        let offset = 0;

        // Current DOS date/time
        const now = new Date();
        const dosTime = ((now.getHours() & 0x1F) << 11) |
            ((now.getMinutes() & 0x3F) << 5) |
            ((Math.floor(now.getSeconds() / 2)) & 0x1F);
        const dosDate = (((now.getFullYear() - 1980) & 0x7F) << 9) |
            (((now.getMonth() + 1) & 0x0F) << 5) |
            (now.getDate() & 0x1F);

        files.forEach(function (file) {
            const nameBytes = textEnc.encode(file.name || 'file.txt');
            const dataBytes = typeof file.content === 'string'
                ? textEnc.encode(file.content)
                : (file.content instanceof Uint8Array ? file.content : new Uint8Array(file.content || []));

            const crc = crc32(dataBytes);
            const size = dataBytes.length;

            // Local File Header (30 bytes + name + data)
            const lhBuf = new ArrayBuffer(30);
            const lhView = new DataView(lhBuf);
            lhView.setUint32(0, 0x04034b50, true); // Local header signature
            lhView.setUint16(4, 20, true);         // Version needed to extract (2.0)
            lhView.setUint16(6, 0x0800, true);     // General purpose bit flag (UTF-8 filename)
            lhView.setUint16(8, 0, true);          // Compression method: 0 (STORE)
            lhView.setUint16(10, dosTime, true);
            lhView.setUint16(12, dosDate, true);
            lhView.setUint32(14, crc, true);
            lhView.setUint32(18, size, true);       // Compressed size
            lhView.setUint32(22, size, true);       // Uncompressed size
            lhView.setUint16(26, nameBytes.length, true);
            lhView.setUint16(28, 0, true);          // Extra field length

            const localBlock = new Uint8Array(30 + nameBytes.length + size);
            localBlock.set(new Uint8Array(lhBuf), 0);
            localBlock.set(nameBytes, 30);
            localBlock.set(dataBytes, 30 + nameBytes.length);
            localParts.push(localBlock);

            // Central Directory Header (46 bytes + name)
            const chBuf = new ArrayBuffer(46);
            const chView = new DataView(chBuf);
            chView.setUint32(0, 0x02014b50, true); // Central directory signature
            chView.setUint16(4, 20, true);         // Version made by
            chView.setUint16(6, 20, true);         // Version needed
            chView.setUint16(8, 0x0800, true);     // UTF-8 flag
            chView.setUint16(10, 0, true);         // Method: STORE
            chView.setUint16(12, dosTime, true);
            chView.setUint16(14, dosDate, true);
            chView.setUint32(16, crc, true);
            chView.setUint32(20, size, true);
            chView.setUint32(24, size, true);
            chView.setUint16(28, nameBytes.length, true);
            chView.setUint16(30, 0, true);         // Extra field length
            chView.setUint16(32, 0, true);         // Comment length
            chView.setUint16(34, 0, true);         // Disk start
            chView.setUint16(36, 0, true);         // Internal attr
            chView.setUint32(38, 0, true);         // External attr
            chView.setUint32(42, offset, true);    // Offset of local header

            const centralBlock = new Uint8Array(46 + nameBytes.length);
            centralBlock.set(new Uint8Array(chBuf), 0);
            centralBlock.set(nameBytes, 46);
            centralParts.push(centralBlock);

            offset += localBlock.length;
        });

        // Compute central directory size
        let cdSize = 0;
        centralParts.forEach(function (part) { cdSize += part.length; });
        const cdOffset = offset;

        // End of Central Directory Record (22 bytes)
        const eocdBuf = new ArrayBuffer(22);
        const eocdView = new DataView(eocdBuf);
        eocdView.setUint32(0, 0x06054b50, true);
        eocdView.setUint16(4, 0, true);
        eocdView.setUint16(6, 0, true);
        eocdView.setUint16(8, files.length, true);  // Entries on this disk
        eocdView.setUint16(10, files.length, true); // Total entries
        eocdView.setUint32(12, cdSize, true);       // Size of CD
        eocdView.setUint32(16, cdOffset, true);     // Offset of CD
        eocdView.setUint16(20, 0, true);            // Comment length

        const totalSize = offset + cdSize + 22;
        const out = new Uint8Array(totalSize);
        let ptr = 0;
        localParts.forEach(function (part) {
            out.set(part, ptr);
            ptr += part.length;
        });
        centralParts.forEach(function (part) {
            out.set(part, ptr);
            ptr += part.length;
        });
        out.set(new Uint8Array(eocdBuf), ptr);

        return out;
    }

    /**
     * Download files as a ZIP archive.
     * Supports both siDownloadZip(filename, files) and siDownloadZip({ filename, files }).
     * @param {string|Object} filename
     * @param {Array<{name: string, content: string|Uint8Array}>} [files]
     */
    window.siDownloadZip = function (filename, files) {
        if (typeof filename === 'object' && filename !== null && !Array.isArray(filename)) {
            files = filename.files;
            filename = filename.filename;
        }
        if (!filename) filename = 'report.zip';
        if (!/\.zip$/i.test(filename)) filename += '.zip';

        const zipBytes = createZip(files || []);
        const blob = new Blob([zipBytes], { type: 'application/zip' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        setTimeout(function () {
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }, 300);
    };

    /**
     * Escape a value for CSV.
     */
    function escCsv(val) {
        if (val == null) return '""';
        let str = String(val);
        // Strip tags if HTML leaked in
        if (str.indexOf('<') !== -1) {
            str = str.replace(/<[^>]+>/g, '').trim();
        }
        if (str.indexOf('"') !== -1 || str.indexOf(',') !== -1 || str.indexOf('\n') !== -1 || str.indexOf('\r') !== -1) {
            return '"' + str.replace(/"/g, '""') + '"';
        }
        return '"' + str + '"';
    }

    /**
     * Build CSV string with UTF-8 BOM.
     * @param {Object} opts
     * @param {Array<[string, string]>} [opts.meta] e.g. [['Report', 'Agent Register'], ['Date', '2026-11-05']]
     * @param {Array<string>} opts.headers
     * @param {Array<Array<any>>} opts.rows
     * @param {Array<any>} [opts.totals]
     * @returns {string}
     */
    window.siBuildCsv = function (opts) {
        opts = opts || {};
        const lines = [];

        // UTF-8 Byte Order Mark
        let csv = '\uFEFF';

        if (Array.isArray(opts.meta)) {
            opts.meta.forEach(function (pair) {
                if (pair && pair.length >= 2 && pair[1]) {
                    lines.push(escCsv(pair[0]) + ',' + escCsv(pair[1]));
                }
            });
            if (opts.meta.length) lines.push('');
        }

        if (Array.isArray(opts.headers) && opts.headers.length) {
            lines.push(opts.headers.map(escCsv).join(','));
        }

        if (Array.isArray(opts.rows)) {
            opts.rows.forEach(function (row) {
                lines.push((row || []).map(escCsv).join(','));
            });
        }

        if (Array.isArray(opts.totals) && opts.totals.length) {
            lines.push(opts.totals.map(escCsv).join(','));
        }

        return csv + lines.join('\r\n');
    };

    /**
     * Warn user that data > 1000 rows, block print, and offer direct ZIP download.
     * @param {number} rowCount
     * @param {Function} [onDownloadZip]
     */
    window.siWarnPrintLimit = function (rowCount, onDownloadZip) {
        const countTxt = rowCount ? ` (${rowCount} rows)` : '';
        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Exceeds 1000 Rows',
                html: `Your data is more than 1000 so go to download.${countTxt ? '<br><small class="text-muted">' + countTxt + '</small>' : ''}`,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-download me-1"></i> Download ZIP',
                cancelButtonText: 'Close',
                confirmButtonColor: '#28a745'
            }).then(function (result) {
                if (result.isConfirmed && typeof onDownloadZip === 'function') {
                    onDownloadZip();
                }
            });
        } else {
            alert('Your data is more than 1000 so go to download.');
            if (typeof onDownloadZip === 'function') {
                onDownloadZip();
            }
        }
    };

    /**
     * Sanitize a string for filenames.
     */
    window.siSanitizeFilename = function (str) {
        if (!str) return 'Report';
        return String(str)
            .replace(/[\\/:*?"<>|]+/g, '_')
            .replace(/\s+/g, '_')
            .replace(/_+/g, '_')
            .replace(/^_+|_+$/g, '');
    };

    /**
     * Format today as YYYY-MM-DD.
     */
    window.siDateStamp = function () {
        const d = new Date();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${d.getFullYear()}-${m}-${day}`;
    };

    // Auto-inject #downloadBtn next to #printBtn if not already rendered
    $(function () {
        $('button#printBtn').each(function () {
            const $print = $(this);
            if (!$print.siblings('#downloadBtn').length && !$print.parent().find('#downloadBtn').length) {
                const isW100 = $print.hasClass('w-100');
                const btnHtml = isW100
                    ? `<button type="button" class="btn btn-outline-success w-100" id="downloadBtn" disabled>
                           <i class="fas fa-download me-1"></i> Download
                       </button>`
                    : `<button type="button" class="btn btn-outline-success" id="downloadBtn" disabled>
                           <i class="fas fa-download me-1"></i> Download
                       </button>`;

                if ($print.parent().hasClass('d-flex') || $print.parent().hasClass('btn-group')) {
                    $print.after(btnHtml);
                } else if ($print.parent().is('.col, [class*="col-"]')) {
                    // In a column layout
                    const $col = $print.parent();
                    if ($col.siblings().length && isW100) {
                        $col.after(`<div class="${$col.attr('class')}">${btnHtml}</div>`);
                    } else {
                        $print.after(btnHtml);
                    }
                } else {
                    $print.after(btnHtml);
                }
            }
        });
    });

})(window, window.jQuery);
