document.addEventListener("DOMContentLoaded", function() {
    const mutasdReg = document.getElementById("mutasdReg");
    const jelszoReg = document.getElementById("jelszoReg");
    const jelszoRegUjra = document.getElementById("jelszoRegUjra");
    const regForm = document.getElementById("regForm");
    const regHiba = document.getElementById("regHiba");

    if (mutasdReg && jelszoReg && jelszoRegUjra) {
        mutasdReg.addEventListener("mouseover", function() {
            jelszoReg.type = "text";
            jelszoRegUjra.type = "text";
        });
        mutasdReg.addEventListener("mouseout", function() {
            jelszoReg.type = "password";
            jelszoRegUjra.type = "password";
        });
    }

    function uzenet(str) {
        if (regHiba) {
            regHiba.textContent = str;
            regHiba.style.display = str ? "block" : "none";
        }
    }

    if (regForm && regHiba) {
        regForm.addEventListener("submit", function(e) {
            uzenet("");
            const nev = (document.getElementById("nev") && document.getElementById("nev").value || "").trim();
            const email = (document.getElementById("email") && document.getElementById("email").value || "").trim();
            const j1 = jelszoReg ? jelszoReg.value : "";
            const j2 = jelszoRegUjra ? jelszoRegUjra.value : "";

            if (!nev || !email || !j1 || !j2) {
                e.preventDefault();
                uzenet("Minden mező kitöltése kötelező.");
                return;
            }
            if (j1 !== j2) {
                e.preventDefault();
                uzenet("A két jelszó nem egyezik.");
                return;
            }
            if (j1.length < 8) {
                e.preventDefault();
                uzenet("A jelszónak legalább 8 karakter hosszúnak kell lennie.");
                return;
            }
            if (j1.length > 64) {
                e.preventDefault();
                uzenet("A jelszó legfeljebb 64 karakter hosszú lehet.");
                return;
            }
            if (!/[0-9]/.test(j1)) {
                e.preventDefault();
                uzenet("A jelszónak legalább egy számot kell tartalmaznia.");
                return;
            }
            if (!/[a-zA-Z]/.test(j1)) {
                e.preventDefault();
                uzenet("A jelszónak legalább egy betűt kell tartalmaznia.");
                return;
            }
        });
    }
});
