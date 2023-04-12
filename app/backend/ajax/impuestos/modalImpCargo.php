<?php 

?>
<div class="modal-header">
    <h5 class="modal-title">Nuevo Cargo/Impuesto</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="frmImpuesto" name="frmImpuesto">
        <div class="row">
            <div class="col-12 col-lg-4 form-group">
                <label for="inpTipo">Tipo</label>
                <select name="inpTipo" id="inpTipo" class="form-select">
                    <option value="">Seleccione</option>
                    <option value="CARGO">Cargo</option>
                    <option value="IMP">Impuesto</option>
                </select>
            </div>
            <div class="col-12 col-lg-5">
                <label for="inpNom">Nombre</label>
                <input type="text" class="form-control" id="inpNom" name="inpNom" placeholder="Nombre del cargo o Impuesto">
            </div>
            <div class="col-12 col-lg-3">
                <label for="inpValor">Valor</label>
                <input type="number" class="form-control" id="inpValor" name="inpValor" placeholder="Ej: 0.258">
            </div>
        </div>
    </form>
</div>
<div class="modal-footer justify-content-between">
    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="mx-3 fas fa-backspace"></i> Cerrar</button>
    <button type="button" class="btn btn-success" title="Confirmar Los datos" onclick="IsNew('frmImpuesto')"><i class="mx-3 fas fa-check-double"></i> Confirmar</button>
</div>