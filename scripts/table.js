const table = document.getElementById("students");
const modal = document.getElementById('modal');
const modalOverlay = document.getElementById('modal-overlay');
const addBtn = document.getElementById('add-btn');
const closeBtn = document.getElementById('close-btn');
const cancelBtn = document.getElementById('cancel-btn');
const saveBtn = document.getElementById('submit-btn');
const form = document.getElementById('student-form');

let students = [];
let nextId = 1;

addBtn.addEventListener('click', openModal);
closeBtn.addEventListener('click', closeModal);
cancelBtn.addEventListener('click', closeModal);
form.addEventListener('submit', addStudent);
table.addEventListener('click', handleTableActions);

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
        gender: document.getElementById('gender').value,
        birthday: formatDate(document.getElementById('birthday').value)
    };

    const tr = document.createElement("tr");
    tr.dataset.studentId = studentId;

    tr.innerHTML = `
                    <td><input type="checkbox" class="student-select" aria-label="Select student"></td>
                    <td>${formData.group}</td>
                    <td>${formData.name}</td>
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
    const row = deleteBtn.closest('tr');
    const studentId = parseInt(row.dataset.studentId);
    
    const studentIndex = students.findIndex(student => student.id === studentId);
    if (studentIndex !== -1) {
        students.splice(studentIndex, 1);
    }
    
    row.remove();
}

function initializeExistingRows() {
    const existingRows = table.querySelectorAll('tbody tr');
    existingRows.forEach(row => {
        if (!row.dataset.studentId) {
            const studentId = nextId++;
            row.dataset.studentId = studentId;
            
            const cells = row.cells;
            const student = {
                id: studentId,
                group: cells[1].textContent,
                name: cells[2].textContent,
                gender: cells[3].textContent,
                birthday: cells[4].textContent,
                status: cells[5].textContent.includes('Online') ? 'online' : 'offline'
            };
            
            students.push(student);
        }
    });
}

window.addEventListener('DOMContentLoaded', initializeExistingRows);

