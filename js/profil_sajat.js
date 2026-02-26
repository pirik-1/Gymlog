document.addEventListener("DOMContentLoaded", () => {
    const adatokMentes = document.getElementById("adatokMentes");
    const adatokUzenet = document.getElementById("adatokUzenet");
    const magassagInput = document.getElementById("magassagInput");
    const testsulyInput = document.getElementById("testsulyInput");
    const nemSelect = document.getElementById("nemSelect");

    if (adatokMentes) {
        adatokMentes.addEventListener("click", async () => {
            const magassag = magassagInput?.value.trim() || "";
            const testsuly = testsulyInput?.value.trim() || "";
            const nem = nemSelect?.value || "";
            try {
                const res = await fetch("profil_adatok_mentes.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ magassag, testsuly, nem })
                });
                const data = await res.json();
                adatokUzenet.textContent = data.uzenet || "";
                adatokUzenet.style.color = data.siker ? "green" : "red";
            } catch (e) {
                adatokUzenet.textContent = "Hiba a kapcsolatban.";
                adatokUzenet.style.color = "red";
            }
        });
    }

    const popup = document.getElementById("kaloriaPopup");
    const kalkGomb = document.getElementById("kaloriaKalkulatorGomb");
    const popupClose = popup?.querySelector(".popup-close");
    const kalkSzamit = document.getElementById("kalkSzamit");
    const kalkEredmeny = document.getElementById("kalkEredmeny");

    if (kalkGomb && popup) {
        kalkGomb.addEventListener("click", () => { popup.classList.add("open"); });
    }
    if (popupClose && popup) {
        popupClose.addEventListener("click", () => { popup.classList.remove("open"); });
    }
    if (popup) {
        popup.addEventListener("click", (e) => {
            if (e.target === popup) popup.classList.remove("open");
        });
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && popup.classList.contains("open")) popup.classList.remove("open");
        });
    }

    if (kalkSzamit && kalkEredmeny) {
        kalkSzamit.addEventListener("click", () => {
            const eletkor = parseInt(document.getElementById("kalkEletkor")?.value, 10);
            const magassag = parseInt(document.getElementById("kalkMagassag")?.value, 10);
            const tomeg = parseInt(document.getElementById("kalkTomeg")?.value, 10);
            const nem = document.getElementById("kalkNem")?.value;
            const cel = document.getElementById("kalkCel")?.value;

            if (!eletkor || eletkor < 10 || eletkor > 120) {
                kalkEredmeny.textContent = "Adj meg érvényes életkort (10-120)!";
                kalkEredmeny.style.color = "red";
                return;
            }
            if (!magassag || magassag < 50 || magassag > 250) {
                kalkEredmeny.textContent = "Adj meg érvényes magasságot (50-250 cm)!";
                kalkEredmeny.style.color = "red";
                return;
            }
            if (!tomeg || tomeg < 20 || tomeg > 300) {
                kalkEredmeny.textContent = "Adj meg érvényes testsúlyt (20-300 kg)!";
                kalkEredmeny.style.color = "red";
                return;
            }
            if (!nem || (nem !== "ferfi" && nem !== "no")) {
                kalkEredmeny.textContent = "Válaszd ki a nemet!";
                kalkEredmeny.style.color = "red";
                return;
            }

            let bmr = 10 * tomeg + 6.25 * magassag - 5 * eletkor + (nem === "ferfi" ? 5 : -161);
            const tdee = bmr * 1.55;
            let celKaloria = tdee;
            let celNev = "Súlyszinten tartás";
            if (cel === "fogyas") {
                celKaloria = tdee - 400;
                celNev = "Fogyás";
            } else if (cel === "tomegnoveles") {
                celKaloria = tdee + 300;
                celNev = "Tömegnövelés";
            }

            kalkEredmeny.style.color = "red";
            kalkEredmeny.innerHTML = `<b>Eredmény</b><br>Napi szükséglet (TDEE): <b>${Math.round(tdee)}</b> kcal/nap<br>Ajánlott bevitel (${celNev}): <b>${Math.round(celKaloria)}</b> kcal/nap`;
        });
    }
});
