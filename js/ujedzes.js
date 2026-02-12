document.addEventListener("DOMContentLoaded", () => {
    const elemek = {
        ujGyakGomb: document.getElementById("ujGyakorlatGomb"),
        panel: document.getElementById("gyakorlatPanel"),
        panelZar: document.getElementById("panelZar"),
        panelLista: document.getElementById("gyakorlatListaOldal"),
        keresInput: document.getElementById("gyakorlatKereses"),
        valasztottWrap: document.getElementById("valasztottGyakorlatok"),
        hiba: document.getElementById("hiba"),
        gyCount: document.getElementById("gyakorlatCount"),
        mentesGomb: document.getElementById("mentes"),
        inditGomb: document.getElementById("inditGomb"),
        befejezGomb: document.getElementById("befejezGomb"),
        idotartamKijelzo: document.getElementById("idotartamKijelzo")
    };

    if (Object.values(elemek).some(e => !e)) {
        return;
    }

    // Számláló / timer
    let timerInterval = null;
    let elteltMasodperc = 0;

    function formatIdo(mp) {
        const m = Math.floor(mp / 60);
        const s = mp % 60;
        return String(m).padStart(2, "0") + ":" + String(s).padStart(2, "0");
    }

    function timerIndit() {
        if (timerInterval) return;
        elteltMasodperc = 0;
        elemek.idotartamKijelzo.textContent = formatIdo(0);
        timerInterval = setInterval(() => {
            elteltMasodperc++;
            elemek.idotartamKijelzo.textContent = formatIdo(elteltMasodperc);
        }, 1000);
        elemek.inditGomb.disabled = true;
        elemek.befejezGomb.disabled = false;
    }

    function timerLeallit() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        elemek.inditGomb.disabled = false;
        elemek.befejezGomb.disabled = true;
    }

    // Gyakorlat számláló frissítése
    function frissitDarab() {
        const db = elemek.valasztottWrap.querySelectorAll(".edzes-blokk").length;
        elemek.gyCount.textContent = db + " gyakorlat";
        if (db === 0 && !elemek.valasztottWrap.querySelector(".ures-info")) {
            const p = document.createElement("p");
            p.className = "ures-info";
            p.textContent = "Még nem adtál hozzá gyakorlatot.";
            elemek.valasztottWrap.appendChild(p);
        }
    }

    // Panel nyitás/zárás
    function panelNyit() { elemek.panel.classList.add("open"); }
    function panelCsuk() { elemek.panel.classList.remove("open"); }

    // Egy szett sor létrehozása
    function szetSorLetrehozasa(rep = 8, suly = 0, index = 1) {
        const div = document.createElement("div");
        div.className = "szet-sor";
        div.innerHTML = `
            <span class="szet-cimke">${index}.</span>
            <label>Ismétlés: <input type="number" class="rep-input" min="1" max="99" value="${rep}"></label>
            <label>Súly (kg): <input type="number" class="suly-input" min="0" max="500" value="${suly}"></label>
            <button type="button" class="szet-torles" title="Szett törlése">−</button>
        `;
        return div;
    }

    // Gyakorlat blokk létrehozása (név + szettek lista)
    function gyakorlatBlokkLetrehozasa(nev, szettek = [{ rep: 8, suly: 0 }]) {
        if (!Array.isArray(szettek) || szettek.length === 0) szettek = [{ rep: 8, suly: 0 }];
        const blokk = document.createElement("div");
        blokk.className = "edzes-blokk";
        blokk.innerHTML = `
            <div class="edzes-blokk-fej">
                <span class="gyakorlat-nev">${nev}</span>
                <div class="edzes-blokk-gombok">
                    <button type="button" class="szet-hozzaad" title="Szett hozzáadása">+</button>
                    <button type="button" class="sor-torles">✕</button>
                </div>
            </div>
            <div class="szettek-lista"></div>
        `;
        const lista = blokk.querySelector(".szettek-lista");
        szettek.forEach((sz, i) => {
            lista.appendChild(szetSorLetrehozasa(sz.rep ?? 8, sz.suly ?? 0, i + 1));
        });
        szetCimkekFrissit(lista);
        return blokk;
    }

    function szetCimkekFrissit(lista) {
        lista.querySelectorAll(".szet-sor").forEach((s, i) => {
            const cimke = s.querySelector(".szet-cimke");
            if (cimke) cimke.textContent = (i + 1) + ".";
            const torles = s.querySelector(".szet-torles");
            if (torles) torles.style.visibility = lista.children.length > 1 ? "visible" : "hidden";
        });
    }

    // Event listener-ek
    elemek.ujGyakGomb.addEventListener("click", panelNyit);
    elemek.panelZar.addEventListener("click", panelCsuk);

    // Gyakorlat hozzáadása – legutóbbi adatok betöltése ha van
    elemek.panelLista.addEventListener("click", async (e) => {
        if (!e.target.classList.contains("gyakorlat-item")) return;
        const nev = e.target.getAttribute("data-nev") || e.target.textContent.trim();
        if (!nev) return;

        const ures = elemek.valasztottWrap.querySelector(".ures-info");
        if (ures) ures.remove();

        let szettek = [{ rep: 8, suly: 0 }];
        if (!window.vendeg) {
            try {
                const response = await fetch("gyakorlat_utolso_adat.php?gyakorlat_nev=" + encodeURIComponent(nev));
                const data = await response.json();
                if (data?.siker && Array.isArray(data.szettek) && data.szettek.length > 0) {
                    szettek = data.szettek;
                }
            } catch (err) {
                /* marad az alapértelmezett */
            }
        }
        elemek.valasztottWrap.appendChild(gyakorlatBlokkLetrehozasa(nev, szettek));
        frissitDarab();
        panelCsuk();
    });

    // Delegált eseménykezelők: blokk törlése, szett hozzáadás/törlés
    elemek.valasztottWrap.addEventListener("click", (e) => {
        if (e.target.classList.contains("sor-torles")) {
            const blokk = e.target.closest(".edzes-blokk");
            if (blokk) {
                blokk.remove();
                frissitDarab();
            }
        } else if (e.target.classList.contains("szet-hozzaad")) {
            const blokk = e.target.closest(".edzes-blokk");
            const lista = blokk?.querySelector(".szettek-lista");
            if (lista) {
                const utolso = lista.querySelector(".szet-sor:last-child");
                const rep = utolso ? Number(utolso.querySelector(".rep-input")?.value) || 8 : 8;
                const suly = utolso ? Number(utolso.querySelector(".suly-input")?.value) || 0 : 0;
                lista.appendChild(szetSorLetrehozasa(rep, suly, lista.children.length + 1));
                szetCimkekFrissit(lista);
            }
        } else if (e.target.classList.contains("szet-torles")) {
            const sor = e.target.closest(".szet-sor");
            const lista = sor?.closest(".szettek-lista");
            if (lista && lista.children.length > 1) {
                sor.remove();
                szetCimkekFrissit(lista);
            }
        }
    });

    // Kereső
    elemek.keresInput.addEventListener("input", () => {
        const q = elemek.keresInput.value.toLowerCase().trim();
        elemek.panelLista.querySelectorAll(".gyakorlat-item").forEach((btn) => {
            btn.style.display = btn.textContent.toLowerCase().includes(q) ? "block" : "none";
        });
    });

    // Sorok adatainak összegyűjtése (új formátum: nev + szettek)
    function sorokOsszegyujtese() {
        return Array.from(elemek.valasztottWrap.querySelectorAll(".edzes-blokk")).map((blokk) => {
            const nev = blokk.querySelector(".gyakorlat-nev")?.textContent.trim() || "";
            const szetSorok = blokk.querySelectorAll(".szettek-lista .szet-sor");
            const szettek = Array.from(szetSorok).map((s) => ({
                rep: Number(s.querySelector(".rep-input")?.value) || 0,
                suly: Number(s.querySelector(".suly-input")?.value) || 0
            }));
            return { nev, szettek };
        });
    }

    // Indít gomb
    elemek.inditGomb.addEventListener("click", () => {
        const sorok = sorokOsszegyujtese();
        if (sorok.length === 0) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Adj hozzá legalább egy gyakorlatot az indításhoz!";
            return;
        }
        const edzesNev = document.getElementById("edzesNev")?.value.trim() || "";
        if (!edzesNev) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Adj nevet az edzésnek!";
            return;
        }
        elemek.hiba.textContent = "";
        timerIndit();
    });

    // Befejez gomb
    elemek.befejezGomb.addEventListener("click", async () => {
        if (window.vendeg) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Jelentkezz be a befejezéshez.";
            return;
        }

        const sorok = sorokOsszegyujtese();
        if (sorok.length === 0) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Adj hozzá legalább egy gyakorlatot!";
            return;
        }
        const edzesNev = document.getElementById("edzesNev")?.value.trim() || "";
        if (!edzesNev) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Adj nevet az edzésnek!";
            return;
        }

        timerLeallit();

        try {
            elemek.hiba.style.color = "white";
            elemek.hiba.textContent = "Mentés...";

            const response = await fetch("ujedzes_befejez.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    nev: edzesNev,
                    sorok: sorok,
                    idotartam: elteltMasodperc
                })
            });

            const data = await response.json();

            if (data?.siker && data?.redirect) {
                window.location.href = data.redirect;
            } else {
                elemek.hiba.style.color = "red";
                elemek.hiba.textContent = data?.uzenet || "Hiba történt.";
            }
        } catch (e) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Nem sikerült kapcsolódni a szerverhez.";
        }
    });

    // Mentés (tervként)
    elemek.mentesGomb.addEventListener("click", async () => {
        elemek.hiba.style.color = "red";

        if (window.vendeg) {
            elemek.hiba.textContent = "A mentéshez jelentkezz be.";
            return;
        }

        const sorok = sorokOsszegyujtese();
        if (sorok.length === 0) {
            elemek.hiba.textContent = "Adj hozzá legalább egy gyakorlatot!";
            return;
        }

        const edzesNev = document.getElementById("edzesNev")?.value.trim() || "";
        if (!edzesNev) {
            elemek.hiba.textContent = "Adj nevet az edzésnek!";
            return;
        }

        try {
            elemek.hiba.style.color = "white";
            elemek.hiba.textContent = "Mentés folyamatban...";

            const response = await fetch("mentes_edzesterv.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ nev: edzesNev, sorok: sorok })
            });

            const data = await response.json();
            elemek.hiba.style.color = data?.siker ? "lightgreen" : "red";
            elemek.hiba.textContent = data?.uzenet || (data?.siker ? "Edzésterv sikeresen elmentve." : "Hiba történt a mentés közben.");
        } catch (e) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Nem sikerült kapcsolódni a szerverhez.";
        }
    });

    // Terv betöltése (támogatja a régi és az új formátumot)
    function tervBetoltese() {
        if (!window.tervAdatok) return;

        const edzesNevInput = document.getElementById("edzesNev");
        if (edzesNevInput && window.tervAdatok.nev) {
            edzesNevInput.value = window.tervAdatok.nev;
        }

        const sorok = window.tervAdatok.tartalom;
        if (Array.isArray(sorok) && sorok.length > 0) {
            const ures = elemek.valasztottWrap.querySelector(".ures-info");
            if (ures) ures.remove();

            elemek.valasztottWrap.querySelectorAll(".edzes-blokk").forEach(b => b.remove());
            sorok.forEach(sor => {
                if (!sor.nev) return;
                let szettek;
                if (Array.isArray(sor.szettek) && sor.szettek.length > 0) {
                    szettek = sor.szettek;
                } else {
                    const set = sor.set || 3, rep = sor.rep || 8, suly = sor.suly || 0;
                    szettek = Array.from({ length: set }, () => ({ rep, suly }));
                }
                elemek.valasztottWrap.appendChild(gyakorlatBlokkLetrehozasa(sor.nev, szettek));
            });
            frissitDarab();
        }
    }

    tervBetoltese();
    frissitDarab();
});

    