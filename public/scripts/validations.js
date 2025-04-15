const validations = {
    group: {
        regex: /^[A-Za-z]{2}-\d{2}$/,
        message: "Group should be in format XX-YY (e.g., PZ-21)"
    },
    name: {
        regex: /^[A-Za-zА-Яа-яЇїІіЄєҐґ\s\-]{2,50}$/,
        message: "Name should contain 2-50 characters (letters, spaces, hyphens)"
    },
    surname: {
        regex: /^[A-Za-zА-Яа-яЇїІіЄєҐґ\s\-]{2,50}$/,
        message: "Surname should contain 2-50 characters (letters, spaces, hyphens)"
    },
    gender: {
        message: "Please select a gender"
    },
    birthday: {
        message: "Please enter a valid birthday (age should be between 16 and 100 years)"
    }
};

/**
 * Validates if the birthday date is within the acceptable age range (16-100)
 */
function validateBirthday(date) {
    const today = new Date();
    const birthDate = new Date(date);

    if (isNaN(birthDate.getTime())) {
        return false;
    }

    if (birthDate > today) {
        return false;
    }

    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }

    return age >= 16 && age <= 100;
}

const touchedFields = new Set();

/**
 * Validates a specific form field and displays validation results
 */
function validateField(field, skipVisuals = false) {
    const fieldName = field.id;
    const errorElement = document.getElementById(`${fieldName}-error`);
    const validation = validations[fieldName];
    let isValid = false;

    const shouldShowVisuals = touchedFields.has(fieldName);

    if (!skipVisuals) {
        field.parentElement.classList.remove('error', 'valid');
        if (errorElement) errorElement.textContent = '';
    }

    if (fieldName === 'birthday') {
        isValid = field.value && validateBirthday(field.value);
    } else if (fieldName === 'gender' || fieldName === 'group') {
        isValid = field.value !== '';
    } else if (validation && validation.regex) {
        isValid = validation.regex.test(field.value);
    } else {
        isValid = field.value.trim() !== '';
    }

    if (!skipVisuals && shouldShowVisuals) {
        if (!isValid) {
            field.parentElement.classList.add('error');
            if (errorElement) errorElement.textContent = validation.message;
        } else {
            field.parentElement.classList.add('valid');
        }
    }

    return isValid;
}

/**
 * Initializes form validation and sets up event handlers
 */
function initializeValidation() {
    const form = document.getElementById('student-form');
    const htmlRadio = document.getElementById('validation-html');
    const jsRadio = document.getElementById('validation-js');
    const formFields = form.querySelectorAll('input:not([type="radio"]), select');
    const birthdayField = document.getElementById('birthday');

    /**
     * Sets up the min and max date constraints for birthday field
     */
    function setupDateValidation() {
        const today = new Date();

        const maxDate = new Date(today);
        maxDate.setFullYear(today.getFullYear() - 16);

        const minDate = new Date(today);
        minDate.setFullYear(today.getFullYear() - 100);

        birthdayField.max = maxDate.toISOString().split('T')[0];
        birthdayField.min = minDate.toISOString().split('T')[0];
    }

    setupDateValidation();

    /**
     * Toggles between HTML5 and JS validation methods
     */
    function toggleValidationMethod() {
        const useHTML = htmlRadio.checked;

        form.setAttribute('novalidate', !useHTML);

        touchedFields.clear();
        formFields.forEach(field => {
            field.parentElement.classList.remove('error', 'valid');
            const errorElement = document.getElementById(`${field.id}-error`);
            if (errorElement) errorElement.textContent = '';
        });
    }

    htmlRadio.addEventListener('change', toggleValidationMethod);
    jsRadio.addEventListener('change', toggleValidationMethod);

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (jsRadio.checked) {
            formFields.forEach(field => touchedFields.add(field.id));

            let isFormValid = true;
            formFields.forEach(field => {
                if (!validateField(field)) {
                    isFormValid = false;
                }
            });

            if (isFormValid) {
                window.addStudent(e);
            }
        } else {
            if (form.checkValidity()) {
                window.addStudent(e);
            } else {
                form.reportValidity();
            }
        }
    });

    formFields.forEach(field => {
        field.addEventListener('input', function () {
            touchedFields.add(field.id);
            if (jsRadio.checked) {
                validateField(field);
            }
        });

        field.addEventListener('blur', function () {
            touchedFields.add(field.id);
            if (jsRadio.checked) {
                validateField(field);
            }
        });
    });

    toggleValidationMethod();
}

window.addEventListener('DOMContentLoaded', initializeValidation);