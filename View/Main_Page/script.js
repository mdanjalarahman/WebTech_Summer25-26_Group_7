document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent normal form submission

    // Get values from inputs
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const messageDiv = document.getElementById('message');

    // Prepare data to send (JSON format)
    const formData = {
        username: username,
        password: password
    };

    // Send AJAX request using fetch
    fetch('login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
        .then(response => response.json()) // Parse JSON response from PHP
        .then(data => {
            if (data.status === 'success') {
                messageDiv.style.color = 'green';
                messageDiv.innerText = "Login Successful! Redirecting...";
                setTimeout(() => {
                    window.location.href = data.redirectUrl; // Redirect to dashboard
                }, 1000);
            } else {
                messageDiv.style.color = 'red';
                messageDiv.innerText = data.message;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.innerText = "An error occurred.";
        });
});