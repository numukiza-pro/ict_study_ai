const chatForm = document.getElementById("chatForm");
const userMessage = document.getElementById("userMessage");
const chatMessages = document.getElementById("chatMessages");

chatForm.addEventListener("submit", async function (event) {

    event.preventDefault();

    const message = userMessage.value.trim();

    if (message === "") {
        return;
    }

    addMessage(message, "user");

    userMessage.value = "";

    addMessage("Thinking...", "ai");

    try {

        const response = await fetch("ask.php", {

            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                message: message
            })

        });

        const data = await response.json();

        removeLastMessage();

        if (data.success) {

            addMessage(
                data.answer,
                "ai"
            );

        } else {

            addMessage(
                data.message || "Something went wrong.",
                "ai"
            );
        }

    } catch (error) {

        removeLastMessage();

        addMessage(
            "Unable to connect to the AI service.",
            "ai"
        );
    }

});


function addMessage(message, type) {

    const messageDiv = document.createElement("div");

    messageDiv.classList.add(
        "message",
        type === "user"
            ? "user-message"
            : "ai-message"
    );

    messageDiv.innerHTML = `
        <p>${message}</p>
    `;

    chatMessages.appendChild(messageDiv);

    chatMessages.scrollTop =
        chatMessages.scrollHeight;
}


function removeLastMessage() {

    const messages =
        chatMessages.querySelectorAll(".message");

    if (messages.length > 0) {

        messages[messages.length - 1].remove();
    }
}