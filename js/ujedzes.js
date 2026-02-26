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

    // Számláló / timer – startTime sessionStorage-ban is (frissítés ellen)
    const STORAGE_KEY = "gymlog_edzes_start";
    let timerInterval = null;
    let startTime = null;

    function formatIdo(mp) {
        const m = Math.floor(mp / 60);
        const s = mp % 60;
        return String(m).padStart(2, "0") + ":" + String(s).padStart(2, "0");
    }

    function getStartTime() {
        if (startTime) return startTime;
        const saved = sessionStorage.getItem(STORAGE_KEY);
        return saved ? parseInt(saved, 10) : null;
    }

    function getElteltMasodperc() {
        const start = getStartTime();
        return start ? Math.max(1, Math.floor((Date.now() - start) / 1000)) : 0;
    }

    function timerIndit() {
        if (timerInterval) return;
        startTime = Date.now();
        sessionStorage.setItem(STORAGE_KEY, String(startTime));
        elemek.idotartamKijelzo.textContent = formatIdo(0);
        timerInterval = setInterval(() => {
            elemek.idotartamKijelzo.textContent = formatIdo(getElteltMasodperc());
        }, 1000);
        elemek.inditGomb.disabled = true;
        elemek.befejezGomb.disabled = false;
    }

    function timerLeallit() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        sessionStorage.removeItem(STORAGE_KEY);
        elemek.inditGomb.disabled = false;
        elemek.befejezGomb.disabled = true;
    }

    // Ha van mentett start (pl. frissítés után), indítsuk a timert
    function timerVisszaallit() {
        const saved = sessionStorage.getItem(STORAGE_KEY);
        if (saved && elemek.valasztottWrap.querySelectorAll(".edzes-blokk").length > 0) {
            startTime = parseInt(saved, 10);
            elemek.inditGomb.disabled = true;
            elemek.befejezGomb.disabled = false;
            if (!timerInterval) {
                timerInterval = setInterval(() => {
                    elemek.idotartamKijelzo.textContent = formatIdo(getElteltMasodperc());
                }, 1000);
            }
            elemek.idotartamKijelzo.textContent = formatIdo(getElteltMasodperc());
        }
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
    function szetSorLetrehozasa(rep = 8, suly = 0, index = 1, kesz = false) {
        const div = document.createElement("div");
        div.className = "szet-sor" + (kesz ? " kesz" : "");
        div.innerHTML = `
            <button type="button" class="szet-pipa" title="Befejezve" aria-label="Szett befejezve">✓</button>
            <span class="szet-cimke">${index}.</span>
            <div class="szet-mezo">
                <span class="szet-cimke-mezo">Ismétlés</span>
                <input type="number" class="rep-input" min="1" max="99" value="${rep}">
            </div>
            <div class="szet-mezo">
                <span class="szet-cimke-mezo">Súly (kg)</span>
                <input type="number" class="suly-input" min="0" max="500" value="${suly}">
            </div>
            <button type="button" class="szet-torles" title="Szett törlése">−</button>
        `;
        if (kesz) div.setAttribute("data-kesz", "1");
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
            lista.appendChild(szetSorLetrehozasa(sz.rep ?? 8, sz.suly ?? 0, i + 1, !!sz.kesz));
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
        if (elemek.valasztottWrap.querySelectorAll(".edzes-blokk").length === 1) {
            timerIndit();
        }
        panelCsuk();
    });

    // Pipázás esemény
    elemek.valasztottWrap.addEventListener("click", (e) => {
        if (e.target.classList.contains("szet-pipa")) {
            const sor = e.target.closest(".szet-sor");
            if (sor) {
                const kesz = sor.classList.toggle("kesz");
                sor.setAttribute("data-kesz", kesz ? "1" : "0");
            }
            return;
        }
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
                lista.appendChild(szetSorLetrehozasa(rep, suly, lista.children.length + 1, false));
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
    function sorokOsszegyujtese(csakPipalte = false) {
        let result = Array.from(elemek.valasztottWrap.querySelectorAll(".edzes-blokk")).map((blokk) => {
            const nev = blokk.querySelector(".gyakorlat-nev")?.textContent.trim() || "";
            const szetSorok = blokk.querySelectorAll(".szettek-lista .szet-sor");
            const szettek = Array.from(szetSorok).map((s) => ({
                rep: Number(s.querySelector(".rep-input")?.value) || 0,
                suly: Number(s.querySelector(".suly-input")?.value) || 0,
                kesz: s.classList.contains("kesz")
            }));
            return { nev, szettek };
        });
        if (csakPipalte) {
            result = result.map((s) => ({
                nev: s.nev,
                szettek: s.szettek.filter((sz) => sz.kesz)
            })).filter((s) => s.szettek.length > 0);
        }
        return result;
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

        const sorok = sorokOsszegyujtese(true);
        if (sorok.length === 0) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Pipáld ki a befejezett szetteket a mentéshez!";
            return;
        }
        const edzesNev = document.getElementById("edzesNev")?.value.trim() || "";
        if (!edzesNev) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Adj nevet az edzésnek!";
            return;
        }

        const idotartamMasodperc = getElteltMasodperc();
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
                    idotartam: idotartamMasodperc
                })
            });

            const data = await response.json();

            if (data?.siker && data?.redirect) {
                if (idotartamMasodperc === 0) {
                    elemek.hiba.style.color = "orange";
                    elemek.hiba.textContent = "Mentve. Következő alkalommal kattints az „Indít” gombra az edzés kezdetekor az időméréshez.";
                    setTimeout(() => { window.location.href = data.redirect; }, 2500);
                } else {
                    window.location.href = data.redirect;
                }
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
            elemek.hiba.style.color = data?.siker ? "green" : "red";
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
            if (sorok.length > 0) timerIndit();
        }
    }

    tervBetoltese();
    frissitDarab();
    timerVisszaallit();
});

    