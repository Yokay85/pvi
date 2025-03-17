const table = document.getElementById("students");
const modal = document.getElementById('modal');
const confirmModal = document.getElementById('confirmModal');
const modalOverlay = document.getElementById('modal-overlay');
const addBtn = document.getElementById('add-btn');
const closeBtn = document.getElementById('close-btn');
const cancelBtn = document.getElementById('cancel-btn');
const saveBtn = document.getElementById('submit-btn');
const form = document.getElementById('student-form');
const selectAllCheckbox = document.getElementById('selectAll');

const confirmMessage = document.getElementById('confirm-message');
const confirmYesBtn = document.getElementById('confirm-yes-btn');
const confirmNoBtn = document.getElementById('confirm-no-btn');
const closeConfirmBtn = document.getElementById('close-confirm-btn');

let students = [];
let nextId = 1;
let pendingDeleteItems = [];

addBtn.addEventListener('click', openModal);
closeBtn.addEventListener('click', closeModal);
cancelBtn.addEventListener('click', closeModal);
form.addEventListener('submit', addStudent);
table.addEventListener('click', handleTableActions);
selectAllCheckbox.addEventListener('change', toggleSelectAll);

confirmYesBtn.addEventListener('click', confirmDeletion);
confirmNoBtn.addEventListener('click', closeConfirmModal);
closeConfirmBtn.addEventListener('click', closeConfirmModal);

function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.student-select');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

function openModal() {
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}   

function closeModal() {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    form.reset();
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
}

function addStudent(e) {
    e.preventDefault();
    const studentId = nextId++;
    
    const formData = {
        id: studentId,
        group: document.getElementById('group').value,
        name: document.getElementById('name').value,
        surname: document.getElementById('surname').value,
        gender: document.getElementById('gender').value,
        birthday: formatDate(document.getElementById('birthday').value)
    };

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
    
    closeModal();
}

function handleTableActions(e) {
    const target = e.target;
    if (target.classList.contains('delete-btn')) {
        deleteStudent(target);
    } else if (target.classList.contains('edit-btn')) {
        
    }
}

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

function openConfirmModal() {
    confirmModal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeConfirmModal() {
    confirmModal.style.display = 'none';
    document.body.style.overflow = 'auto';
    pendingDeleteItems = [];
}

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

