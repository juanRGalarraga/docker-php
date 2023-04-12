<?php
  /**
   * Plantilla que genera la tabla con los permisos.
   * @created 2021-11-03
   * @author Juan Galarraga
   */

/**
 * Alias para in_array
 */
function permiso_existe(string $permiso, $permisos){
	if(is_array($permisos)){
		return in_array($permiso,$permisos);
	}else{
		return property_exists($permisos, $permiso);
	}
}

if(isset($template) and !empty($template)):
?>
<table class="table caption-top">
  <caption>Permisos</caption>
  <thead>
    <tr class="table-blue-primary">
      <th scope="col">Contenidos</th>
      <th scope="col">Leer</th>
      <th scope="col">Crear</th>
      <th scope="col">Modificar</th>
      <th scope="col">Eliminar</th>
    </tr>
  </thead>
  <tbody>
<?php
    foreach($template as $parentValue):
      $parentKey = $parentValue->id;
      $haveChilds = false;
      $parentPermisos = $parentValue->permisos ?? new stdClass();
      if(isset($parentValue->childs)): $haveChilds = true; endif;
?>
      <input 
        type="hidden" 
        name="plantilla[<?php echo $parentKey ?>][id]" 
        value="<?php echo $parentKey?>">

      <input 
        type="hidden" 
        name="plantilla[<?php echo $parentKey ?>][alias]" 
        value="<?php echo $parentValue->alias?>">

      <input 
        type="hidden" 
        name="plantilla[<?php echo $parentKey ?>][nombre]" 
        value="<?php echo $parentValue->nombre?>">
<?php
      if($haveChilds):
        echo (
          '<button 
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#collapse-'.$parentKey.'" 
            id="toggle-'.$parentKey.'" hidden>
          </button> '
        );
      endif;
?>
    <tr class="table-border-white" <?php echo ($haveChilds)?'role="button"':''?>>
      <th onclick="ToggleCollapse('<?php echo $parentKey?>')" scope="row">
<?php 
        if($haveChilds): 
          echo ('<i class="fas fa-chevron-down" id="toggle-icon-'.$parentKey.'"></i> ');
        endif;
        echo "$parentValue->nombre"
?>
      </th>
      <td>
          <input 
            type="checkbox" 
            name="plantilla[<?php echo $parentKey ?>][permisos][r]" 
            id="checkR-<?php echo $parentKey?>"
            onchange="ControlCheckedPermisos('<?php echo $parentKey?>')" 
            value="r" 
            role="switch" 
            class="form-check-input" 
            <?php 
            echo permiso_existe('r', $parentPermisos) ? 'checked ' : '';
            ?>
          >
      </td>
      <td>
          <input 
            type="checkbox" 
            name="plantilla[<?php echo $parentKey ?>][permisos][c]" 
            id="checkC-<?php echo $parentKey?>"
            value="c" 
            role="switch" 
            class="form-check-input" 
            <?php 
            echo permiso_existe('c', $parentPermisos) ? 'checked ' : ''; 
            echo !permiso_existe('r', $parentPermisos) ? 'disabled' : '';
            ?>
          >
      </td>
      <td>
          <input 
            type="checkbox" 
            name="plantilla[<?php echo $parentKey ?>][permisos][u]"
            id="checkU-<?php echo $parentKey?>" 
            value="u" 
            role="switch" 
            class="form-check-input" 
            <?php 
            echo permiso_existe('u', $parentPermisos) ? 'checked ' : ''; 
            echo !permiso_existe('r', $parentPermisos) ? 'disabled' : '';
            ?>
          >
      </td>
      <td>
          <input 
            type="checkbox" 
            name="plantilla[<?php echo $parentKey ?>][permisos][d]" 
            id="checkD-<?php echo $parentKey?>" 
            value="d" 
            role="switch" 
            class="form-check-input" 
            <?php 
            echo permiso_existe('d', $parentPermisos) ? 'checked ' : '';
            echo !permiso_existe('r', $parentPermisos) ? 'disabled' : '';
            ?>
          >
      </td>
    </tr>

<?php
      if($haveChilds):
        foreach(new ArrayIterator($parentValue->childs) as $value):
          $childPermisos = $value->permisos ?? new stdClass();
          $childKey = $value->id;
?> 
    <input 
      type="hidden" 
      name="plantilla[<?php echo $parentKey ?>][childs][<?php echo $childKey ?>][id]" 
      value="<?php echo $childKey?>">

    <input 
      type="hidden" 
      name="plantilla[<?php echo $parentKey ?>][childs][<?php echo $childKey ?>][alias]" 
      value="<?php echo $value->alias?>">

    <input 
      type="hidden" 
      name="plantilla[<?php echo $parentKey ?>][childs][<?php echo $childKey ?>][nombre]" 
      value="<?php echo $value->nombre?>">

    <tr class="collapse table-blue-ligth" id="collapse-<?php echo $parentKey?>">
      <th scope="row" style="padding-left: 2rem !important">
        <i class="fas fa-level-up-alt fa-rotate-90" style="margin-right: 12px;"></i><?php echo " $value->nombre"?>
      </th>
      <td>
          <input 
            id="checkR-<?php echo $childKey?>" 
            type="checkbox" 
            name="plantilla[<?php echo $parentKey ?>][childs][<?php echo $childKey ?>][permisos][r]"  
            value="r" 
            role="switch" 
            class="form-check-input checkToParent-<?php echo $parentKey ?>" 
            onchange="ControlCheckedPermisos('<?php echo $childKey?>')"
            <?php 
            echo permiso_existe('r', $childPermisos) ? 'checked ' : '';
            echo !permiso_existe('r', $parentPermisos) ? 'disabled' : '';
            ?>
          >
      </td>
      <td>
          <input 
            id="checkC-<?php echo $childKey?>" 
            type="checkbox" 
            name="plantilla[<?php echo $parentKey ?>][childs][<?php echo $childKey ?>][permisos][c]" 
            value="c" 
            role="switch" 
            class="form-check-input checkToParent-<?php echo $parentValue->id ?>" 
            <?php 
            echo permiso_existe('c', $childPermisos) ? 'checked' : ''; 
            echo !permiso_existe('r', $childPermisos) || !permiso_existe('r', $parentPermisos) ? 'disabled' : '';
            ?>
          >
      </td>
      <td>
          <input 
            id="checkU-<?php echo $childKey?>" 
            type="checkbox" 
            name="plantilla[<?php echo $parentKey ?>][childs][<?php echo $childKey ?>][permisos][u]" 
            value="u" 
            role="switch" 
            class="form-check-input checkToParent-<?php echo $parentKey ?>" 
            <?php 
            echo permiso_existe('u', $childPermisos) ? 'checked' : ''; 
            echo !permiso_existe('r', $childPermisos) || !permiso_existe('r', $parentPermisos) ? 'disabled' : '';
            ?>
          >
      </td>
      <td>
          <input 
            id="checkD-<?php echo $childKey?>" 
            type="checkbox" 
            name="plantilla[<?php echo $parentKey ?>][childs][<?php echo $childKey ?>][permisos][d]" 
            value="d" 
            role="switch" 
            class="form-check-input checkToParent-<?php echo $parentKey ?>" 
            <?php 
            echo permiso_existe('d', $childPermisos) ? 'checked' : ''; 
            echo !permiso_existe('r', $childPermisos) || !permiso_existe('r', $parentPermisos) ? 'disabled' : '';
            ?>
          >
      </td>
    </tr>
<?php
        endforeach;
      endif;
    endforeach;
?>
  </tbody>
</table>
<?php
endif;