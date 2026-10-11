document.addEventListener("DOMContentLoaded", () => {
    const forms = document.querySelectorAll("form[data-validate]");

    forms.forEach((form) => {
        form.addEventListener("submit", (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
            }
        });
    });

    const dateInput = document.querySelector("#visit_date");

    if (dateInput) {
        const today = new Date();
        const localDate = [
            today.getFullYear(),
            String(today.getMonth() + 1).padStart(2, "0"),
            String(today.getDate()).padStart(2, "0")
        ].join("-");

        dateInput.min = localDate;
    }
});