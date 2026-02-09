document.addEventListener("DOMContentLoaded", () => {
    const ujGyakGomb = document.getElementById("ujGyakorlatGomb");
    const panel = document.getElementById("gyakorlatPanel");
    const panelZar = document.getElementById("panelZar");
    const panelLista = document.getElementById("gyakorlatListaOldal");
    const keresInput = document.getElementById("gyakorlatKereses");
    const valasztottWrap = document.getElementById("valasztottGyakorlatok");
    const hiba = document.getElementById("hiba");
    const gyCount = document.getElementById("gyakorlatCount");
    const mentesGomb = document.getElementById("mentes");

    if (!ujGyakGomb || !panel || !panelZar || !panelLista || !valasztottWrap || !hiba || !gyCount || !mentesGomb) {
        return;
    }

    function frissitDarab() {
        const db = valasztottWrap.querySelectorAll(".edzes-sor").length;
        gyCount.textContent = db + " gyakorlat";
        if (db === 0 && !valasztottWrap.querySelector(".ures-info")) {
            const p = document.createElement("p");
            p.className = "ures-info";
            p.textContent = "Még nem adtál hozzá gyakorlatot.";
            valasztottWrap.appendChild(p);
        }
    }

    function panelNyit() {
        panel.classList.add("open");
    }

    function panelCsuk() {
        panel.classList.remove("open");
    }

    ujGyakGomb.addEventListener("click", () => {
        panelNyit();
    });

    panelZar.addEventListener("click", () => {
        panelCsuk();
    });

    // Gyakorlat hozzáadása kattintásra (oldalsó panel)
    panelLista.addEventListener("click", (e) => {
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;
        if (!target.classList.contains("gyakorlat-item")) return;

        const nev = target.getAttribute("data-nev") || target.textContent.trim();
        if (!nev) return;

        // első hozzáadásnál távolítsuk el az üres szöveget
        const ures = valasztottWrap.querySelector(".ures-info");
        if (ures) {
            ures.remove();
        }

        const sor = document.createElement("div");
        sor.className = "edzes-sor";
        sor.innerHTML = `
            <span class="gyakorlat-nev">${nev}</span>
            <label>Set:
                <input type="number" class="set-input" min="1" max="10" value="3">
            </label>
            <label>Rep:
                <input type="number" class="rep-input" min="1" max="30" value="8">
            </label>
            <label>Súly (kg):
                <input type="number" class="suly-input" min="0" max="500" value="0">
            </label>
            <button type="button" class="sor-torles">✕</button>
        `;
        valasztottWrap.appendChild(sor);

        frissitDarab();
        panelCsuk();
    });

    // Sor törlése és darabszám frissítése
    valasztottWrap.addEventListener("click", (e) => {
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;
        if (!target.classList.contains("sor-torles")) return;

        const sor = target.closest(".edzes-sor");
        if (sor) {
            sor.remove();
            frissitDarab();
        }
    });

    // Kereső a panelen
    keresInput.addEventListener("input", () => {
        const q = keresInput.value.toLowerCase().trim();
        const elemek = panelLista.querySelectorAll(".gyakorlat-item");
        elemek.forEach((btn) => {
            const text = btn.textContent.toLowerCase();
            btn.style.display = text.includes(q) ? "block" : "none";
        });
    });

    // Mentés: validáció + elküldés a szervernek
    mentesGomb.addEventListener("click", async () => {
        hiba.style.color = "red";

        const db = valasztottWrap.querySelectorAll(".edzes-sor").length;
        if (db === 0) {
            hiba.textContent = "Adj hozzá legalább egy gyakorlatot!";
            return;
        }

        const edzesNevInput = document.getElementById("edzesNev");
        const edzesNev = edzesNevInput ? edzesNevInput.value.trim() : "";

        if (!edzesNev) {
            hiba.textContent = "Adj nevet az edzésnek!";
            return;
        }

        const sorok = Array.from(valasztottWrap.querySelectorAll(".edzes-sor")).map((sor) => {
            const nevElem = sor.querySelector(".gyakorlat-nev");
            const setInput = sor.querySelector(".set-input");
            const repInput = sor.querySelector(".rep-input");
            const sulyInput = sor.querySelector(".suly-input");

            return {
                nev: nevElem ? nevElem.textContent.trim() : "",
                set: setInput ? Number(setInput.value) || 0 : 0,
                rep: repInput ? Number(repInput.value) || 0 : 0,
                suly: sulyInput ? Number(sulyInput.value) || 0 : 0,
            };
        });

        try {
            hiba.style.color = "white";
            hiba.textContent = "Mentés folyamatban...";

            const response = await fetch("mentes_edzesterv.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    nev: edzesNev,
                    sorok: sorok,
                }),
            });

            const data = await response.json();

            if (data && data.siker) {
                hiba.style.color = "lightgreen";
                hiba.textContent = data.uzenet || "Edzésterv sikeresen elmentve.";
            } else {
                hiba.style.color = "red";
                hiba.textContent = (data && data.uzenet) ? data.uzenet : "Hiba történt a mentés közben.";
            }
        } catch (e) {
            console.error(e);
            hiba.style.color = "red";
            hiba.textContent = "Nem sikerült kapcsolódni a szerverhez.";
        }
    });

    // Terv betöltése, ha van URL paraméter
    function tervBetoltese() {
        if (!window.tervAdatok) {
            return;
        }

        const adatok = window.tervAdatok;
        const edzesNevInput = document.getElementById("edzesNev");
        
        if (edzesNevInput && adatok.nev) {
            edzesNevInput.value = adatok.nev;
        }

        if (adatok.tartalom && Array.isArray(adatok.tartalom) && adatok.tartalom.length > 0) {
            // Távolítsuk el az üres szöveget
            const ures = valasztottWrap.querySelector(".ures-info");
            if (ures) {
                ures.remove();
            }

            // Töröljük a meglévő sorokat (ha vannak)
            valasztottWrap.querySelectorAll(".edzes-sor").forEach(sor => sor.remove());

            // Hozzáadjuk az új sorokat
            adatok.tartalom.forEach((sor) => {
                const nev = sor.nev || "";
                const set = sor.set || 3;
                const rep = sor.rep || 8;
                const suly = sor.suly || 0;

                if (!nev) return;

                const sorElem = document.createElement("div");
                sorElem.className = "edzes-sor";
                sorElem.innerHTML = `
                    <span class="gyakorlat-nev">${nev}</span>
                    <label>Set:
                        <input type="number" class="set-input" min="1" max="10" value="${set}">
                    </label>
                    <label>Rep:
                        <input type="number" class="rep-input" min="1" max="30" value="${rep}">
                    </label>
                    <label>Súly (kg):
                        <input type="number" class="suly-input" min="0" max="500" value="${suly}">
                    </label>
                    <button type="button" class="sor-torles">✕</button>
                `;
                valasztottWrap.appendChild(sorElem);
            });

            frissitDarab();
        }
    }

    // Betöltjük a tervet, ha van
    tervBetoltese();

    frissitDarab();
});

    