
const inputs = document.querySelectorAll('.code-inputs input');

inputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
        const value = e.target.value;

        // Only allow digits
        if (!/^\d$/.test(value)) {
            e.target.value = '';
            return;
        }

        // Move to next input if not the last
        if (value && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace') {
            if (e.target.value === '' && index > 0) {
                inputs[index - 1].focus();
            }
        }
    });
});
