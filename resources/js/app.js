document.addEventListener('click', (event) => {
    const pickerInput = event.target.closest('input[type="date"]');

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

const buildTimeOptions = () => {
    const options = [];

    for (let hour = 8; hour <= 18; hour += 1) {
        for (let minute = 0; minute < 60; minute += 15) {
            if (hour === 18 && minute > 0) {
                continue;
            }

            options.push(`${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`);
        }
    }

    return options;
};

const timeOptions = buildTimeOptions();
let activeTimeInput = null;
let timePickerMenu = null;

const ensureTimePickerMenu = () => {
    if (timePickerMenu) {
        return timePickerMenu;
    }

    timePickerMenu = document.createElement('div');
    timePickerMenu.className = 'time-picker-menu';
    timePickerMenu.hidden = true;
    timePickerMenu.setAttribute('role', 'listbox');

    const header = document.createElement('div');
    header.className = 'time-picker-header';
    header.innerHTML = '<span>Horario</span><strong>08:00 - 18:00</strong>';
    timePickerMenu.append(header);

    const grid = document.createElement('div');
    grid.className = 'time-picker-grid';

    for (const option of timeOptions) {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = option;
        button.dataset.timeValue = option;
        button.setAttribute('role', 'option');

        grid.append(button);
    }

    timePickerMenu.append(grid);

    document.body.append(timePickerMenu);

    return timePickerMenu;
};

const closeTimePicker = () => {
    if (timePickerMenu) {
        timePickerMenu.hidden = true;
    }

    activeTimeInput = null;
};

const positionTimePicker = (input) => {
    const menu = ensureTimePickerMenu();
    const rect = input.getBoundingClientRect();
    const top = Math.min(rect.bottom + 6, window.innerHeight - 330);

    menu.style.left = `${Math.max(16, Math.min(rect.left, window.innerWidth - 336))}px`;
    menu.style.top = `${Math.max(16, top)}px`;
};

const openFallbackTimePicker = (input) => {
    activeTimeInput = input;
    const menu = ensureTimePickerMenu();

    for (const button of menu.querySelectorAll('button')) {
        button.setAttribute('aria-selected', button.dataset.timeValue === input.value ? 'true' : 'false');
    }

    positionTimePicker(input);
    menu.hidden = false;
};

document.addEventListener('pointerdown', (event) => {
    const timeInput = event.target.closest('input[data-time-picker]');

    if (! timeInput || timeInput.disabled) {
        return;
    }

    event.preventDefault();
    timeInput.focus();
    openFallbackTimePicker(timeInput);
});

document.addEventListener('click', (event) => {
    const selectedTime = event.target.closest('.time-picker-menu button');

    if (selectedTime && activeTimeInput) {
        activeTimeInput.value = selectedTime.dataset.timeValue;
        activeTimeInput.dispatchEvent(new Event('input', { bubbles: true }));
        activeTimeInput.dispatchEvent(new Event('change', { bubbles: true }));
        closeTimePicker();

        return;
    }

    if (! event.target.closest('.time-picker-menu, input[data-time-picker]')) {
        closeTimePicker();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeTimePicker();
    }
});

window.addEventListener('resize', closeTimePicker);
