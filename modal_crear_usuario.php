<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearUsuarioLabel">Crear Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="gestion_usuarios.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nUsuario" class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="nUsuario" name="nUsuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="contraseña" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="contraseña" name="contraseña" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellidoP" class="form-label">Apellido Paterno</label>
                        <input type="text" class="form-control" id="apellidoP" name="apellidoP" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellidoM" class="form-label">Apellido Materno (Opcional)</label>
                        <input type="text" class="form-control" id="apellidoM" name="apellidoM">
                    </div>
                    <div class="mb-3">
                        <label for="fechaNac" class="form-label">Fecha de Nacimiento (Opcional)</label>
                        <input type="date" class="form-control" id="fechaNac" name="fechaNac">
                    </div>
                    <div class="mb-3">
                        <label for="curp" class="form-label">CURP</label>
                        <input type="text" class="form-control" id="curp" name="curp" required>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipoUsuario" class="form-label">Rol de Usuario</label>
                        <select class="form-select" id="tipoUsuario" name="tipoUsuario" required>
                            <option value="" disabled selected>Selecciona un Rol</option>
                            <?php foreach ($tiposUsuario as $tipo): ?>
                                <option value="<?php echo $tipo; ?>"><?php echo ucfirst($tipo); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="crear_usuario" class="btn btn-primary">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>