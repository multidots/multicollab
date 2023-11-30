document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("cf_requestAccessForm").addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Get the email input element
        const emailInput = document.getElementById("cf_request_access_email");
        // Get the email value
        const emailValue = emailInput.value.trim(); // Trim whitespace

        // Validate the email address
        if (!emailValue || !isValidEmail(emailValue)) {
            const validationMessage = document.querySelector(".cf_access_validation");
            validationMessage.style.display = "block";
            return; // Prevent form submission
        }

        // Hide the validation error message if it was previously shown
        const validationMessage = document.querySelector(".cf_access_validation");
        validationMessage.style.display = "none";

        // Serialize the form data
        const formData = new FormData(this);

        // Send an AJAX request to the server
        fetch(requestAccess.action_url, {
            method: 'POST',
            body: formData,
        })
        .then(response => response.text())
        .then(data => {
            const jsonResponse = JSON.parse(data);
            const message       = jsonResponse.data.msg;

            const authorizationStatus = document.querySelector(".cf_access_authorization_status");
            authorizationStatus.textContent = message;
            authorizationStatus.style.display = "block";
            var authorizationStatusElement = document.querySelector('.cf_access_authorization_status');
            if('success' === jsonResponse.data.status ) {
                authorizationStatusElement.classList.remove('failed');
                authorizationStatusElement.classList.add('success');
            } else {
                authorizationStatusElement.classList.remove('success');
                authorizationStatusElement.classList.add('failed');
            }
            // Set a timeout to hide the message after 3 seconds
            setTimeout(function() {
                authorizationStatus.textContent = "";
                if('success' === jsonResponse.data.status ) {
                    const emailInput = document.getElementById('cf_request_access_email');
                    emailInput.value = '';
                }
            }, 3000);

        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});