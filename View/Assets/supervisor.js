

async function addExpense() {
    const desc = document.getElementById('exp_desc').value;
    const amount = document.getElementById('exp_amount').value;
    const category = document.getElementById('exp_category').value;
    const date = document.getElementById('exp_date').value;
    const msg = document.getElementById('exp_msg');

    if (!desc || !amount || !date) {
        msg.textContent = "Please fill all fields.";
        msg.style.color = "red";
        return;
    }

    try {
        const res = await fetch('../../Controller/add_expense.php', {
            method: 'POST',
            body: JSON.stringify({ description: desc, amount: amount, category: category, date: date })
        });
        const data = await res.json();
        if (data.status === 'success') {
            msg.textContent = data.message;
            msg.style.color = "green";
            setTimeout(() => location.reload(), 1000);
        } else {
            msg.textContent = data.message;
            msg.style.color = "red";
        }
    } catch (err) {
        console.error(err);
    }
}


async function addDeposit() {
    const name = document.getElementById('dep_name').value;
    const amount = document.getElementById('dep_amount').value;
    const date = document.getElementById('dep_date').value;
    const msg = document.getElementById('dep_msg');


    if (!name || !amount || !date) {
        msg.textContent = "Please fill all fields.";
        msg.style.color = "red";
        return;
    }

    try {
        const res = await fetch('../../Controller/add_deposit.php', {
            method: 'POST',
            body: JSON.stringify({ resident_name: name, amount: amount, date: date })
        });
        const data = await res.json();
        if (data.status === 'success') {
            msg.textContent = data.message;
            msg.style.color = "green";
            setTimeout(() => location.reload(), 1000);
        } else {
            msg.textContent = data.message;
            msg.style.color = "red";
        }
    } catch (err) {
        console.error(err);
    }
}


// --- EDIT EXPENSE LOGIC ---
function openEditExpenseModal(id, date, desc, amount, category) {
    document.getElementById('edit_exp_id').value = id;
    document.getElementById('edit_exp_desc').value = desc;
    document.getElementById('edit_exp_amount').value = amount;
    document.getElementById('edit_exp_category').value = category;
    document.getElementById('edit_exp_date').value = date;

    document.getElementById('editExpenseModal').style.display = 'flex';
}

function closeEditExpenseModal() {
    document.getElementById('editExpenseModal').style.display = 'none';
}

async function updateExpense() {
    const id = document.getElementById('edit_exp_id').value;
    const desc = document.getElementById('edit_exp_desc').value;
    const amount = document.getElementById('edit_exp_amount').value;
    const category = document.getElementById('edit_exp_category').value;
    const date = document.getElementById('edit_exp_date').value;

    if (!desc || !amount || !date) {
        alert("Please fill all fields");
        return;
    }

    try {
        const res = await fetch('../../Controller/update_expense.php', {
            method: 'POST',
            body: JSON.stringify({ id, description: desc, amount: amount, category: category, date: date })
        });
        const data = await res.json();
        if (data.status === 'success') {
            alert("Expense updated!");
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    } catch (err) {
        console.error(err);
        alert("Request Failed");
    }
}

// --- EDIT DEPOSIT LOGIC ---
function openEditDepositModal(id, date, resident, amount) {
    document.getElementById('edit_dep_id').value = id;
    document.getElementById('edit_dep_date').value = date;
    document.getElementById('edit_dep_name').value = resident; // Ensure options value matches resident name
    document.getElementById('edit_dep_amount').value = amount;

    document.getElementById('editDepositModal').style.display = 'flex';
}

function closeEditDepositModal() {
    document.getElementById('editDepositModal').style.display = 'none';
}

async function updateDeposit() {
    const id = document.getElementById('edit_dep_id').value;
    const resident = document.getElementById('edit_dep_name').value;
    const amount = document.getElementById('edit_dep_amount').value;
    const date = document.getElementById('edit_dep_date').value;

    if (!resident || !amount || !date) {
        alert("Please fill all fields");
        return;
    }

    try {
        const res = await fetch('../../Controller/update_deposit.php', {
            method: 'POST',
            body: JSON.stringify({ id, resident_name: resident, amount: amount, date: date })
        });
        const data = await res.json();
        if (data.status === 'success') {
            alert("Deposit updated!");
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    } catch (err) {
        console.error(err);
        alert("Request Failed");
    }
}


// --- MANAGE RESIDENT MEALS LOGIC ---

async function loadResidentMeals() {
    const user = document.getElementById('meal_target_user').value;
    const container = document.getElementById('residentMealTable');

    if (!user) {
        container.innerHTML = "<p style='color:red;'>Please select a resident.</p>";
        return;
    }

    container.innerHTML = "Loading...";

    try {
        const res = await fetch(`../../Controller/fetch_meal_history.php?user=${encodeURIComponent(user)}`);
        const data = await res.json();

        if (data.length === 0) {
            container.innerHTML = "<p>No meal records found for this user.</p>";
            return;
        }

        let tableHtml = `
            <table>
                <tr>
                    <th>Date</th>
                    <th>Lunch</th>
                    <th>Dinner</th>
                    <th>Action</th>
                </tr>
        `;

        data.forEach(row => {
            tableHtml += `
                <tr>
                    <td>${row.date}</td>
                    <td>${row.lunch}</td>
                    <td>${row.dinner}</td>
                    <td>
                        <button class="btn-sm" style='background-color:#ffc107; color:black;' 
                           onclick='openEditMealModal("${row.date}", "${row.lunch}", "${row.dinner}", "${user}")'>Edit</button>
                    </td>
                </tr>
            `;
        });

        tableHtml += "</table>";
        container.innerHTML = tableHtml;

    } catch (err) {
        console.error(err);
        container.innerHTML = "<p style='color:red;'>Error fetching data.</p>";
    }
}

function openEditMealModal(date, lunch, dinner, user) {
    document.getElementById('edit_meal_user').value = user;
    document.getElementById('edit_meal_date').value = date; // Stores ORIGINAL date

    document.getElementById('disp_meal_user').textContent = user;
    document.getElementById('edit_meal_new_date').value = date; // Sets picker to current date

    document.getElementById('edit_lunch_count').value = lunch;
    document.getElementById('edit_dinner_count').value = dinner;

    document.getElementById('editMealModal').style.display = 'flex';
}

function closeEditMealModal() {
    document.getElementById('editMealModal').style.display = 'none';
}

async function updateTargetMeal() {
    const user = document.getElementById('edit_meal_user').value;
    const original_date = document.getElementById('edit_meal_date').value; // OLD Date
    const new_date = document.getElementById('edit_meal_new_date').value; // NEW Date
    const lunch = document.getElementById('edit_lunch_count').value;
    const dinner = document.getElementById('edit_dinner_count').value;

    if (!new_date) {
        alert("Date is required");
        return;
    }

    try {
        const res = await fetch('../../Controller/update_meal.php', {
            method: 'POST',
            body: JSON.stringify({
                date: new_date,
                original_date: original_date,
                lunch: lunch,
                dinner: dinner,
                target_user: user // Sending target user for backend override
            })
        });
        const data = await res.json();
        if (data.status === 'success') {
            alert("Meal updated successfully!");
            closeEditMealModal();
            loadResidentMeals(); // Refresh table
        } else {
            alert("Error: " + data.message);
        }
    } catch (err) {
        console.error(err);
        alert("Request Failed");
    }
}


// --- PERSONAL DUTIES ---

async function updateMeal() {
    const date = document.getElementById('mealDate').value;
    const lunch = document.getElementById('lunchCount').value; // Get number input
    const dinner = document.getElementById('dinnerCount').value; // Get number input
    const msg = document.getElementById('mealMsg');

    if (!date) {
        msg.textContent = "Please select a date.";
        msg.style.color = "red";
        return;
    }

    try {
        const res = await fetch('../../Controller/update_meal.php', {
            method: 'POST',
            body: JSON.stringify({ date: date, lunch: lunch, dinner: dinner })
        });
        const data = await res.json();
        if (data.status === 'success') {
            msg.textContent = data.message;
            msg.style.color = "green";
            setTimeout(() => location.reload(), 1000);
        } else {
            msg.textContent = data.message;
            msg.style.color = "red";
        }
    } catch (err) {
        console.error(err);
    }
}

async function submitComplaint() {
    const text = document.getElementById('complaintText').value;
    const msg = document.getElementById('compMsg');

    if (!text) {
        msg.textContent = "Please write something.";
        msg.style.color = "red";
        return;
    }

    try {
        const res = await fetch('../../Controller/add_complaint.php', {
            method: 'POST',
            body: JSON.stringify({ message: text })
        });
        const data = await res.json();
        if (data.status === 'success') {
            msg.textContent = "Complaint Sent!";
            msg.style.color = "green";
            document.getElementById('complaintText').value = "";
        } else {
            msg.textContent = data.message;
        }
    } catch (err) {
        console.error(err);
    }
}

// utils

async function deleteEntry(id, type) {
    if (!confirm("Are you sure you want to delete this?")) return;

    try {
        const res = await fetch('../../Controller/delete_entry.php', {
            method: 'POST',
            body: JSON.stringify({ id: id, type: type })
        });
        const data = await res.json();
        if (data.status === 'success') {
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    } catch (err) {
        console.error(err);
    }
}

// notification
document.addEventListener("DOMContentLoaded", function () {
    updateNotificationBadge();
});

function updateNotificationBadge() {
    const badge = document.querySelector('.notification-badge');
    const items = document.querySelectorAll('.notification-item');
    const lastChecked = localStorage.getItem('last_notice_check_time') || "0";

    let unseenCount = 0;
    items.forEach(item => {
        if (item.getAttribute('data-time') > lastChecked) {
            unseenCount++;
        }
    });

    if (badge) {
        if (unseenCount > 0) {
            badge.innerText = unseenCount;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

function toggleNotifications() {
    const drop = document.getElementById("notificationDropdown");
    const badge = document.querySelector('.notification-badge');

    if (drop.style.display === "block") {
        drop.style.display = "none";
    } else {
        drop.style.display = "block";

        // Mark as seen
        const now = new Date();
        const formattedNow = now.getFullYear() + "-" +
            ("0" + (now.getMonth() + 1)).slice(-2) + "-" +
            ("0" + now.getDate()).slice(-2) + " " +
            ("0" + now.getHours()).slice(-2) + ":" +
            ("0" + now.getMinutes()).slice(-2) + ":" +
            ("0" + now.getSeconds()).slice(-2);

        localStorage.setItem('last_notice_check_time', formattedNow);

        if (badge) badge.style.display = 'none';
    }
}

// Close Dropdown when clicking outside
window.onclick = function (event) {
    if (!event.target.matches('.notification-btn') && !event.target.closest('.notification-btn')) {
        var dropdowns = document.getElementsByClassName("notification-dropdown");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.style.display === "block") {
                openDropdown.style.display = "none";
            }
        }
    }
}

// --- CLOSE PERIOD LOGIC ---
async function closePeriod() {
    if (!confirm("This will calculate all the data of current period.\nAre you sure you want to proceed?")) {
        return;
    }

    try {
        const res = await fetch('../../Controller/close_period.php');
        const data = await res.json();

        if (data.status === 'success') {
            alert(data.message);
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    } catch (err) {
        console.error(err);
        alert("Request Failed");
    }
}

// reset
async function resetPeriodData() {
    if (!confirm("RESET DATA. Please Confirm")) {
        return;
    }

    // Simple double check
    if (!confirm("Are you absolutely sure? This cannot be undone.")) {
        return;
    }

    try {
        const res = await fetch('../../Controller/reset_data.php');
        const data = await res.json();

        if (data.status === 'success') {
            alert(data.message);
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    } catch (err) {
        console.error(err);
        alert("Request Failed");
    }
}

// undo reset
async function undoReset() {
    if (!confirm("Restore data from the last Reset?")) {
        return;
    }

    try {
        const res = await fetch('../../Controller/undo_reset.php');
        const data = await res.json();

        if (data.status === 'success') {
            alert(data.message);
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    } catch (err) {
        console.error(err);
        alert("Request Failed");
    }
}

