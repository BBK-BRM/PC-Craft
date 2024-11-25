document.addEventListener("DOMContentLoaded", function () {
    const buildForm = document.getElementById("build-form");

    // Function to load components dynamically
    function loadComponents(componentType) {
        fetch(`getComponents.php?type=${componentType}`)
            .then(response => response.json())
            .then(data => {
                const selectElement = document.getElementById(componentType);
                data.forEach(component => {
                    const option = document.createElement("option");
                    option.value = component.id;
                    option.textContent = component.name;
                    selectElement.appendChild(option);
                });
            });
    }

    // Load all component options
    ["cpu", "motherboard", "gpu", "ram", "psu", "storage", "case", "cooling"].forEach(loadComponents);

    // Handle form submission
    buildForm.addEventListener("submit", function (event) {
        event.preventDefault();
        const formData = new FormData(buildForm);
        const selectedComponents = {
            cpu: formData.get("cpu"),
            motherboard: formData.get("motherboard"),
            gpu: formData.get("gpu"),
            ram: formData.get("ram"),
            psu: formData.get("psu"),
            storage: formData.get("storage"),
            case: formData.get("case"),
            cooling: formData.get("cooling")
        };

        fetch("ac3_algorithm.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(selectedComponents)
        })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById("compatibility-result");
                if (data.compatible) {
                    resultDiv.textContent = "All selected components are compatible!";
                    resultDiv.style.color = "green";
                } else {
                    resultDiv.textContent = "Selected components are not compatible.";
                    resultDiv.style.color = "red";
                }
            });
    });
});
