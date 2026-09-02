window.addEventListener("pageshow", function (event) {
    var historyTraversal = event.persisted ||
        (typeof window.performance != "undefined" &&
            window.performance.navigation.type === 2);
    if (historyTraversal) {
        window.location.reload();
    }
});

function updateRole(userId, newRole) {
    if (!newRole) return;

    if (confirm("Are you sure you want to change this user's role?")) {
        fetch('../../Controller/update_role.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, new_role: newRole })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            });
    }
}

function deleteComplaint(compId) {
    if (confirm("Mark this complaint as resolved and remove it?")) {
        fetch('../../Controller/delete_complaint.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: compId })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                }
            });
    }
}

function postNotice() {
    const title = document.getElementById('noticeTitle').value;
    const message = document.getElementById('noticeMessage').value;
    const msgDiv = document.getElementById('noticeMsg');

    if (!title || !message) {
        msgDiv.style.color = "red"; msgDiv.innerText = "Fill all fields"; return;
    }

    fetch('../../Controller/add_notice.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: title, message: message })
    })
        .then(res => res.json())
        .then(data => {
            msgDiv.style.color = data.status === 'success' ? 'green' : 'red';
            msgDiv.innerText = data.message;
            if (data.status === 'success') setTimeout(() => location.reload(), 1000);
        });
}

function deleteUser(userId) {
    if (confirm("WARNING: Are you sure you want to delete this user? This cannot be undone!")) {
        fetch('../../Controller/delete_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: userId })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            });
    }
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
                            item.className = 'notice-item';
                            item.style.backgroundColor = '#f9f9f9';
                            item.innerHTML = `
                            <h4 style='margin:0 0 5px 0; color:#555;'>${notice.title}</h4>
                            <p style='margin:5px 0; color:#777;'>${notice.message}</p>
                            <div class='notice-date'>Posted: ${notice.created_at}</div>
                        `;
                            list.appendChild(item);
                        });
                    } else {
                        list.innerHTML = "<p style='padding:10px; color:#666;'>No archived notices found.</p>";
                    }
                    container.style.display = 'block';
                } else {
                    alert('Error fetching archive');
                }
            });
    } else {
        container.style.display = 'none';
    }
}
function deleteNotice(noticeId) {
    if (confirm("Are you sure you want to delete this notice?")) {
        fetch('../../Controller/delete_notice.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: noticeId })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            });
    }
}
function openEditModal(id, name, phone, emg, nid, occ) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_phone').value = (phone !== 'null') ? phone : '';
    document.getElementById('edit_emg').value = (emg !== 'null') ? emg : '';
    document.getElementById('edit_nid').value = (nid !== 'null') ? nid : '';
    document.getElementById('edit_occ').value = (occ !== 'null') ? occ : '';
    document.getElementById('editUserModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editUserModal').style.display = 'none';
}

function saveUserDetails() {
    const id = document.getElementById('edit_user_id').value;
    const phone = document.getElementById('edit_phone').value;
    const emg = document.getElementById('edit_emg').value;
    const nid = document.getElementById('edit_nid').value;
    const occ = document.getElementById('edit_occ').value;

    fetch('../../Controller/update_user_details.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, phone: phone, emergency_contact: emg, nid: nid, occupation: occ })
    })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') location.reload();
        });
}
