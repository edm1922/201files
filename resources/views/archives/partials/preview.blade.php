<div class="preview-overlay" id="document-preview-overlay" hidden>
    <div class="preview-overlay__backdrop" data-preview-close></div>
    <div class="preview-overlay__toolbar">
        <div class="preview-overlay__toolbar-left">
            <button type="button" class="preview-overlay__btn" data-preview-close aria-label="Close preview" title="Close">
                <i class="fas fa-times"></i>
            </button>
            <span class="preview-overlay__filename" id="document-preview-title">Preview</span>
        </div>
        <div class="preview-overlay__toolbar-center d-none" id="preview-pdf-controls">
            <div class="preview-overlay__pager" aria-label="PDF page navigation">
                <span class="preview-overlay__pager-label">Page</span>
                <input type="text" inputmode="numeric" class="preview-overlay__pager-input" id="preview-page-input"
                    aria-label="Current page">
                <span>/</span>
                <span id="preview-page-total">1</span>
            </div>
            <div class="preview-overlay__zoom" aria-label="PDF zoom controls">
                <button type="button" class="preview-overlay__btn" id="preview-zoom-out" title="Zoom out"
                    aria-label="Zoom out">
                    <i class="fas fa-search-minus"></i>
                </button>
                <button type="button" class="preview-overlay__btn" id="preview-zoom-reset" title="Reset zoom"
                    aria-label="Reset zoom">
                    <i class="fas fa-compress-arrows-alt"></i>
                </button>
                <button type="button" class="preview-overlay__btn" id="preview-zoom-in" title="Zoom in"
                    aria-label="Zoom in">
                    <i class="fas fa-search-plus"></i>
                </button>
            </div>
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
        const previewContent = previewOverlay?.querySelector('.preview-overlay__content');
        const previewTitle = document.getElementById('document-preview-title');
        const previewBody = document.getElementById('document-preview-body');
        const previewDownloadBtn = document.getElementById('preview-download-btn');
        const previewPrintBtn = document.getElementById('preview-print-btn');
        const previewPdfControls = document.getElementById('preview-pdf-controls');
        const previewPager = previewPdfControls?.querySelector('.preview-overlay__pager');
        const previewPageInput = document.getElementById('preview-page-input');
        const previewPageTotal = document.getElementById('preview-page-total');
        const previewZoomOutBtn = document.getElementById('preview-zoom-out');
        const previewZoomResetBtn = document.getElementById('preview-zoom-reset');
        const previewZoomInBtn = document.getElementById('preview-zoom-in');

        let currentPreviewUrl = '';
        let currentPreviewKind = '';
        const pdfPreviewState = {
            totalPages: 0,
            currentPage: 1,
            zoom: 1,
            minZoom: 0.5,
            maxZoom: 2.5,
        };
        let previewControlsHideTimer = null;

        const setPreviewControlsMode = (mode) => {
            if (!previewPdfControls || !previewOverlay) return;

            const visible = mode === 'pdf' || mode === 'docx' || mode === 'image' || mode === 'sheet';
            previewPdfControls.classList.toggle('d-none', !visible);
            previewOverlay.classList.toggle('preview-overlay--pdf', mode === 'pdf');
            previewOverlay.classList.toggle('preview-overlay--has-controls', visible);
            previewOverlay.classList.toggle('preview-overlay--paged', false);
            previewOverlay.classList.toggle('preview-overlay--controls-hidden', false);

            if (previewPager) {
                previewPager.classList.toggle('d-none', !(mode === 'pdf' || mode === 'docx'));
            }

            if (previewControlsHideTimer) {
                clearTimeout(previewControlsHideTimer);
                previewControlsHideTimer = null;
            }
        };

        const revealPdfControlsTemporarily = () => {
            if (!previewOverlay || previewOverlay.hidden || !previewOverlay.classList.contains('preview-overlay--has-controls')) {
                return;
            }

            previewOverlay.classList.remove('preview-overlay--controls-hidden');
            if (previewControlsHideTimer) clearTimeout(previewControlsHideTimer);

            previewControlsHideTimer = setTimeout(() => {
                if (!previewOverlay.hidden && previewOverlay.classList.contains('preview-overlay--has-controls')) {
                    previewOverlay.classList.add('preview-overlay--controls-hidden');
                }
            }, 1600);
        };

        const syncPdfControls = () => {
            if (previewPageInput) previewPageInput.value = String(pdfPreviewState.currentPage || 1);
            if (previewPageTotal) previewPageTotal.textContent = String(pdfPreviewState.totalPages || 1);
        };

        const applyPreviewZoom = () => {
            if (!previewBody) return;

            if (currentPreviewKind === 'pdf') {
                const canvases = previewBody.querySelectorAll('.preview-overlay__pdf-canvas');
                const zoomPercent = Math.max(25, Math.round(pdfPreviewState.zoom * 100));
                canvases.forEach((canvas) => {
                    canvas.style.width = `${zoomPercent}%`;
                    canvas.style.maxWidth = 'none';
                    canvas.style.alignSelf = 'center';
                });
                return;
            }

            if (currentPreviewKind === 'image') {
                const image = previewBody.querySelector('.preview-overlay__image');
                if (image) {
                    image.style.transform = `scale(${pdfPreviewState.zoom})`;
                    image.style.transformOrigin = 'center center';
                }
                return;
            }

            if (currentPreviewKind === 'docx') {
                const docx = previewBody.querySelector('.preview-overlay__docx');
                if (docx) docx.style.zoom = String(pdfPreviewState.zoom);
                return;
            }

            if (currentPreviewKind === 'sheet') {
                const sheet = previewBody.querySelector('.preview-overlay__sheet-wrap');
                if (sheet) sheet.style.zoom = String(pdfPreviewState.zoom);
            }
        };

        const scrollPdfToPage = (page) => {
            const scroller = previewContent || previewBody;
            if (!scroller) return;

            const index = Math.max(1, Math.min(page, pdfPreviewState.totalPages || 1));
            const target = previewBody?.querySelector(`.preview-overlay__pdf-canvas[data-page-number="${index}"]`);
            if (target) {
                const scrollerRect = scroller.getBoundingClientRect();
                const targetRect = target.getBoundingClientRect();
                const nextTop = scroller.scrollTop + (targetRect.top - scrollerRect.top) - 12;
                scroller.scrollTo({ top: Math.max(0, nextTop), behavior: 'smooth' });
                pdfPreviewState.currentPage = index;
                syncPdfControls();
            }
        };

        const scrollDocxToPage = (page) => {
            const scroller = previewContent || previewBody;
            if (!scroller || !previewBody) return;

            const pages = Array.from(previewBody.querySelectorAll('.preview-overlay__docx .docx-wrapper section.docx'));
            if (pages.length === 0) return;

            const index = Math.max(1, Math.min(page, pages.length));
            const target = pages[index - 1];
            const scrollerRect = scroller.getBoundingClientRect();
            const targetRect = target.getBoundingClientRect();
            const nextTop = scroller.scrollTop + (targetRect.top - scrollerRect.top) - 12;

            scroller.scrollTo({ top: Math.max(0, nextTop), behavior: 'smooth' });
            pdfPreviewState.currentPage = index;
            pdfPreviewState.totalPages = pages.length;
            syncPdfControls();
        };

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
            currentPreviewKind = '';
            setPreviewControlsMode('none');
            resetPreviewBody();
        };

        const openPreview = () => {
            if (!previewOverlay) return;
            previewOverlay.hidden = false;
            document.body.classList.add('preview-overlay-open');
            if (previewContent) {
                previewContent.scrollTo({ top: 0, left: 0, behavior: 'auto' });
            }
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

            pdfPreviewState.totalPages = pdf.numPages;
            pdfPreviewState.currentPage = 1;
            pdfPreviewState.zoom = 1;
            syncPdfControls();
            setPreviewControlsMode('pdf');
            revealPdfControlsTemporarily();

            const containerWidth = Math.max((previewBody.clientWidth || 900) - 24, 320);
            previewOverlay?.classList.toggle('preview-overlay--paged', pdf.numPages > 1);

            for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                if (token !== previewRenderToken) return;

                const page = await pdf.getPage(pageNumber);
                const baseViewport = page.getViewport({ scale: 1 });
                const scale = Math.max(0.6, Math.min(1.75, containerWidth / Math.max(baseViewport.width, 1)));
                const viewport = page.getViewport({ scale });

                const canvas = document.createElement('canvas');
                canvas.className = 'preview-overlay__pdf-canvas';
                canvas.dataset.pageNumber = String(pageNumber);
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

            if (token !== previewRenderToken) return;

            const pages = Array.from(docxHost.querySelectorAll('.docx-wrapper section.docx'));
            pdfPreviewState.totalPages = pages.length;
            pdfPreviewState.currentPage = 1;
            pdfPreviewState.zoom = 1;
            syncPdfControls();
            setPreviewControlsMode('docx');
            revealPdfControlsTemporarily();
            previewOverlay?.classList.toggle('preview-overlay--paged', pages.length > 1);
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

            pdfPreviewState.zoom = 1;
            setPreviewControlsMode('sheet');
        };

        const renderPreview = async (url, kind, filename, ext) => {
            if (!previewBody || !previewTitle) return;

            const safeUrl = encodeURI(url);
            const safeName = escapeHtml(filename || 'Preview');
            const safeExt = escapeHtml((ext || '').toUpperCase());
            const token = ++previewRenderToken;
            previewTitle.textContent = filename || 'Preview';
            currentPreviewKind = kind;

            try {
                if (kind === 'image') {
                    previewBody.innerHTML = `<img src="${safeUrl}" alt="${safeName}" class="preview-overlay__image">`;
                    pdfPreviewState.zoom = 1;
                    setPreviewControlsMode('image');
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

                setPreviewControlsMode('none');
                previewBody.innerHTML = `
                    <div class="preview-overlay__unsupported">
                        <div class="mb-2">Inline preview is not available for <strong>${safeExt || 'this format'}</strong>.</div>
                        <a href="${safeUrl}" class="btn btn-brand btn-sm" target="_blank" rel="noopener">Open / Download</a>
                    </div>
                `;
            } catch (error) {
                if (token !== previewRenderToken) return;
                setPreviewControlsMode('none');
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

        if (previewZoomInBtn) {
            previewZoomInBtn.addEventListener('click', () => {
                pdfPreviewState.zoom = Math.min(pdfPreviewState.maxZoom, Number((pdfPreviewState.zoom + 0.1).toFixed(2)));
                applyPreviewZoom();
            });
        }

        if (previewZoomOutBtn) {
            previewZoomOutBtn.addEventListener('click', () => {
                pdfPreviewState.zoom = Math.max(pdfPreviewState.minZoom, Number((pdfPreviewState.zoom - 0.1).toFixed(2)));
                applyPreviewZoom();
            });
        }

        if (previewZoomResetBtn) {
            previewZoomResetBtn.addEventListener('click', () => {
                pdfPreviewState.zoom = 1;
                applyPreviewZoom();
            });
        }

        if (previewPageInput) {
            previewPageInput.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') return;

                event.preventDefault();
                const requestedPage = Number.parseInt(previewPageInput.value, 10);
                if (!Number.isFinite(requestedPage)) {
                    syncPdfControls();
                    return;
                }

                if (currentPreviewKind === 'pdf') {
                    scrollPdfToPage(requestedPage);
                } else if (currentPreviewKind === 'docx') {
                    scrollDocxToPage(requestedPage);
                } else {
                    syncPdfControls();
                }
            });
        }

        if (previewOverlay) {
            previewOverlay.addEventListener('mousemove', revealPdfControlsTemporarily);
            previewOverlay.addEventListener('mouseenter', revealPdfControlsTemporarily);
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closePreview();
            }
        });
    })();
</script>
