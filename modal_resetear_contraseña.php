<div class="modal fade" id="modalResetearContraseña<?php echo $usuario['idUsuario']; ?>" tabindex="-1" aria-labelledby="modalResetearContraseñaLabel<?php echo $usuario['idUsuario']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalResetearContraseñaLabel<?php echo $usuario['idUsuario']; ?>">Resetear Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas resetear la contraseña del usuario <strong><?php echo htmlspecialchars($usuario['NUsuario']); ?></strong>?</p>
                <p class="text-warning"><small>Se generará una <strong>nueva contraseña temporal aleatoria</strong> y se enviará al correo electrónico del usuario.</small></p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="gestion_usuarios.php">
                    <input type="hidden" name="idUsuarioResetear" value="<?php echo $usuario['idUsuario']; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="resetear_contraseña" class="btn btn-primary">Resetear Contraseña</button>
                </form>
            </div>
        </div>
    </div>
</div>