// DormBook — клиенттік JavaScript
document.addEventListener("DOMContentLoaded", function () {

    // 1. Мобильді мәзір (кішкене экранда ☰ батырмасы)
    const burger = document.getElementById("burger");
    const nav = document.getElementById("nav");

    if (burger && nav) {
        burger.addEventListener("click", function () {
            nav.classList.toggle("open");
        });
    }

    // 2. Бөлмелерді бетте бірден сүзгілеу (9-аптадағы статикалық бет үшін)
    const liveSearch = document.getElementById("liveSearch");
    const typeFilter = document.getElementById("typeFilter");
    const freeOnly = document.getElementById("freeOnly");
    const rooms = document.querySelectorAll(".room-card[data-search]");
    const emptyMessage = document.getElementById("emptyMessage");

    function filterRooms() {
        const text = liveSearch.value.toLowerCase().trim();
        const type = typeFilter.value;
        const onlyFree = freeOnly.checked;
        let visible = 0;

        rooms.forEach(function (room) {
            const ok =
                room.dataset.search.toLowerCase().includes(text) &&
                (type === "" || room.dataset.type === type) &&
                (!onlyFree || Number(room.dataset.free) > 0);

            room.hidden = !ok;
            if (ok) visible++;
        });

        if (emptyMessage) emptyMessage.hidden = visible !== 0;
    }

    if (liveSearch && typeFilter && freeOnly) {
        liveSearch.addEventListener("input", filterRooms);
        typeFilter.addEventListener("change", filterRooms);
        freeOnly.addEventListener("change", filterRooms);
    }

    // Форма астындағы қате мәтінін көрсету
    function showError(form, text, isSuccess) {
        const box = form.querySelector(".form-error");
        if (box) {
            box.textContent = text;
            box.style.color = isSuccess ? "#1d8a45" : "";
            box.hidden = false;
        } else {
            alert(text);
        }
    }

    // 3. Брондау формасы: шығу күні кіру күнінен кейін болуы керек
    document.querySelectorAll(".booking-form").forEach(function (form) {
        form.addEventListener("submit", function (e) {
            const start = form.querySelector("[name=start_date]").value;
            const end = form.querySelector("[name=end_date]").value;

            if (start && end && end <= start) {
                e.preventDefault();
                showError(form, "Шығу күні кіру күнінен кейін болуы керек.");
            }
        });
    });

    // 4. Тіркелу формасы: құпиясөздер сәйкес пе?
    const registerForm = document.getElementById("registerForm");

    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            const pass = document.getElementById("password").value;
            const confirm = document.getElementById("confirm_password").value;

            if (pass !== confirm) {
                e.preventDefault();
                showError(registerForm, "Құпиясөздер сәйкес келмейді.");
            }
        });
    }

    // 6. Әкімші: жоюдан бұрын растау сұрау
    document.querySelectorAll(".delete-form").forEach(function (form) {
        form.addEventListener("submit", function (e) {
            if (!confirm(form.dataset.confirm || "Өшіруді растайсыз ба?")) {
                e.preventDefault();
            }
        });
    });

    // 5. Кіру формасы (9-апта, статикалық): email мен құпиясөзді тексеру
    const staticLogin = document.getElementById("staticLoginForm");

    if (staticLogin) {
        staticLogin.addEventListener("submit", function (e) {
            e.preventDefault();
            const email = document.getElementById("email").value.trim();
            const pass = document.getElementById("password").value;

            if (!email.includes("@")) {
                showError(staticLogin, "Email мекенжайын дұрыс енгізіңіз.");
            } else if (pass.length < 8) {
                showError(staticLogin, "Құпиясөз кемінде 8 таңба болуы керек.");
            } else {
                showError(staticLogin, "Форма дұрыс толтырылды. Серверлік тексеру келесі кезеңде қосылады.", true);
            }
        });
    }
});
