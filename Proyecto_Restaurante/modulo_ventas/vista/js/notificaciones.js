class NotificacionManager {
    constructor(usuarioId) {
        this.usuarioId = usuarioId;
        this.intervalo = null;
    }

    iniciar() {
        // Verificar notificaciones cada 10 segundos
        this.intervalo = setInterval(() => this.verificarNotificaciones(), 10000);
        this.verificarNotificaciones(); // Verificar inmediatamente
    }

    detener() {
        if (this.intervalo) {
            clearInterval(this.intervalo);
        }
    }

    verificarNotificaciones() {
        fetch(`../Controlador/NotificacionControlador.php?accion=obtener&usuario=${this.usuarioId}`)
            .then(response => response.json())
            .then(notificaciones => {
                notificaciones.forEach(notif => {
                    this.mostrarNotificacion(notif);
                    this.marcarLeida(notif.id_notificacion);
                });
            });
    }

    mostrarNotificacion(notificacion) {
        // Mostrar notificación nativa del navegador
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Restaurante El Adobe', {
                body: notificacion.mensaje,
                icon: '/img/icon.png'
            });
        }

        // Mostrar notificación en la interfaz
        this.mostrarNotificacionUI(notificacion);
    }

    mostrarNotificacionUI(notificacion) {
        const notifDiv = document.createElement('div');
        notifDiv.className = 'notificacion-toast';
        notifDiv.innerHTML = `
            <div class="notificacion-contenido">
                <strong>📢 Notificación</strong>
                <p>${notificacion.mensaje}</p>
                <small>${new Date(notificacion.fecha).toLocaleTimeString()}</small>
            </div>
        `;

        document.body.appendChild(notifDiv);

        // Remover después de 5 segundos
        setTimeout(() => {
            notifDiv.remove();
        }, 5000);
    }

    marcarLeida(idNotificacion) {
        fetch(`../Controlador/NotificacionControlador.php?accion=marcar_leida&id=${idNotificacion}`);
    }
}

// Uso en las páginas de mesero
// const notifManager = new NotificacionManager(2); // ID del usuario
// notifManager.iniciar();