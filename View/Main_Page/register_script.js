document.getElementById('registerForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const phone = document.getElementById('phone').value;
    const emergency_contact = document.getElementById('emergency_contact').value;
    const nid = document.getElementById('nid').value;
    const occupation = document.getElementById('occupation').value;
    const messageDiv = document.getElementById('message');

    // Basic Validation
    if (username.length < 3 || password.length < 3) {
        messageDiv.style.color = "red";
        messageDiv.innerText = "Username and Password must be at least 3 characters.";
        return;
    }

    const formData = {
        username: username,
        password: password,
        phone: phone,
        emergency_contact: emergency_contact,
        nid: nid,
        occupation: occupation
    };

    fetch('register_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                messageDiv.style.color = "green";
                messageDiv.innerText = data.message;
                setTimeout(() => {
                    window.location.href = "index.php"; // Redirect to login
                }, 2000);
            } else {
                messageDiv.style.color = "red";
                messageDiv.innerText = data.message;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.innerText = "An error occurred.";
        });
});