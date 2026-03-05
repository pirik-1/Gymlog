document.addEventListener("DOMContentLoaded", () => {
    const gomb = document.getElementById("baratJonelolGomb");
    const uzenet = document.getElementById("baratAllapotUzenet");
    if (!gomb) return;

    gomb.addEventListener("click", async () => {
        const userId = gomb.getAttribute("data-user-id");
        if (!userId) return;
        gomb.disabled = true;

        try {
            const response = await fetch("barat_jonelol.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ user_id: parseInt(userId, 10) })
            });
            const data = await response.json();

            if (uzenet) {
                uzenet.style.color = data.siker ? "green" : "red";
                uzenet.textContent = data.uzenet || "";
            }
            if (data.siker) {
                gomb.textContent = "Kérelem küldve";
            } else {
                gomb.disabled = false;
            }
        } catch (e) {
            if (uzenet) uzenet.textContent = "Hiba történt.";
            gomb.disabled = false;
        }
    });
});
