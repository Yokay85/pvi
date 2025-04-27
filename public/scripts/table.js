// DOM elements initialization
const table = document.getElementById("students");
const modal = document.getElementById('modal');
const confirmModal = document.getElementById('confirmModal');
const addBtn = document.getElementById('add-btn');
const closeBtn = document.getElementById('close-btn');
const cancelBtn = document.getElementById('cancel-btn');
const form = document.getElementById('student-form');
const selectAllCheckbox = document.getElementById('selectAll');

// Pagination elements
const prevPageBtn = document.getElementById('prev-page');
const nextPageBtn = document.getElementById('next-page');
const pageButtons = document.querySelectorAll('.page-number');

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
addBtn && addBtn.addEventListener('click', openModal);
closeBtn && closeBtn.addEventListener('click', closeModal);
cancelBtn && cancelBtn.addEventListener('click', closeModal);
table && table.addEventListener('click', handleTableActions);
selectAllCheckbox && selectAllCheckbox.addEventListener('change', toggleSelectAll);

confirmYesBtn && confirmYesBtn.addEventListener('click', confirmDeletion);
confirmNoBtn && confirmNoBtn.addEventListener('click', closeConfirmModal);
closeConfirmBtn && closeConfirmBtn.addEventListener('click', closeConfirmModal);

modalOverlay && modalOverlay.addEventListener('click', function () {
    closeModal();
    closeConfirmModal();
});

// Add event listener for form submission
form && form.addEventListener('submit', handleFormSubmit);

// Pagination event listeners
prevPageBtn && prevPageBtn.addEventListener('click', goToPrevPage);
nextPageBtn && nextPageBtn.addEventListener('click', goToNextPage);
pageButtons.forEach(button => {
    button.addEventListener('click', function() {
        const pageNum = parseInt(this.getAttribute('data-page'));
        goToPage(pageNum);
    });
});

// Pagination functions
function goToPrevPage() {
    if (!prevPageBtn.disabled) {
        const currentPage = getCurrentPage();
        goToPage(currentPage - 1);
    }
}

function goToNextPage() {
    if (!nextPageBtn.disabled) {
        const currentPage = getCurrentPage();
        goToPage(currentPage + 1);
    }
}

function getCurrentPage() {
    const activeButton = document.querySelector('.page-number.active');
    return activeButton ? parseInt(activeButton.getAttribute('data-page')) : 1;
}

function goToPage(pageNum) {
    // Redirect to the same URL but with the page parameter
    window.location.href = `${window.location.pathname}?page=${pageNum}`;
}

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

// Format date from YYYY-MM-DD to DD.MM.YYYY
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
}

// Handle the form submission
function handleFormSubmit(e) {
    e.preventDefault(); // Prevent default HTML form submission

    const formData = new FormData(form);
    const studentId = editingStudentId; // Get the ID if we are editing

    // Prepare data common to add and update
    const studentData = {
        group: formData.get('group'),
        name: formData.get('name'),
        surname: formData.get('surname'),
        gender: formData.get('gender'),
        birthday: formData.get('birthday'),
        role: formData.get('role')
    };

    if (isEditingMode && studentId !== null) {
        // Update student logic
        studentData.id = studentId; // Add id for update request
        fetch(`${window.location.origin}${URL_ROOT}/public/index.php?action=updateStudent`, {
            method: 'POST',
            body: new URLSearchParams(studentData) // Send as form data
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Student updated successfully:', data.student);
                // Update local array and UI
                updateStudentInLocal(studentId, data.student);
                closeModal();
            } else {
                console.error('Error updating student:', data.message);
                alert('Error updating student: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('An error occurred while updating the student: ' + error.message);
        });

    } else {
        // Add student logic (existing fetch call)
        fetch(`${window.location.origin}${URL_ROOT}/public/index.php?action=addStudent`, {
            method: 'POST',
            body: formData // Send as FormData for add
        })
        .then(response => {
            // ... existing response checking ...
            if (!response.ok) {
                // Try to get error message from response body if possible
                return response.text().then(text => {
                    throw new Error(`Network response was not ok: ${response.status}. ${text}`);
                });
            }
            const contentType = response.headers.get('Content-Type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                     throw new Error(`Response is not JSON. Received: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Server response:', data);
            if (data.success) {
                // Get current pagination information
                const currentPage = getCurrentPage();
                const totalStudentsElement = document.querySelector('.pagination');
                
                // Check if we need to refresh the page due to pagination
                const studentsOnPage = document.querySelectorAll('#students tr').length;
                const studentsPerPage = 4; // This should match the server-side setting
                
                if (studentsOnPage < studentsPerPage) {
                    // There's space on the current page, we can add the student to the DOM
                    addStudentToDOM(data.student);
                } else {
                    // The page is full, we need to refresh to show updated pagination
                    const newUrl = new URL(window.location);
                    const totalPages = document.querySelectorAll('.page-number').length;
                    
                    // If we're on the last page, stay there, otherwise redirect to the new last page
                    if (currentPage === totalPages) {
                        // Stay on current page, but refresh to get the updated student list
                        window.location.reload();
                    } else {
                        // Go to the new last page (current last page + 1) if a new page was created
                        newUrl.searchParams.set('page', totalPages + 1);
                        window.location.href = newUrl.toString();
                    }
                }
                
                closeModal();
            } else {
                console.error('Error adding student:', data.message);
                alert('Error adding student: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('An error occurred while adding the student: ' + error.message);
        });
    }
}

// Helper function to add a student to the DOM
function addStudentToDOM(student) {
    const formattedBirthday = formatDate(student.birthday); // Format date from server
    const tr = document.createElement("tr");
    tr.dataset.studentId = student.id;

    // Create HTML content based on whether the user is logged in
    let innerHtml = '';
    
    // Check if selectAllCheckbox exists (which means the user is logged in)
    if (selectAllCheckbox) {
        innerHtml += `<td><input type="checkbox" class="student-select" aria-label="Select student"></td>`;
    }
    
    innerHtml += `
        <td>${student.group_name}</td>
        <td>${student.name}</td>
        <td>${student.gender}</td>
        <td>${formattedBirthday}</td>
        <td><span class="status-indicator ${student.status || 'offline'}"></span> ${student.status ? student.status.charAt(0).toUpperCase() + student.status.slice(1) : 'Offline'}</td>`;
    
    // Check if admin options (edit/delete buttons) should be shown
    const isAdmin = document.querySelector('.add-btn') !== null;
    if (isAdmin) {
        innerHtml += `
        <td>
            <button class="edit-btn" aria-label="Edit student">Edit</button>
            <button class="delete-btn" aria-label="Delete student">Delete</button>
        </td>`;
    }
    
    tr.innerHTML = innerHtml;
    table.appendChild(tr);
    
    // Update local students array
    student.birthday = formattedBirthday; // Store formatted date locally
    students.push(student);
    
    console.log('Added student to UI:', JSON.stringify(student, null, 2));
}

// Update existing student data in local array and UI (called after successful server update)
function updateStudentInLocal(studentId, updatedStudentData) {
    const index = students.findIndex(student => student.id === studentId);
    if (index === -1) return;

    // Format birthday before storing/displaying
    const formattedBirthday = formatDate(updatedStudentData.birthday);

    // Update local array
    students[index] = {
        ...students[index], // Keep existing status etc. if not provided
        id: studentId,
        group_name: updatedStudentData.group_name, // Use names from server response
        name: updatedStudentData.name,
        gender: updatedStudentData.gender,
        birthday: formattedBirthday, // Store formatted date
        // status: updatedStudentData.status || students[index].status || 'offline' // Keep existing status
    };

    // Update UI
    const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
    if (row) {
        row.cells[1].textContent = students[index].group_name;
        row.cells[2].textContent = students[index].name;
        row.cells[3].textContent = students[index].gender;
        row.cells[4].textContent = students[index].birthday; // Display formatted date
        // Update status indicator if needed
        const statusCell = row.cells[5];
        const indicator = statusCell.querySelector('.status-indicator');
        const statusText = statusCell.textContent.trim().split(' ').slice(1).join(' '); // Get text part
        if (indicator) {
            indicator.className = `status-indicator ${students[index].status || 'offline'}`;
        }
         statusCell.textContent = ''; // Clear cell
         statusCell.appendChild(indicator); // Re-add indicator
         statusCell.appendChild(document.createTextNode(` ${students[index].status ? students[index].status.charAt(0).toUpperCase() + students[index].status.slice(1) : 'Offline'}`)); // Re-add text

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
    const deletePromises = pendingDeleteItems.map(row => {
        const studentId = parseInt(row.dataset.studentId);
        return fetch(`${window.location.origin}${URL_ROOT}/public/index.php?action=deleteStudent`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ id: studentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`Student ${studentId} deleted successfully.`);
                // Remove from local array
                const studentIndex = students.findIndex(student => student.id === studentId);
                if (studentIndex !== -1) {
                    students.splice(studentIndex, 1);
                }
                // Remove row from UI
                row.remove();
                return { success: true };
            } else {
                console.error(`Error deleting student ${studentId}:`, data.message);
                alert(`Error deleting student ${row.cells[2].textContent}: ${data.message}`);
                return { success: false, message: data.message };
            }
        })
        .catch(error => {
            console.error(`Fetch error deleting student ${studentId}:`, error);
            alert(`An error occurred while deleting student ${row.cells[2].textContent}: ${error.message}`);
            return { success: false, message: error.message };
        });
    });

    // Wait for all delete requests to complete
    Promise.all(deletePromises).then(results => {
        const allSucceeded = results.every(result => result.success);
        if (allSucceeded) {
            console.log('All selected students deleted successfully.');
        } else {
            console.warn('Some students could not be deleted.');
        }

        // Reset state regardless of success/failure of individual deletions
        if (pendingDeleteItems.length > 1) {
            selectAllCheckbox.checked = false;
        }
        pendingDeleteItems = [];
        closeConfirmModal();
    });
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
function editStudent(editBtn) {
    const row = editBtn.closest('tr');
    const studentId = parseInt(row.dataset.studentId);

    // Find student data from the local 'students' array
    const student = students.find(s => s.id === studentId);

    if (!student) {
        console.error("Student data not found locally for ID:", studentId);
        alert("Could not find student data to edit.");
        return;
    }

    // Populate form fields
    document.getElementById('group').value = student.group_name; // Use group_name

    // Split full name into name and surname (assuming "Surname Name" format)
    const nameParts = student.name.trim().split(' ');
    const surname = nameParts.length > 1 ? nameParts.slice(0, -1).join(' ') : ''; // Handle multiple surnames
    const name = nameParts.length > 0 ? nameParts[nameParts.length - 1] : '';
    document.getElementById('surname').value = surname;
    document.getElementById('name').value = name;

    document.getElementById('gender').value = student.gender;

    // Convert DD.MM.YYYY back to YYYY-MM-DD for the date input
    const dateParts = student.birthday.split('.');
    if (dateParts.length === 3) {
        const formattedDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
        document.getElementById('birthday').value = formattedDate;
    } else {
         document.getElementById('birthday').value = ''; // Clear if format is wrong
         console.warn("Could not parse birthday:", student.birthday);
    }

    // Populate role - Assuming role is stored or can be inferred.
    // If role isn't in the local 'students' array, you might need to fetch it
    // or decide on a default/logic. For now, let's assume it might be 'student'.
    // If you added 'role' to the student object fetched initially, use:
    // document.getElementById('role').value = student.role || 'student';
    // If not, you might need to adjust how role is handled during edit.
    // Let's assume 'role' is part of the student object now.
     document.getElementById('role').value = student.role || 'student'; // Default to student if not set


    isEditingMode = true;
    editingStudentId = studentId;

    // Change modal title and button text
    document.querySelector('#modal .modal-header h3').textContent = 'Edit Student';
    document.getElementById('submit-btn').textContent = 'Update';

    openModal();
}

// Initialize existing rows on page load
function initializeExistingRows() {
    const existingRows = table.querySelectorAll('tbody tr');
    // Clear existing local students before initializing
    students = [];
    nextId = 1; // Reset nextId if you rely on it for non-DB IDs

    existingRows.forEach(row => {
        const studentIdAttr = row.dataset.studentId;
        if (studentIdAttr) {
             const studentId = parseInt(studentIdAttr);
             const cells = row.cells;
             const statusText = cells[5].textContent.trim();
             const status = statusText.toLowerCase().includes('online') ? 'online' : 'offline';

             // Assuming role might be available as a data attribute or needs fetching.
             // For now, let's add a placeholder or default.
             // If role comes from the server, ensure it's included in the initial PHP loop.
             const role = row.dataset.role || 'student'; // Example: read from data-role attribute or default

             const student = {
                 id: studentId,
                 group_name: cells[1].textContent.trim(), // Use group_name
                 name: cells[2].textContent.trim(),
                 gender: cells[3].textContent.trim(),
                 birthday: cells[4].textContent.trim(), // Keep DD.MM.YYYY format locally
                 status: status,
                 role: role // Store role
             };
             students.push(student);
             // Update nextId if needed to avoid collisions if mixing DB and local IDs
             if (studentId >= nextId) {
                 nextId = studentId + 1;
             }
        } else {
             console.warn("Row found without data-student-id:", row);
             // Optionally handle rows without IDs if necessary
        }
    });
     console.log("Initialized local students:", students);
}

window.addEventListener('DOMContentLoaded', initializeExistingRows);