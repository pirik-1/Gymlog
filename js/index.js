const navToggle = document.getElementById("navToggle");
const navMenu = document.getElementById("navMenu");
const navBackdrop = document.getElementById("navBackdrop");

/* Bejegyzések keresése a főoldalon */
const bejegyzesKereso = document.getElementById("bejegyzesKereso");
if (bejegyzesKereso) {
    const kartyak = document.querySelectorAll(".poszt-kartya[data-kereso-szoveg]");
    const nincsEredmeny = document.getElementById("bejegyzesNincsEredmeny");
    bejegyzesKereso.addEventListener("input", () => {
        const keres = (bejegyzesKereso.value || "").trim().toLowerCase();
        let lathato = 0;
        kartyak.forEach(kartya => {
            const szoveg = (kartya.dataset.keresoSzoveg || "").toLowerCase();
            const egyezik = !keres || szoveg.includes(keres);
            kartya.style.display = egyezik ? "" : "none";
            if (egyezik) lathato++;
        });
        if (nincsEredmeny) nincsEredmeny.style.display = (keres && lathato === 0) ? "block" : "none";
    });
}

if (navToggle && navMenu) {
    function nyitMenut() {
        navMenu.classList.add("nav-open");
        if (navBackdrop) navBackdrop.classList.add("active");
    }
    function csukMenut() {
        navMenu.classList.remove("nav-open");
        if (navBackdrop) navBackdrop.classList.remove("active");
    }
    navToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        navMenu.classList.toggle("nav-open");
        if (navBackdrop) navBackdrop.classList.toggle("active");
    });
    if (navBackdrop) navBackdrop.addEventListener("click", csukMenut);
    navMenu.querySelectorAll("a").forEach(a => {
        a.addEventListener("click", () => { if (window.innerWidth <= 768) csukMenut(); });
    });
}

/* Komment hozzáadás és törlés */
document.querySelectorAll(".komment-uj-form").forEach(form => {
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const posztId = parseInt(form.dataset.posztId, 10);
        const input = form.querySelector('input[name="tartalom"]');
        const tartalom = (input && input.value) ? input.value.trim() : "";
        if (!tartalom) return;
        const btn = form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;
        try {
            const res = await fetch("poszt_komment_add.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ poszt_id: posztId, tartalom })
            });
            const data = await res.json();
            if (data.siker && data.komment) {
                const li = document.createElement("li");
                li.className = "komment-elem";
                li.dataset.kommentId = data.komment.id;
                let delBtn = "";
                if (window.gymlogAdmin) delBtn = '<button type="button" class="komment-torles-gomb" title="Törlés">✕</button>';
                li.innerHTML = `<span class="komment-szerzo">${escapeHtml(data.komment.felhasznaloNev)}</span>
                    <span class="komment-datum">${escapeHtml(data.komment.datum)}</span>${delBtn}
                    <span class="komment-tartalom">${escapeHtml(data.komment.tartalom)}</span>`;
                if (window.gymlogAdmin) {
                    li.querySelector(".komment-torles-gomb")?.addEventListener("click", function() {
                        torlesGombKattint(this);
                    });
                }
                const lista = form.closest(".poszt-kommentek").querySelector(".komment-lista");
                if (lista) lista.appendChild(li);
                if (input) input.value = "";
            } else {
                alert(data.uzenet || "Hiba a küldéskor.");
            }
        } catch (err) {
            alert("Hiba a kapcsolat során.");
        } finally {
            if (btn) btn.disabled = false;
        }
    });
});

async function torlesGombKattint(btn) {
        const li = btn.closest(".komment-elem");
        const kommentId = li ? parseInt(li.dataset.kommentId, 10) : 0;
        if (!kommentId || !confirm("Biztosan törölni szeretnéd a kommentet?")) return;
        btn.disabled = true;
        try {
            const res = await fetch("poszt_komment_torles.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ komment_id: kommentId })
            });
            const data = await res.json();
            if (data.siker && li) li.remove();
            else alert(data.uzenet || "Hiba a törléskor.");
        } catch (err) {
            alert("Hiba a kapcsolat során.");
        } finally {
            btn.disabled = false;
        }
}

document.querySelectorAll(".komment-torles-gomb").forEach(btn => {
    btn.addEventListener("click", () => torlesGombKattint(btn));
});

function escapeHtml(s) {
    if (!s) return "";
    const div = document.createElement("div");
    div.textContent = s;
    return div.innerHTML;
}