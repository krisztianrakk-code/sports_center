/**
 * Core Application Frontend Logic (Combined Driver)
 * Path: assets/js/main.js
 */
document.addEventListener("DOMContentLoaded", function () {
    
    // ==========================================
    // 1. RÉSZ: Kezdőlapi Sportág Választó (Carousel)
    // ==========================================
    const ORDER = ['football', 'basketball', 'volleyball', 'tennis'];

    const fields = {
        football: {
            title: "Book Premium Football Fields",
            desc: "Professional-grade football pitches available for hourly rental. Perfect for matches and training sessions.",
            img: "assets/img/football.jpg",
            color: "var(--c-football)"
        },
        basketball: {
            title: "Book Premium Basketball Courts",
            desc: "Indoor and outdoor basketball courts with pro-grade flooring and adjustable hoops, ready for pickup games or league play.",
            img: "assets/img/basketball.jpg",
            color: "var(--c-basketball)"
        },
        volleyball: {
            title: "Book Premium Volleyball Courts",
            desc: "Sand and indoor volleyball courts maintained to tournament standard, available by the hour for teams and casual players.",
            img: "assets/img/volleyball.jpg",
            color: "var(--c-volleyball)"
        },
        tennis: {
            title: "Book Premium Tennis Courts",
            desc: "Hard and clay tennis courts with fresh lines and quality nets, available for singles, doubles, and coaching sessions.",
            img: "assets/img/tennis.jpg",
            color: "var(--c-tennis)"
        }
    };

    const root = document.documentElement;
    const sidebarItems = document.querySelectorAll('.field-item');
    const title = document.getElementById('title');
    const desc = document.getElementById('desc');
    const badge = document.getElementById('badge');
    const badge2 = document.getElementById('badge2');
    const heroImg = document.getElementById('hero-img');
    const dots = document.querySelectorAll('.dot');
    const bookBtn = document.getElementById('book-now-btn');

    const TIMER_MS = 4000;
    let currentIndex = 0;
    let intervalId = null;

    // Csak akkor fut le a körhinta logikája, ha a hozzá tartozó elemek léteznek az oldalon (pl. Index oldal)
    if (sidebarItems.length > 0 && bookBtn) {
        
        function applyField(index, animateImage) {
            const key = ORDER[index];
            const data = fields[key];

            if (title) title.textContent = data.title;
            if (desc) desc.textContent = data.desc;
            
            const label = key.charAt(0).toUpperCase() + key.slice(1);
            if (badge) badge.textContent = label;
            if (badge2) badge2.textContent = label;

            root.style.setProperty('--active-color', data.color);

            if (heroImg) {
                if (animateImage) {
                    heroImg.style.opacity = 0;
                    setTimeout(() => { 
                        heroImg.src = data.img; 
                        heroImg.style.opacity = 1; 
                    }, 180);
                } else {
                    heroImg.src = data.img;
                }
            }

            sidebarItems.forEach(b => b.classList.toggle('active', b.dataset.field === key));
            bookBtn.href = 'fields.php?type=' + key;

            dots.forEach((d, i) => {
                d.classList.remove('active', 'done');
                d.style.removeProperty('--dot-color');
                if (i < index) d.classList.add('done');
                if (i === index) {
                    d.classList.add('active');
                    d.style.setProperty('--dot-color', data.color);
                }
            });

            currentIndex = index;
        }

        function goTo(index, userInitiated) {
            applyField(index, true);
            if (userInitiated) restartTimer();
        }

        function nextField() {
            const next = (currentIndex + 1) % ORDER.length;
            applyField(next, true);
        }

        function restartTimer() {
            if (intervalId) clearInterval(intervalId);
            intervalId = setInterval(nextField, TIMER_MS);
        }

        sidebarItems.forEach(btn => {
            btn.addEventListener('click', () => {
                goTo(ORDER.indexOf(btn.dataset.field), true);
            });
        });

        dots.forEach(d => {
            d.addEventListener('click', () => {
                goTo(parseInt(d.dataset.i, 10), true);
            });
        });

        // Inicializálás
        applyField(0, false);
        restartTimer();
    }

    // ==========================================
    // 2. RÉSZ: Idősáv Ellenőrzés és Foglalás (AJAX)
    // ==========================================
    const dateInput = document.getElementById("booking_date");
    const slotsContainer = document.getElementById("slots_container");
    const selectedTimeInput = document.getElementById("selected_time");
    const courtIdInput = document.getElementById("court_id");

    // Csak akkor fut le, ha a foglalási felületen vagyunk (pl. fields.php / booking oldal)
    if (dateInput && slotsContainer) {
        dateInput.addEventListener("change", function () {
            const selectedDate = this.value;
            const courtId = courtIdInput ? courtIdInput.value : '';

            if (!selectedDate) return;

            slotsContainer.innerHTML = "<em>Loading slots...</em>";

            fetch(`api/check_slots.php?court_id=${courtId}&date=${selectedDate}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        slotsContainer.innerHTML = `<span style="color:red;">Error: ${data.message}</span>`;
                        return;
                    }

                    slotsContainer.innerHTML = ""; // Konténer ürítése

                    data.slots.forEach(slot => {
                        const btn = document.createElement("button");
                        btn.type = "button";
                        btn.innerText = slot.time;
                        btn.style.margin = "5px";
                        btn.style.padding = "10px";

                        if (!slot.available) {
                            btn.disabled = true;
                            btn.style.backgroundColor = "#e0e0e0";
                            btn.style.color = "#999";
                            btn.style.cursor = "not-allowed";
                        } else {
                            btn.style.backgroundColor = "#fff";
                            btn.style.border = "1px solid #000";
                            btn.style.cursor = "pointer";

                            btn.addEventListener("click", function () {
                                Array.from(slotsContainer.children).forEach(b => {
                                    if (!b.disabled) b.style.backgroundColor = "#fff";
                                });
                                btn.style.backgroundColor = "orange";
                                if (selectedTimeInput) {
                                    selectedTimeInput.value = slot.time + ":00";
                                }
                            });
                        }
                        slotsContainer.appendChild(btn);
                    });
                })
                .catch(err => {
                    console.error("Fetch communication breakdown:", err);
                    slotsContainer.innerHTML = '<span style="color:red;">Failed to load time slots.</span>';
                });
        });
    }
});