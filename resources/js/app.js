document.addEventListener('click', (event) => {
    const pickerInput = event.target.closest('input[type="date"], input[type="time"]');

    if (! pickerInput || pickerInput.disabled || pickerInput.readOnly || typeof pickerInput.showPicker !== 'function') {
        return;
    }

    try {
        pickerInput.showPicker();
    } catch {
        pickerInput.focus();
    }
});

const validateSelectableDate = (dateInput) => {
    if (! dateInput || ! dateInput.matches('input[type="date"][data-no-sundays]') || ! dateInput.value) {
        return;
    }

    const selectedDate = new Date(`${dateInput.value}T00:00:00`);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (selectedDate <= today) {
        dateInput.setCustomValidity('Selecciona una fecha posterior a hoy.');
    } else if (selectedDate.getDay() === 0) {
        dateInput.setCustomValidity('No se pueden seleccionar domingos.');
    } else {
        dateInput.setCustomValidity('');

        return;
    }

    dateInput.reportValidity();
    dateInput.value = '';
    dateInput.dispatchEvent(new Event('input', { bubbles: true }));
};

document.addEventListener('change', (event) => {
    validateSelectableDate(event.target.closest('input[type="date"][data-no-sundays]'));
});
