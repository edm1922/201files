document.addEventListener('DOMContentLoaded', function () {
    const companySelectForm = document.getElementById('companySelectForm');
    const folderCodePreview = document.getElementById('folderCodePreview');
    const locationSelect = document.getElementById('locationSelectForm');
    const initialLocation = locationSelect ? locationSelect.value : '';
    let firstLocationFilterPass = true;

    function refreshFolderCodePreview() {
        if (!folderCodePreview || !companySelectForm) {
            return;
        }

        const selectedOption = companySelectForm.options[companySelectForm.selectedIndex];
        const companyCode = selectedOption ? selectedOption.getAttribute('data-code') : '';
        const originalCompanyId = folderCodePreview.dataset.originalCompanyId || '';
        const existingCode = folderCodePreview.dataset.existingCode || '';

        if (existingCode && companySelectForm.value === originalCompanyId) {
            folderCodePreview.value = existingCode;
            return;
        }

        if (!companyCode) {
            folderCodePreview.value = '';
            folderCodePreview.placeholder = 'Auto-generated on save';
            return;
        }

        const nextCode = selectedOption ? (selectedOption.getAttribute('data-next-folder-code') || '') : '';

        if (nextCode) {
            folderCodePreview.value = nextCode;
            return;
        }

        folderCodePreview.value = `CSC-${companyCode.toUpperCase()}-0001`;
    }

    function filterLocationsByCompany() {
        if (!locationSelect || !companySelectForm) {
            return;
        }

        const selectedCompanyId = companySelectForm.value;
        let hasSelectedVisible = false;

        Array.from(locationSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            const optionCompanyId = option.getAttribute('data-company-id');
            const shouldShow = !!selectedCompanyId && optionCompanyId === selectedCompanyId;
            option.hidden = !shouldShow;
            const initiallyDisabled = option.getAttribute('data-initial-disabled') === '1';
            option.disabled = !shouldShow || initiallyDisabled;

            if (option.value === initialLocation) {
                option.disabled = false;
            }

            if (shouldShow && option.value === locationSelect.value) {
                hasSelectedVisible = true;
            }
        });

        if (selectedCompanyId && !hasSelectedVisible && (!firstLocationFilterPass || locationSelect.value !== initialLocation)) {
            locationSelect.value = '';
            $(locationSelect).trigger('change.select2');
        }

        firstLocationFilterPass = false;
    }

    if (companySelectForm) {
        companySelectForm.addEventListener('change', function () {
            refreshFolderCodePreview();
            filterLocationsByCompany();
        });
    }

    refreshFolderCodePreview();
    filterLocationsByCompany();
});
