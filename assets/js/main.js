document.addEventListener('DOMContentLoaded', function() {
    /**
     * Toggle sidebar en dispositivos móviles
     */
    const sidebarToggle = document.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const sidebar = document.querySelector('#sidebar');
            const content = document.querySelector('#content');
            
            sidebar.classList.toggle('active');
            
            // Ajustar contenido solo en móviles
            if (window.innerWidth <= 768) {
                if (sidebar.classList.contains('active')) {
                    content.style.marginLeft = '250px';
                } else {
                    content.style.marginLeft = '0';
                }
            }
        });
    }

    // Cerrar sidebar al hacer clic en enlaces en modo móvil
    const sidebarLinks = document.querySelectorAll('#sidebar a');
    if (sidebarLinks) {
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    document.querySelector('#sidebar').classList.remove('active');
                    document.querySelector('#content').style.marginLeft = '0';
                }
            });
        });
    }

    /**
     * Inicialización de tooltips de Bootstrap
     */
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    /**
     * Inicialización de tablas con DataTable para ordenación y filtrado
     */
    const dataTables = document.querySelectorAll('.data-table');
    if (dataTables.length > 0 && typeof $.fn.DataTable !== 'undefined') {
        dataTables.forEach(table => {
            $(table).DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]]
            });
        });
    }

    /**
     * Función para confirmar acciones peligrosas
     */
    const confirmActions = document.querySelectorAll('[data-confirm]');
    if (confirmActions.length > 0) {
        confirmActions.forEach(element => {
            element.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm') || '¿Estás seguro de realizar esta acción?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    }

    /**
     * Función para mostrar/ocultar elementos basados en selección
     */
    const toggleSelectors = document.querySelectorAll('[data-toggle-target]');
    if (toggleSelectors.length > 0) {
        toggleSelectors.forEach(element => {
            element.addEventListener('change', function() {
                const targetId = this.getAttribute('data-toggle-target');
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    if (this.checked || this.value === 'show') {
                        targetElement.style.display = 'block';
                    } else {
                        targetElement.style.display = 'none';
                    }
                }
            });
            
            // Trigger inicial
            element.dispatchEvent(new Event('change'));
        });
    }
    
    /**
     * Auto-formateo de fechas y números
     */
    const formatDates = document.querySelectorAll('.format-date');
    if (formatDates.length > 0) {
        formatDates.forEach(element => {
            const dateStr = element.textContent.trim();
            if (dateStr) {
                const date = new Date(dateStr);
                element.textContent = date.toLocaleDateString('es-ES');
            }
        });
    }
    
    /**
     * Filtros rápidos en dashboard
     */
    const quickFilters = document.querySelectorAll('.quick-filter');
    if (quickFilters.length > 0) {
        quickFilters.forEach(filter => {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetTable = document.querySelector(this.getAttribute('data-target'));
                const filterValue = this.getAttribute('data-filter');
                
                if (targetTable && typeof $.fn.DataTable !== 'undefined') {
                    const dataTable = $(targetTable).DataTable();
                    if (dataTable) {
                        dataTable.search(filterValue).draw();
                    }
                }
            });
        });
    }
    
    /**
     * Actualización en tiempo real de contadores
     */
    function updateCounters() {
        const counters = document.querySelectorAll('[data-counter-url]');
        if (counters.length > 0) {
            counters.forEach(counter => {
                const url = counter.getAttribute('data-counter-url');
                if (url) {
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.count !== undefined) {
                                counter.textContent = data.count;
                            }
                        })
                        .catch(error => console.error('Error actualizando contador:', error));
                }
            });
        }
    }
    
    // Actualizar contadores cada 5 minutos si existen
    if (document.querySelectorAll('[data-counter-url]').length > 0) {
        setInterval(updateCounters, 300000); // 5 minutos
    }
}); 