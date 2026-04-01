<div class="preview-overlay" id="document-preview-overlay" hidden>
    <div class="preview-overlay__backdrop" data-preview-close></div>
    <div class="preview-overlay__toolbar">
        <div class="preview-overlay__toolbar-left">
            <button type="button" class="preview-overlay__btn" data-preview-close aria-label="Close preview" title="Close">
                <i class="fas fa-times"></i>
            </button>
            <span class="preview-overlay__filename" id="document-preview-title">Preview</span>
        </div>
        <div class="preview-overlay__toolbar-right">
            <a href="#" class="preview-overlay__btn" id="preview-download-btn" title="Download" download>
                <i class="fas fa-download"></i>
            </a>
            <button type="button" class="preview-overlay__btn" id="preview-print-btn" title="Print">
                <i class="fas fa-print"></i>
            </button>
        </div>
    </div>
    <div class="preview-overlay__content" data-preview-close>
        <div class="preview-overlay__body" id="document-preview-body">
            <div class="text-muted small">Select a previewable file (PDF, image, DOCX, Excel, or CSV).</div>
        </div>
    </div>
</div>

<script>
    (() => {
        const previewOverlay = document.getElementById('document-preview-overlay');
        const previewTitle = document.getElementById('document-preview-title');
        const previewBody = document.getElementById('document-preview-body');
        const previewDownloadBtn = document.getElementById('preview-download-btn');
        const previewPrintBtn = document.getElementById('preview-print-btn');
        let currentPreviewUrl = '';

        const resetPreviewBody = () => {
            if (!previewBody) return;
            previewBody.innerHTML = '<div class="text-muted small">Select a previewable file (PDF, image, DOCX, Excel, or CSV).</div>';
        };

        const PREVIEW_LIBRARIES = {
            jsZip: 'https://unpkg.com/jszip/dist/jszip.min.js',
            pdfJs: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js',
            pdfWorker: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js',
            docxPreview: 'https://unpkg.com/docx-preview@0.3.7/dist/docx-preview.min.js',
            sheetJs: 'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js',
        };

        const scriptPromises = {};
        let previewRenderToken = 0;

        const closePreview = () => {
            if (!previewOverlay) return;
            previewRenderToken += 1;
            previewOverlay.hidden = true;
            document.body.classList.remove('preview-overlay-open');
            resetPreviewBody();
        };

        const openPreview = () => {
            if (!previewOverlay) return;
            previewOverlay.hidden = false;
            document.body.classList.add('preview-overlay-open');
        };

        const escapeHtml = (value) => String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const loadScript = (src) => {
            if (scriptPromises[src]) return scriptPromises[src];

            scriptPromises[src] = new Promise((resolve, reject) => {
                const existing = Array.from(document.querySelectorAll('script[data-preview-lib]'))
                    .find((node) => node.getAttribute('data-preview-lib') === src);

                if (existing) {
                    if (existing.dataset.loaded === '1') {
                        resolve();
                        return;
                    }
                    existing.addEventListener('load', () => resolve(), { once: true });
                    existing.addEventListener('error', () => reject(new Error(`Failed to load ${src}`)), { once: true });
                    return;
                }

                const script = document.createElement('script');
                script.src = src;
                script.async = true;
                script.defer = true;
                script.dataset.previewLib = src;
                script.addEventListener('load', () => {
                    script.dataset.loaded = '1';
                    resolve();
                }, { once: true });
                script.addEventListener('error', () => reject(new Error(`Failed to load ${src}`)), { once: true });
                document.head.appendChild(script);
            });

            return scriptPromises[src];
        };

        const fetchFileBuffer = async (url) => {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('Unable to fetch preview file.');
            }
            return response.arrayBuffer();
        };

        const showPreviewLoading = (message) => {
            if (!previewBody) return;
            previewBody.innerHTML = `<div class="preview-overlay__loading">${escapeHtml(message || 'Loading preview...')}</div>`;
        };

        const renderPdfWithPdfJs = async (url, token) => {
            await loadScript(PREVIEW_LIBRARIES.pdfJs);
            if (!window.pdfjsLib) throw new Error('PDF renderer is unavailable.');
            window.pdfjsLib.GlobalWorkerOptions.workerSrc = PREVIEW_LIBRARIES.pdfWorker;

            const buffer = await fetchFileBuffer(url);
            if (token !== previewRenderToken) return;

            const pdf = await window.pdfjsLib.getDocument({ data: buffer }).promise;
            if (token !== previewRenderToken) return;

            const stack = document.createElement('div');
            stack.className = 'preview-overlay__pdf-stack';
            previewBody.innerHTML = '';
            previewBody.appendChild(stack);

            const containerWidth = Math.max((previewBody.clientWidth || 900) - 24, 320);
            for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                if (token !== previewRenderToken) return;

                const page = await pdf.getPage(pageNumber);
                const baseViewport = page.getViewport({ scale: 1 });
                const scale = Math.max(0.6, Math.min(1.75, containerWidth / Math.max(baseViewport.width, 1)));
                const viewport = page.getViewport({ scale });

                const canvas = document.createElement('canvas');
                canvas.className = 'preview-overlay__pdf-canvas';
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                const context = canvas.getContext('2d');
                if (!context) throw new Error('Canvas rendering is unavailable.');

                await page.render({ canvasContext: context, viewport }).promise;
                if (token !== previewRenderToken) return;

                stack.appendChild(canvas);
            }
        };

        const renderDocxWithLibrary = async (url, token) => {
            await loadScript(PREVIEW_LIBRARIES.jsZip);
            await loadScript(PREVIEW_LIBRARIES.docxPreview);

            if (!window.docx || typeof window.docx.renderAsync !== 'function') {
                throw new Error('DOCX renderer is unavailable.');
            }

            const buffer = await fetchFileBuffer(url);
            if (token !== previewRenderToken) return;

            previewBody.innerHTML = '<div class="preview-overlay__docx"></div>';
            const docxHost = previewBody.querySelector('.preview-overlay__docx');
            if (!docxHost) return;

            await window.docx.renderAsync(buffer, docxHost, undefined, {
                className: 'docx',
                inWrapper: true,
                ignoreWidth: false,
                ignoreHeight: false,
                breakPages: true,
                ignoreLastRenderedPageBreak: false,
                useBase64URL: true,
            });
        };

        const renderSheetWithLibrary = async (url, token) => {
            await loadScript(PREVIEW_LIBRARIES.sheetJs);

            if (!window.XLSX) {
                throw new Error('Spreadsheet renderer is unavailable.');
            }

            const buffer = await fetchFileBuffer(url);
            if (token !== previewRenderToken) return;

            const workbook = window.XLSX.read(buffer, { type: 'array' });
            const firstSheetName = workbook.SheetNames?.[0];
            if (!firstSheetName) throw new Error('Spreadsheet has no data.');

            const rows = window.XLSX.utils.sheet_to_json(workbook.Sheets[firstSheetName], {
                header: 1, raw: false, defval: '',
            });

            const maxRows = 250;
            const maxCols = 30;
            const clippedRows = rows.slice(0, maxRows).map((row) =>
                Array.isArray(row) ? row.slice(0, maxCols) : []
            );

            previewBody.innerHTML = '<div class="preview-overlay__sheet-wrap"></div>';
            const wrap = previewBody.querySelector('.preview-overlay__sheet-wrap');
            if (!wrap) return;

            const table = document.createElement('table');
            table.className = 'table table-sm table-bordered table-striped mb-0 preview-overlay__sheet-table';

            clippedRows.forEach((row, rowIndex) => {
                const tr = document.createElement('tr');
                const safeRow = row.length > 0 ? row : [''];
                safeRow.forEach((cell) => {
                    const cellEl = document.createElement(rowIndex === 0 ? 'th' : 'td');
                    cellEl.textContent = String(cell ?? '');
                    tr.appendChild(cellEl);
                });
                table.appendChild(tr);
            });
            wrap.appendChild(table);

            const wasTrimmed = rows.length > maxRows || rows.some((row) => Array.isArray(row) && row.length > maxCols);
            if (wasTrimmed) {
                const note = document.createElement('div');
                note.className = 'preview-overlay__sheet-note';
                note.textContent = `Preview limited to first ${maxRows} rows and ${maxCols} columns.`;
                wrap.appendChild(note);
            }
        };

        const renderPreview = async (url, kind, filename, ext) => {
            if (!previewBody || !previewTitle) return;

            const safeUrl = encodeURI(url);
            const safeName = escapeHtml(filename || 'Preview');
            const safeExt = escapeHtml((ext || '').toUpperCase());
            const token = ++previewRenderToken;
            previewTitle.textContent = filename || 'Preview';

            try {
                if (kind === 'image') {
                    previewBody.innerHTML = `<img src="${safeUrl}" alt="${safeName}" class="preview-overlay__image">`;
                    return;
                }
                if (kind === 'pdf') {
                    showPreviewLoading('Rendering PDF preview...');
                    await renderPdfWithPdfJs(url, token);
                    return;
                }
                if (kind === 'docx') {
                    showPreviewLoading('Rendering DOCX preview...');
                    await renderDocxWithLibrary(url, token);
                    return;
                }
                if (kind === 'sheet') {
                    showPreviewLoading('Rendering spreadsheet preview...');
                    await renderSheetWithLibrary(url, token);
                    return;
                }

                previewBody.innerHTML = `
                    <div class="preview-overlay__unsupported">
                        <div class="mb-2">Inline preview is not available for <strong>${safeExt || 'this format'}</strong>.</div>
                        <a href="${safeUrl}" class="btn btn-brand btn-sm" target="_blank" rel="noopener">Open / Download</a>
                    </div>
                `;
            } catch (error) {
                if (token !== previewRenderToken) return;
                previewBody.innerHTML = `
                    <div class="preview-overlay__unsupported">
                        <div class="mb-2">Preview could not be rendered for <strong>${safeExt || 'this format'}</strong>.</div>
                        <div class="small text-muted mb-3">Open or download the file as a fallback.</div>
                        <a href="${safeUrl}" class="btn btn-brand btn-sm" target="_blank" rel="noopener">Open / Download</a>
                    </div>
                `;
            }
        };

        const bindPreviewButtons = () => {
            document.querySelectorAll('[data-preview-trigger]').forEach((button) => {
                if (button.dataset.previewBound === '1') return;

                button.dataset.previewBound = '1';
                button.addEventListener('click', () => {
                    const url = button.getAttribute('data-preview-url');
                    const kind = button.getAttribute('data-preview-kind') || 'unknown';
                    const filename = button.getAttribute('data-preview-name') || 'Preview';
                    const ext = button.getAttribute('data-preview-ext') || '';

                    if (!url) return;

                    currentPreviewUrl = url;
                    if (previewDownloadBtn) {
                        previewDownloadBtn.href = url;
                        previewDownloadBtn.setAttribute('download', filename);
                    }

                    openPreview();
                    renderPreview(url, kind, filename, ext);
                });
            });
        };

        bindPreviewButtons();

        document.querySelectorAll('[data-preview-close]').forEach((button) => {
            button.addEventListener('click', (e) => {
                if (e.target === e.currentTarget) {
                    closePreview();
                }
            });
        });

        if (previewPrintBtn) {
            previewPrintBtn.addEventListener('click', () => {
                if (!currentPreviewUrl) return;
                const printWin = window.open(currentPreviewUrl, '_blank');
                if (printWin) {
                    printWin.addEventListener('load', () => {
                        printWin.print();
                    });
                }
            });
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closePreview();
            }
        });
    })();
</script>
