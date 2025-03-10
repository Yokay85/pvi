const table = document.getElementById("students");
const addButton = document.getElementById("add-btn");

let array = [];


function addStudent() {
    const tr = document.createElement("tr");

    tr.innerHTML = `
                    <tr>
                        <td><input type="checkbox" class="student-select" aria-label="Select student"></td>
                        <td>KN-21</td>
                        <td>John Smith</td>
                        <td>M</td>
                        <td>11.05.2004</td>
                        <td><span class="status-indicator online"></span> Online</td>
                        <td>
                            <button class="edit-btn" aria-label="Edit student">Edit</button>
                            <button class="delete-btn" aria-label="Delete student">Delete</button>
                        </td>
                    </tr>
                `;

    table.appendChild(tr);
}

addButton.addEventListener("click", addStudent);