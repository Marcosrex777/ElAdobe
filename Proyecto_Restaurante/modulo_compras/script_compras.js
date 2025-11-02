// script_compras.js
function debounce(fn, ms=250){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }
function formato(num){ return (Number(num)||0).toFixed(2); }
const $ = (id)=>document.getElementById(id);

// ===== AUTOCOMPLETADO =====
function attachAutocomplete(input, fetcher, onPick){
  const cont = document.createElement('div');
  cont.className = 'ac-list';
  input.parentNode.style.position = 'relative';
  input.parentNode.appendChild(cont);

  input.addEventListener('input', debounce(async ()=>{
    const q = input.value.trim();
    if(!q){ cont.innerHTML=''; return; }
    const arr = await fetcher(q);
    cont.innerHTML = '';
    arr.forEach(it=>{
      const div = document.createElement('div');
      div.className='ac-item';
      div.textContent = it.label ?? it.nombre ?? it;
      div.addEventListener('click', ()=>{
        input.value = it.label ?? it.nombre ?? '';
        cont.innerHTML='';
        onPick && onPick(it);
      });
      cont.appendChild(div);
    });
  }, 250));

  document.addEventListener('click', (e)=>{
    if (!cont.contains(e.target) && e.target!==input){ cont.innerHTML=''; }
  });
}

// ===== VARIABLES =====
const detalle = [];

// ===== AUTOCOMPLETADOS =====
attachAutocomplete($('proveedor_buscar'), async(q)=>{
  const r = await fetch(`buscar_filtros.php?action=proveedores&q=${encodeURIComponent(q)}`);
  return await r.json();
}, (item)=>{
  $('id_proveedor').value = item.id;
});

attachAutocomplete($('producto_buscar'), async(q)=>{
  const r = await fetch(`buscar_filtros.php?action=productos&q=${encodeURIComponent(q)}`);
  return await r.json();
}, async (item)=>{
  $('producto_buscar').dataset.id = item.id;
  $('precio_add').value = formato(item.precio);
});

// ===== AGREGAR PRODUCTO =====
$('btn_agregar').addEventListener('click', ()=>{
  const idp = parseInt($('producto_buscar').dataset.id||'0',10);
  const nombre = $('producto_buscar').value.trim();
  const cantidad = parseInt($('cantidad_add').value||'0',10);
  const precio = parseFloat($('precio_add').value||'0');
  if(!idp || !nombre){ alert('Selecciona un producto válido.'); return; }
  if(!cantidad || cantidad<=0){ alert('Cantidad inválida.'); return; }

  const idx = detalle.findIndex(x=>x.id_producto===idp);
  if(idx>=0){ detalle[idx].cantidad += cantidad; }
  else{ detalle.push({id_producto:idp, nombre, cantidad, precio_unitario:precio}); }

  $('producto_buscar').value=''; $('producto_buscar').dataset.id=''; $('cantidad_add').value=1; $('precio_add').value='';
  renderDetalle();
});

function renderDetalle(){
  const tbody = $('tabla_detalle').querySelector('tbody');
  tbody.innerHTML='';
  let total=0;
  detalle.forEach((it,i)=>{
    const sub = it.cantidad * it.precio_unitario;
    total += sub;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${it.nombre}</td>
      <td><input type="number" min="1" value="${it.cantidad}" data-i="${i}" class="inp-cant"></td>
      <td>${formato(it.precio_unitario)}</td>
      <td>${formato(sub)}</td>
      <td><button data-i="${i}" class="btn-quitar">X</button></td>
    `;
    tbody.appendChild(tr);
  });
  $('total_general').textContent = formato(total);

  tbody.querySelectorAll('.inp-cant').forEach(inp=>{
    inp.addEventListener('change', e=>{
      const i = parseInt(e.target.dataset.i,10);
      detalle[i].cantidad = parseInt(e.target.value||'1',10);
      renderDetalle();
    });
  });
  tbody.querySelectorAll('.btn-quitar').forEach(btn=>{
    btn.addEventListener('click', e=>{
      const i = parseInt(e.target.dataset.i,10);
      detalle.splice(i,1);
      renderDetalle();
    });
  });
}

// ===== GUARDAR COMPRA =====
$('btn_guardar').addEventListener('click', async ()=>{
  const id_proveedor = parseInt($('id_proveedor').value||'0',10);
  if(!id_proveedor){ $('msg_form').textContent='Selecciona un proveedor'; return; }
  if(detalle.length===0){ $('msg_form').textContent='Agrega al menos un producto'; return; }

  const payload = {
    id_proveedor,
    fecha: $('fecha').value,
    metodo_pago: $('metodo_pago').value,
    numero_comprobante: $('numero_comprobante').value,
    items: detalle.map(d=>({
      id_producto: d.id_producto,
      cantidad: d.cantidad,
      precio_unitario: d.precio_unitario
    }))
  };

  $('msg_form').textContent='Guardando...';
  const r = await fetch('registrar_compra.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  });
  const j = await r.json();
  if(j.ok){
    $('msg_form').textContent='Compra registrada. ID: '+j.id_compra+' Total: Q '+j.total;
    $('id_proveedor').value=''; $('proveedor_buscar').value=''; $('numero_comprobante').value='';
    detalle.splice(0,detalle.length); renderDetalle();
  }else{
    $('msg_form').textContent = j.msg || 'Error';
  }
});

// ===== REPORTES =====
$('btn_toggle_reporte').addEventListener('click', ()=>{
  $('panel_filtros').classList.toggle('oculto');
});

// Autocompletados de filtros
attachAutocomplete($('rep_proveedor'), async(q)=>{
  const r = await fetch(`buscar_filtros.php?action=proveedores&q=${encodeURIComponent(q)}`);
  return await r.json();
}, (item)=>{ $('rep_proveedor_id').value = item.id; });

attachAutocomplete($('rep_producto'), async(q)=>{
  const r = await fetch(`buscar_filtros.php?action=productos&q=${encodeURIComponent(q)}`);
  return await r.json();
}, (item)=>{ $('rep_producto_id').value = item.id; });

attachAutocomplete($('rep_categoria'), async(q)=>{
  const r = await fetch(`buscar_filtros.php?action=categorias&q=${encodeURIComponent(q)}`);
  return await r.json();
}, (item)=>{ $('rep_categoria').value = item.label; });

// Autocompletado para comprobantes
attachAutocomplete($('rep_comprobante'), async(q)=>{
  const r = await fetch(`buscar_filtros.php?action=comprobantes&q=${encodeURIComponent(q)}`);
  return await r.json();
}, (item)=>{ $('rep_comprobante').value = item.label; });


// ===== BUSCAR REPORTE =====
$('btn_buscar_reporte').addEventListener('click', async ()=>{
  // Actualizar formulario PDF con filtros actuales
  $('pdf_proveedor_id').value = $('rep_proveedor_id').value;
  $('pdf_producto_id').value = $('rep_producto_id').value;
  $('pdf_categoria').value = $('rep_categoria').value;
  $('pdf_comprobante').value = $('rep_comprobante').value;
  $('pdf_inicio').value = $('rep_inicio').value;
  $('pdf_fin').value = $('rep_fin').value;

  const qs = new URLSearchParams({
    proveedor_id: $('rep_proveedor_id').value || '',
    producto_id: $('rep_producto_id').value || '',
    categoria: $('rep_categoria').value || '',
    comprobante: $('rep_comprobante').value || '',
    inicio: $('rep_inicio').value || '',
    fin: $('rep_fin').value || ''
  });

  $('msg_reporte').textContent='Cargando...';
  const r = await fetch('reporte_compras.php?'+qs.toString());
  const j = await r.json();
  $('msg_reporte').textContent='';
  renderReporte(j);
});

// ===== LIMPIAR REPORTE =====
$('btn_limpiar_reporte').addEventListener('click', ()=>{
  ['rep_proveedor','rep_producto','rep_categoria','rep_inicio','rep_fin','rep_comprobante'].forEach(id=>$(id).value='');
  ['rep_proveedor_id','rep_producto_id'].forEach(id=>$(id).value='');
  renderReporte({rows:[], total_reporte:'0.00'});

  // Limpiar también los filtros del PDF
  ['pdf_proveedor_id','pdf_producto_id','pdf_categoria','pdf_comprobante','pdf_inicio','pdf_fin']
    .forEach(id=>$(id).value='');
});

// ===== RENDERIZAR REPORTE =====
function renderReporte(data){
  const tbody = $('tabla_reporte').querySelector('tbody');
  tbody.innerHTML='';
  (data.rows||[]).forEach(r=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${r.fecha}</td>
      <td>${r.proveedor}</td>
      <td>${r.comprobante ?? ''}</td>
      <td>${r.producto}</td>
      <td>${r.categoria ?? ''}</td>
      <td>${r.cantidad}</td>
      <td>${r.precio}</td>
      <td>${r.subtotal}</td>
      <td>${r.total_compra}</td>
    `;
    tbody.appendChild(tr);
  });
  $('rep_total').textContent = data.total_reporte ? data.total_reporte : '0.00';
}
