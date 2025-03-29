// DOM elements initialization
const table = document.getElementById("students");
const modal = document.getElementById('modal');
const confirmModal = document.getElementById('confirmModal');
const addBtn = document.getElementById('add-btn');
const closeBtn = document.getElementById('close-btn');
const cancelBtn = document.getElementById('cancel-btn');
const form = document.getElementById('student-form');
const selectAllCheckbox = document.getElementById('selectAll');

const confirmMessage = document.getElementById('confirm-message');
const confirmYesBtn = document.getElementById('confirm-yes-btn');
const confirmNoBtn = document.getElementById('confirm-no-btn');
const closeConfirmBtn = document.getElementById('close-confirm-btn');
const modalOverlay = document.getElementById('modal-overlay');

// State variables
let students = [];
let nextId = 1;
let pendingDeleteItems = [];
let isEditingMode = false;  
let editingStudentId = null;

// Event listeners setup
addBtn.addEventListener('click', openModal);
closeBtn.addEventListener('click', closeModal);
cancelBtn.addEventListener('click', closeModal);
table.addEventListener('click', handleTableActions);
selectAllCheckbox.addEventListener('change', toggleSelectAll);

confirmYesBtn.addEventListener('click', confirmDeletion);
confirmNoBtn.addEventListener('click', closeConfirmModal);
closeConfirmBtn.addEventListener('click', closeConfirmModal);

modalOverlay.addEventListener('click', function() {
    closeModal();
    closeConfirmModal();
});

// Select/deselect all rows
function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.student-select');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

// Show add/edit student modal
function openModal() {
    modal.style.display = 'block';
    modal.classList.add('active');

    modalOverlay.classList.add('active');
    modalOverlay.style.display = 'block';

    document.body.style.overflow = 'hidden';
}   

// Hide student modal
function closeModal() {
    modal.classList.remove('active');
    modalOverlay.classList.remove('active');
    
    setTimeout(() => {
        modal.style.display = 'none';
        modalOverlay.style.display = 'none';
        document.body.style.overflow = 'auto';
        form.reset();
        
        isEditingMode = false;
        editingStudentId = null;
        document.getElementById('submit-btn').textContent = 'Save';
    }, 300);
}

// Format date to DD.MM.YYYY format
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
}

// Add or update student data
window.addStudent = function(e) {
    e.preventDefault();
    
    const isHtmlValidation = document.getElementById('validation-html').checked;
    
    if (isHtmlValidation && !e.target.checkValidity()) {
        return;
    }
    
    const formData = {
        group: document.getElementById('group').value,
        name: document.getElementById('name').value,
        surname: document.getElementById('surname').value,
        gender: document.getElementById('gender').value,
        birthday: formatDate(document.getElementById('birthday').value)
    };
    
    if (isEditingMode && editingStudentId !== null) {
        updateStudent(editingStudentId, formData);
        isEditingMode = false;
        editingStudentId = null;
        document.getElementById('submit-btn').textContent = 'Save';

        console.log('Updated student:', JSON.stringify(formData, null, 2));
    } else {
        const studentId = nextId++;
        formData.id = studentId;
        
        const tr = document.createElement("tr");
        tr.dataset.studentId = studentId;

        tr.innerHTML = `
                        <td><input type="checkbox" class="student-select" aria-label="Select student"></td>
                        <td>${formData.group}</td>
                        <td>${formData.surname} ${formData.name}</td>
                        <td>${formData.gender}</td>
                        <td>${formData.birthday}</td>
                        <td><span class="status-indicator online"></span> Online</td>
                        <td>
                            <button class="edit-btn" aria-label="Edit student">Edit</button>
                            <button class="delete-btn" aria-label="Delete student">Delete</button>
                        </td>
                    `;

        table.appendChild(tr);
        students.push(formData);

        console.log('Added student:', JSON.stringify(formData, null, 2));
    }
    
    // console.log('Current students:', JSON.stringify(students, null, 2));

    closeModal();
};

// Update existing student data
function updateStudent(studentId, formData) {
    const index = students.findIndex(student => student.id === studentId);
    if (index === -1) return;
    
    formData.id = studentId;
    formData.status = students[index].status || 'online';
    
    students[index] = formData;
    
    const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
    if (row) {
        row.cells[1].textContent = formData.group;
        row.cells[2].textContent = `${formData.surname} ${formData.name}`;
        row.cells[3].textContent = formData.gender;
        row.cells[4].textContent = formData.birthday;
    }
}

// Handle table action buttons (edit/delete)
function handleTableActions(e) {
    const target = e.target;
    if (target.classList.contains('delete-btn')) {
        deleteStudent(target);
    } else if (target.classList.contains('edit-btn')) {
        editStudent(target);
    }
}

// Process student deletion
function deleteStudent(deleteBtn) {
    const selectedCheckboxes = document.querySelectorAll('.student-select:checked');
    
    if (selectedCheckboxes.length > 0) {
        pendingDeleteItems = Array.from(selectedCheckboxes).map(checkbox => checkbox.closest('tr'));
        confirmMessage.textContent = `Are you sure you want to delete all ${selectedCheckboxes.length} selected students?`;
        openConfirmModal();
    } else {
        const row = deleteBtn.closest('tr');
        pendingDeleteItems = [row];
        
        const studentName = row.cells[2].textContent;
        confirmMessage.textContent = `Are you sure you want to delete ${studentName}?`;
        openConfirmModal();
    }
}

// Confirm and execute deletion of students
function confirmDeletion() {
    pendingDeleteItems.forEach(row => {
        const studentId = parseInt(row.dataset.studentId);
        
        const studentIndex = students.findIndex(student => student.id === studentId);
        if (studentIndex !== -1) {
            students.splice(studentIndex, 1);
        }
        
        row.remove();
    });
    
    if (pendingDeleteItems.length > 1) {
        selectAllCheckbox.checked = false;
    }
    
    pendingDeleteItems = [];
    closeConfirmModal();
}

// Show confirmation modal
function openConfirmModal() {
    confirmModal.style.display = 'block';
    confirmModal.classList.add('active');
    
    modalOverlay.style.display = 'block';
    modalOverlay.classList.add('active');
    
    document.body.style.overflow = 'hidden';
}

// Hide confirmation modal
function closeConfirmModal() {
    confirmModal.classList.remove('active');
    modalOverlay.classList.remove('active');
    
    setTimeout(() => {
        confirmModal.style.display = 'none';
        if (!modal.classList.contains('active')) {
            document.body.style.overflow = 'auto';
            modalOverlay.style.display = 'none';
        }
        pendingDeleteItems = [];
    }, 300);
}

// Edit existing student data
function editStudent(editBtn){
    const row = editBtn.closest('tr');
    const studentId = parseInt(row.dataset.studentId);

    const student = students.find(student => student.id === studentId);

    if (!student) 
        return;

    document.getElementById('group').value = student.group;
    document.getElementById('name').value = student.name;
    document.getElementById('surname').value = student.surname;
    document.getElementById('gender').value = student.gender;

    const dateParts = student.birthday.split('.');
    if (dateParts.length === 3) {
        const formattedDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
        document.getElementById('birthday').value = formattedDate;
    }

    isEditingMode = true;
    editingStudentId = studentId;

    openModal();

    document.getElementById('submit-btn').textContent = 'Update';
}

// Initialize existing table rows on page load
function initializeExistingRows() {
    const existingRows = table.querySelectorAll('tbody tr');
    existingRows.forEach(row => {
        if (!row.dataset.studentId) {
            const studentId = nextId++;
            row.dataset.studentId = studentId;
            
            const cells = row.cells;
            
            const fullName = cells[2].textContent.trim();
            const nameParts = fullName.split(' ');
            const surname = nameParts[0] || '';
            const name = nameParts.slice(1).join(' ') || '';
            
            const student = {
                id: studentId,
                group: cells[1].textContent,
                surname: surname,
                name: name,
                gender: cells[3].textContent,
                birthday: cells[4].textContent,
                status: cells[5].textContent.includes('Online') ? 'online' : 'offline'
            };
            
            students.push(student);
        }
    });
}

window.addEventListener('DOMContentLoaded', initializeExistingRows);

