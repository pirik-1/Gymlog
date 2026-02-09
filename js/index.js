// Ellenőrizzük, hogy létezik-e a menuBtn elem (régi mobil menü)
const menuBtn = document.getElementById("menuBtn");
const menu = document.querySelector("ul");

// Csak akkor futtatjuk, ha a menuBtn létezik (régi mobil menü esetén)
if (menuBtn && menu) {
    // menut ezzel nyitod/csukod
    menuBtn.addEventListener("click", (event) => {
        event.stopPropagation();
        menu.classList.toggle("open");
    });

    // ha a menün kivül kattintunk akkor is becsukódjon a menü
    document.addEventListener("click", (event) => {
        const clickedInsideMenu = menu.contains(event.target);

        if (!clickedInsideMenu) {
            menu.classList.remove("open");
        }
    });
}