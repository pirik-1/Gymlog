function getCheckedValue(name) {
    const el = document.querySelector(`input[name="${name}"]:checked`);
    return el ? el.value : null;
}

function getNumber(id) {
    const v = document.getElementById(id).value;
    if (v === "") return null;
    return Number(v);
}

const btn = document.getElementById("mentes");
const hiba = document.getElementById("hiba");

btn.addEventListener("click", () => {

    const nem = getCheckedValue("nem");
    const cel = getCheckedValue("cel");

    const eletkor = getNumber("eletkor");
    const magassag = getNumber("magassag");
    const tomeg = getNumber("tomeg");

    // Ellenőrzések
    if (!nem) {
        hiba.style.color = "red";
        hiba.innerText = "Válaszd ki a nemet!";
        return;
    }
    if (!cel) {
        hiba.style.color = "red";
        hiba.innerText = "Válaszd ki a célt!";
        return;
    }
    if (!eletkor || eletkor < 1) {
        hiba.style.color = "red";
        hiba.innerText = "Adj meg érvényes életkort!";
        return;
    }
    if (!magassag || magassag < 50) {
        hiba.style.color = "red";
        hiba.innerText = "Adj meg érvényes magasságot!";
        return;
    }
    if (!tomeg || tomeg < 20) {
        hiba.style.color = "red";
        hiba.innerText = "Adj meg érvényes testsúlyt!";
        return;
    }

    // BMR
    let bmr;
    if (nem === "ferfi") {
        bmr = 10 * tomeg + 6.25 * magassag - 5 * eletkor + 5;
    } else {
        bmr = 10 * tomeg + 6.25 * magassag - 5 * eletkor - 161;
    }

    // Terhesség egyszerű korrekció
    if (nem === "no_allapotos") {
        bmr += 300;
    }

    // Alap aktivitás (közepes)
    const tdee = bmr * 1.55;

    // Cél szerinti kalória
    let celKaloria = tdee;
    let celNev = "Szintentartás";

    if (cel === "fogyas") {
        celKaloria = tdee - 400;
        celNev = "Fogyás";
    } else if (cel === "tomegnoveles") {
        celKaloria = tdee + 300;
        celNev = "Tömegnövelés";
    }

    // Eredmény
    hiba.style.color = "white";
    hiba.innerHTML = `
        <b>Eredmény</b><br>
        Napi szükséglet (TDEE): <b>${Math.round(tdee)}</b> kcal/nap<br>
        Ajánlott bevitel (${celNev}): <b>${Math.round(celKaloria)}</b> kcal/nap
    `;
});
