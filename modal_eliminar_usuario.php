<div class="modal fade" id="modalEliminarUsuario<?php echo $usuario['idUsuario']; ?>" tabindex="-1" aria-labelledby="modalEliminarUsuarioLabel<?php echo $usuario['idUsuario']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEliminarUsuarioLabel<?php echo $usuario['idUsuario']; ?>">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar al usuario <strong><?php echo htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['ApellidoP'] . ' ' . $usuario['ApellidoM']); ?></strong>?</p>
                <p class="text-warning"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="gestion_usuarios.php">
                    <input type="hidden" name="idUsuarioEliminar" value="<?php echo $usuario['idUsuario']; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="eliminar_usuario" class="btn btn-danger">Eliminar Usuario</button>
                </form>
            </div>
        </div>
    </div>
</div>