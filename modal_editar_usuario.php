<div class="modal fade" id="modalEditarUsuario<?php echo $usuario['idUsuario']; ?>" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel<?php echo $usuario['idUsuario']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarUsuarioLabel<?php echo $usuario['idUsuario']; ?>">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="gestion_usuarios.php">
                <div class="modal-body">
                    <input type="hidden" name="idUsuarioEditar" value="<?php echo $usuario['idUsuario']; ?>">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['Nombre']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellidoP" class="form-label">Apellido Paterno</label>
                        <input type="text" class="form-control" id="apellidoP" name="apellidoP" value="<?php echo htmlspecialchars($usuario['ApellidoP']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellidoM" class="form-label">Apellido Materno (Opcional)</label>
                        <input type="text" class="form-control" id="apellidoM" name="apellidoM" value="<?php echo htmlspecialchars($usuario['ApellidoM']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="fechaNac" class="form-label">Fecha de Nacimiento (Opcional)</label>
                        <input type="date" class="form-control" id="fechaNac" name="fechaNac" value="<?php echo htmlspecialchars($usuario['FechaNac']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="curp" class="form-label">CURP</label>
                        <input type="text" class="form-control" id="curp" name="curp" value="<?php echo htmlspecialchars($usuario['CURP']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario['Correo']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipoUsuario" class="form-label">Rol de Usuario</label>
                        <select class="form-select" id="tipoUsuario" name="tipoUsuario" required>
                            <option value="" disabled>Selecciona un Rol</option>
                            <?php foreach ($tiposUsuario as $tipo): ?>
                                <option value="<?php echo $tipo; ?>" <?php if ($usuario['TipoUsuario'] === $tipo) echo 'selected'; ?>><?php echo ucfirst($tipo); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="editar_usuario" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>