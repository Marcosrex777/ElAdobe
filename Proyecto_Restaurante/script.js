document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-link, .tab-link-footer');
    const tabContents = document.querySelectorAll('.tab-content');

    // Lógica para mostrar y ocultar pestañas
    function showTab(targetTab) {
        tabContents.forEach(content => {
            content.classList.remove('active');
        });
        tabButtons.forEach(btn => {
            btn.classList.remove('active');
        });

        const targetContent = document.getElementById(targetTab);
        if (targetContent) {
            targetContent.classList.add('active');
            document.querySelectorAll(`[data-tab="${targetTab}"]`).forEach(btn => {
                btn.classList.add('active');
            });
        }
    }

    tabButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const targetTab = button.dataset.tab;
            showTab(targetTab);
        });
    });

    showTab('inicio');

    // --- Lógica del Carrusel de Imágenes ---
    const carouselItems = document.querySelectorAll('.carousel-item');
    const carouselDots = document.querySelectorAll('.carousel-nav .dot');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    let currentCarouselIndex = 0;

    function showCarouselItem(index) {
        carouselItems.forEach((item, i) => {
            if (i === index) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
        carouselDots.forEach((dot, i) => {
            if (i === index) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }
    
    // Inicia el carrusel
    showCarouselItem(currentCarouselIndex);

    // Navegación con los puntos
    carouselDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentCarouselIndex = index;
            showCarouselItem(currentCarouselIndex);
        });
    });

    // Navegación con las flechas
    nextBtn.addEventListener('click', () => {
        currentCarouselIndex = (currentCarouselIndex + 1) % carouselItems.length;
        showCarouselItem(currentCarouselIndex);
    });

    prevBtn.addEventListener('click', () => {
        currentCarouselIndex = (currentCarouselIndex - 1 + carouselItems.length) % carouselItems.length;
        showCarouselItem(currentCarouselIndex);
    });

    // Función para avanzar el carrusel automáticamente
    setInterval(() => {
        currentCarouselIndex = (currentCarouselIndex + 1) % carouselItems.length;
        showCarouselItem(currentCarouselIndex);
    }, 5000); // Cambia cada 5 segundos
});