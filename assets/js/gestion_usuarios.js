document.addEventListener('DOMContentLoaded', function() {
    const listUsersTab = document.getElementById('listUsers-tab');
    const listUsersContent = document.getElementById('listUsers');
    const addUserForm = document.querySelector('#addUser form');
    const listUsersTableBody = listUsersContent.querySelector('tbody');
    const addUserAlertContainer = addUserForm.querySelector('.alert-container'); // Container para alertas en Add User form (crear en HTML)


    // Función para cargar la lista de usuarios vía AJAX
    function cargarUsuarios() {
        fetch('backend/get_usuarios.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error("Error al cargar usuarios:", data.error);
                    // Mostrar mensaje de error en la UI si es necesario
                } else {
                    actualizarTablaUsuarios(data.usuarios); // Función para actualizar la tabla con los datos
                }
            })
            .catch(error => {
                console.error("Error al cargar usuarios:", error);
                // Mostrar mensaje de error en la UI si es necesario
            });
    }

    // Función para actualizar la tabla de usuarios en el HTML
    function actualizarTablaUsuarios(usuarios) {
        listUsersTableBody.innerHTML = ''; // Limpiar la tabla actual
        if (usuarios && usuarios.length > 0) {
            usuarios.forEach(usuario => {
                let row = listUsersTableBody.insertRow();
                row.insertCell().textContent = usuario.Nombre + ' ' + usuario.ApellidoP + ' ' + (usuario.ApellidoM || '');
                row.insertCell().textContent = usuario.NUsuario;
                row.insertCell().textContent = usuario.TipoUsuario;
                row.insertCell().textContent = usuario.Correo;
                let accionesCell = row.insertCell();
                accionesCell.innerHTML = `
                    <button class="btn btn-warning btn-sm btn-editar-usuario" data-id="${usuario.idUsuario}">Editar</button>
                    <button class="btn btn-danger btn-sm btn-eliminar-usuario" data-id="${usuario.idUsuario}">Eliminar</button>
                `;
            });
        } else {
            listUsersTableBody.innerHTML = '<tr><td colspan="5" class="text-center">No se encontraron usuarios.</td></tr>';
        }
    }


    // Evento para cargar usuarios cuando se muestra la pestaña "Listar Usuarios"
    listUsersTab.addEventListener('shown.bs.tab', function (event) {
        cargarUsuarios();
    });

    // Cargar usuarios inicialmente si la pestaña "Listar Usuarios" está activa al cargar la página
    if (listUsersTab.classList.contains('active')) {
        cargarUsuarios();
    }


    // Evento para el formulario "Agregar Usuario"
    addUserForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Evitar la recarga de la página

        const formData = new FormData(addUserForm);
        const userData = {};
        formData.forEach((value, key) => {
            userData[key] = value;
        });

        // Validación básica en cliente (puedes agregar más validaciones aquí)
        if (!userData.nombre || !userData.usuario || !userData.correo || !userData.rol || !userData.apellidoP) {
            mostrarAlerta('Por favor, complete todos los campos.', 'danger', addUserAlertContainer);
            return;
        }

        fetch('backend/agregar_usuario.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json' // Indicar que enviamos JSON
            },
            body: JSON.stringify(userData) // Enviar datos como JSON
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                mostrarAlerta('Error al agregar usuario: ' + data.error, 'danger', addUserAlertContainer);
            } else if (data.mensaje) {
                mostrarAlerta(data.mensaje + ' Contraseña temporal: ' + data.password_temporal + ' (¡Mostrar de forma segura en un sistema real!)', 'success', addUserAlertContainer);
                addUserForm.reset(); // Limpiar el formulario
                cargarUsuarios(); // Recargar la lista de usuarios para que se refleje el nuevo usuario
            }
        })
        .catch(error => {
            mostrarAlerta('Error de red al agregar usuario: ' + error, 'danger', addUserAlertContainer);
            console.error("Error al agregar usuario:", error);
        });
    });

    // Función para mostrar alertas (reutilizable)
    function mostrarAlerta(mensaje, tipo, contenedor) {
        const alertaHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        contenedor.innerHTML = alertaHTML; // Insertar la alerta en el contenedor
    }


});