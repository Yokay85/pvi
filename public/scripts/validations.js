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
    // Removed htmlRadio and jsRadio references
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

    // Removed toggleValidationMethod function and its calls

    form.addEventListener('submit', function (e) {
        // Validate all fields first
        formFields.forEach(field => touchedFields.add(field.id)); // Ensure errors show even if not touched before submit

        let isFormValid = true;
        formFields.forEach(field => {
            if (!validateField(field)) { // validateField now also handles showing errors
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            e.preventDefault(); // Prevent submission ONLY if validation fails
            console.log("Validation failed, preventing submission.");
        }
        // If valid, do nothing here - allow the event to proceed to the table.js listener
        // REMOVED: window.addStudent(e);
    });

    formFields.forEach(field => {
        field.addEventListener('input', function () {
            touchedFields.add(field.id);
            // Always validate with JS on input
            validateField(field);
        });

        field.addEventListener('blur', function () {
            touchedFields.add(field.id);
            // Always validate with JS on blur
            validateField(field);
        });
    });

    // Ensure novalidate is always set for JS validation
    form.setAttribute('novalidate', true);
}

window.addEventListener('DOMContentLoaded', initializeValidation);