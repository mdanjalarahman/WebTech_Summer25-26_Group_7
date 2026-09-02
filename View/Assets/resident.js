
window.addEventListener("pageshow", function (event) {
    var historyTraversal = event.persisted ||
        (typeof window.performance != "undefined" &&
            window.performance.navigation.type === 2);
    if (historyTraversal) {
        window.location.reload();
    }
});

// Notification Logic
document.addEventListener("DOMContentLoaded", function () {
    updateNotificationBadge();
});

function updateNotificationBadge() {
    const badge = document.querySelector('.notification-badge');
    const items = document.querySelectorAll('.notification-item');
    const lastChecked = localStorage.getItem('last_notice_check_time') || "0";

    let unseenCount = 0;
    items.forEach(item => {
        // SQL timestamp is YYYY-MM-DD HH:MM:SS. String comparison works nicely for this format.
        if (item.getAttribute('data-time') > lastChecked) {
            unseenCount++;
        }
    });

    if (badge) {
        if (unseenCount > 0) {
            badge.innerText = unseenCount;
            badge.style.display = 'flex'; // Ensure it's visible if there are new items
        } else {
            badge.style.display = 'none';
        }
    }
}

function toggleNotifications() {
    const dropdown = document.getElementById("notificationDropdown");
    const badge = document.querySelector('.notification-badge');

    if (dropdown.style.display === "block") {
        dropdown.style.display = "none";
    } else {
        dropdown.style.display = "block";

        // Mark all as seen by updating timestamp to Now
        const now = new Date();
        const formattedNow = now.getFullYear() + "-" +
            ("0" + (now.getMonth() + 1)).slice(-2) + "-" +
            ("0" + now.getDate()).slice(-2) + " " +
            ("0" + now.getHours()).slice(-2) + ":" +
            ("0" + now.getMinutes()).slice(-2) + ":" +
            ("0" + now.getSeconds()).slice(-2);

        localStorage.setItem('last_notice_check_time', formattedNow);

        // Hide badge immediately
        if (badge) badge.style.display = 'none';
    }
}

// Close dropdown if clicked outside
window.onclick = function (event) {
    if (!event.target.matches('.notification-btn') && !event.target.parentElement.matches('.notification-btn')) {
        var dropdowns = document.getElementsByClassName("notification-dropdown");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.style.display === "block") {
                openDropdown.style.display = "none";
            }
        }
    }
}

function updateMeal() {
    const date = document.getElementById('mealDate').value;
    // Get value from Number Inputs
    const lunch = document.getElementById('lunchCount').value;
    const dinner = document.getElementById('dinnerCount').value;
    const msg = document.getElementById('mealMsg');

    if (!date) {
        msg.style.color = "red"; msg.innerText = "Please select a date."; return;
    }

    fetch('../../Controller/update_meal.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ date: date, lunch: lunch, dinner: dinner })
    })
        .then(res => res.json())
        .then(data => {
            msg.style.color = data.status === 'success' ? 'green' : 'red';
            msg.innerText = data.message;
            if (data.status === 'success') setTimeout(() => location.reload(), 1000);
        });
}

function submitComplaint() {
    const message = document.getElementById('complaintText').value;
    const msg = document.getElementById('compMsg');

    if (!message) {
        msg.style.color = "red"; msg.innerText = "Please write a complaint."; return;
    }

    fetch('../../Controller/add_complaint.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: message })
    })
        .then(res => res.json())
        .then(data => {
            msg.style.color = data.status === 'success' ? 'green' : 'red';
            msg.innerText = data.message;
            if (data.status === 'success') document.getElementById('complaintText').value = '';
        });
}

function toggleArchive() {
    const container = document.getElementById('archiveContainer');
    const list = document.getElementById('archiveList');

    if (container.style.display === 'none') {
        fetch('../../Controller/fetch_archived_notices.php')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    list.innerHTML = '';
                    if (data.data.length > 0) {
                        data.data.forEach(notice => {
                            const item = document.createElement('div');
                            item.style.borderBottom = '1px solid #ddd';
                            item.style.padding = '8px 0';
                            item.innerHTML = `
                            <strong style='color:#555;'>${notice.title}</strong>
                            <p style='margin:2px 0; color:#666;'>${notice.message}</p>
                            <div style='font-size:0.8em; color:#888;'>Posted: ${notice.created_at}</div>
                        `;
                            list.appendChild(item);
                        });
                    } else {
                        list.innerHTML = "<p style='color:#666;'>No archived notices.</p>";
                    }
                    container.style.display = 'block';
                }
            });
    } else {
        container.style.display = 'none';
    }
}
