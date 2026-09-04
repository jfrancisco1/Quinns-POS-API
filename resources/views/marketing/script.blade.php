const form = document.getElementById('register-form');
const submitBtn = document.getElementById('submit-btn');
const message = document.getElementById('form-message');

function clearErrors() {
    form.querySelectorAll('.field').forEach((field) => {
        field.classList.remove('has-error');
        field.querySelector('.field-error').textContent = '';
    });
    message.className = 'form-message';
    message.textContent = '';
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating your account…';

    const payload = Object.fromEntries(new FormData(form).entries());
    Object.keys(payload).forEach((key) => {
        if (payload[key] === '') delete payload[key];
    });

    try {
        const response = await fetch('/api/v1/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (response.status === 422 && data.errors) {
            Object.entries(data.errors).forEach(([field, errors]) => {
                const fieldEl = form.querySelector(`[data-field="${field}"]`);
                if (fieldEl) {
                    fieldEl.classList.add('has-error');
                    fieldEl.querySelector('.field-error').textContent = errors[0];
                }
            });
            message.className = 'form-message error';
            message.textContent = 'Please fix the errors above and try again.';
            return;
        }

        if (!response.ok) {
            message.className = 'form-message error';
            message.textContent = data.message || 'Something went wrong. Please try again.';
            return;
        }

        form.reset();
        message.className = 'form-message success';
        message.textContent = `You're all set! We've emailed login details to ${payload.email}.`;
    } catch (err) {
        message.className = 'form-message error';
        message.textContent = 'Could not reach the server. Please check your connection and try again.';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Start free trial';
    }
});
