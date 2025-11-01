const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('sidebar');
const main = document.getElementById('contenido-principal');

// ----------------------
// Toggle menú lateral
// ----------------------
hamburger.addEventListener('click', () => {
    sidebar.classList.toggle('open');
});

// ----------------------
// Toggle submenús
// ----------------------
const menuItems = document.querySelectorAll('.sidebar > ul > li');
menuItems.forEach(item => {
    item.addEventListener('click', (e) => {
        const submenu = item.querySelector('.submenu');
        if (submenu) {
            submenu.classList.toggle('open');
            e.stopPropagation();
        }
    });
});

// ----------------------
// Ejecutar scripts de contenido cargado (si hay)
// ----------------------
function executeScripts(container) {
  container.querySelectorAll('script').forEach(oldScript => {
    const s = document.createElement('script');
    if (oldScript.src) {
      s.src = oldScript.src;  // ejecuta archivos externos
      s.async = false;
    } else {
      s.textContent = oldScript.textContent; // ejecuta inline
    }
    document.head.appendChild(s);
    document.head.removeChild(s);
  });
}


// ----------------------
// Inicializar formularios dinámicos (como el de crear usuario)
// ----------------------
function initDynamicForms(container) {
    const form = container.querySelector('#formCrearUsuario'); // id del form
    if (!form) return;

    const password = form.querySelector('#password');
    const confirmar = form.querySelector('#confirmar');
    const mensajeError = form.querySelector('#mensaje-error');

    // Validación en tiempo real
    if (confirmar) {
        confirmar.addEventListener('input', () => {
            if (password.value !== confirmar.value) mensajeError.style.display = 'block';
            else mensajeError.style.display = 'none';
        });
    }

    // Envío mediante fetch
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (password.value !== confirmar.value) {
            mensajeError.style.display = 'block';
            return;
        }
        const formData = new FormData(form);
        try {
            const res = await fetch('CreacionUsuarioEmpleados.php', {
                method: 'POST',
                body: formData
            });
            const html = await res.text();
            main.innerHTML = html;
            executeScripts(main);
        } catch (err) {
            console.error(err);
            alert('Error al enviar el formulario.');
        }
    });
}

// ----------------------
// Cargar módulos al hacer clic
// ----------------------
document.querySelectorAll('[data-url]').forEach(el => {
    el.addEventListener('click', async (e) => {
        e.stopPropagation();
        const url = el.getAttribute('data-url');
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Error al cargar la página');
            const html = await response.text();
            main.innerHTML = html;
            initDynamicForms(main);     // Inicializar formularios cargados
            executeScripts(main);       // Ejecutar scripts dentro del contenido
            sidebar.classList.remove('open');
            history.pushState({page: url}, '', url);
        } catch (error) {
            main.innerHTML = `<h2>Error</h2><p>No se pudo cargar "${url}".</p>`;
        }
    });
});
