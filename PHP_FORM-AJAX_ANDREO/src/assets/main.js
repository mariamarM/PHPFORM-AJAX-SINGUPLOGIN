document.addEventListener('DOMContentLoaded', () => {

  const URL_API_SERVIDOR = 'auth/api.php';
  let listaUsuarios = [];

  const nodoCuerpoTablaUsuarios = document.getElementById('usersTable');
  const formularioAltaUsuario = document.getElementById('userForm');
  const nodoZonaMensajesEstado = document.getElementById('msg');
  const modalEditar = document.getElementById('modalEditar');
  const formEditarUsuario = document.getElementById('formEditarUsuario');
  const editIndex = document.getElementById('editIndex');
  const editNombre = document.getElementById('editNombre');
  const editEmail = document.getElementById('editEmail');

  // --- Mostrar mensajes de estado ---
  function mostrarMensajeDeEstado(tipo, texto) {
    nodoZonaMensajesEstado.className = tipo;
    nodoZonaMensajesEstado.textContent = texto;
    if (tipo) {
      setTimeout(() => {
        nodoZonaMensajesEstado.className = '';
        nodoZonaMensajesEstado.textContent = '';
      }, 2000);
    }
  }

  // --- Sanitizar texto ---
  function convertirATextoSeguro(txt) {
    return String(txt)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#39;');
  }

  // --- Renderizar tabla ---
  function renderizarTablaDeUsuarios(arrayUsuarios) {
    nodoCuerpoTablaUsuarios.innerHTML = '';

    if (!Array.isArray(arrayUsuarios) || arrayUsuarios.length === 0) {
      nodoCuerpoTablaUsuarios.innerHTML = `
        <tr><td colspan="3" class="text-center py-3 text-gray-500">No hay usuarios registrados</td></tr>
      `;
      return;
    }

    listaUsuarios = arrayUsuarios;

    arrayUsuarios.forEach((usuario, i) => {
      const fila = document.createElement('tr');
      fila.innerHTML = `
        <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700">${convertirATextoSeguro(usuario.nombre)}</td>
        <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700">${convertirATextoSeguro(usuario.email)}</td>
        <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700 flex gap-2">
          <button data-posicion="${i}" class="editar px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-500">Editar</button>
          <button data-posicion="${i}" class="eliminar px-3 py-1 bg-red-600 text-white rounded hover:bg-red-500">Eliminar</button>
        </td>
      `;
      nodoCuerpoTablaUsuarios.appendChild(fila);
    });
  }

  // --- Obtener y mostrar lista ---
  async function obtenerYMostrarListadoDeUsuarios() {
    try {
      const respuestaHttp = await fetch(`${URL_API_SERVIDOR}?action=list`);
      const cuerpoJson = await respuestaHttp.json();

      if (respuestaHttp.ok) {
        renderizarTablaDeUsuarios(cuerpoJson);
      } else {
        throw new Error(cuerpoJson.error || 'No fue posible obtener el listado.');
      }
    } catch (error) {
      mostrarMensajeDeEstado('error', error.message);
      console.error('Error list:', error);
    }
  }

  // --- Crear usuario ---
  formularioAltaUsuario.addEventListener('submit', async e => {
    e.preventDefault();
    const form = new FormData(formularioAltaUsuario);
    const nombre = form.get('nombre').trim();
    const email = form.get('email').trim();

    if (!nombre || !email) {
      mostrarMensajeDeEstado('error', 'Nombre y Email son obligatorios.');
      return;
    }

    try {
      const res = await fetch(`${URL_API_SERVIDOR}?action=create`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre, email })
      });

      const data = await res.json();

      if (!res.ok) {
        mostrarMensajeDeEstado('error', data.error || 'No se pudo crear el usuario.');
        return;
      }

      renderizarTablaDeUsuarios(data);
      formularioAltaUsuario.reset();
      mostrarMensajeDeEstado('ok', 'Usuario agregado correctamente.');
    } catch (e) {
      mostrarMensajeDeEstado('error', e.message);
    }
  });

  // --- Delegación de eventos para Editar y Eliminar ---
  nodoCuerpoTablaUsuarios.addEventListener('click', async e => {
    const btn = e.target.closest('button');
    if (!btn) return;
    const index = parseInt(btn.dataset.posicion, 10);
    if (isNaN(index)) return;

    // --- Eliminar ---
    if (btn.classList.contains('eliminar')) {
      if (!confirm('¿Seguro que deseas eliminar este usuario?')) return;
      try {
        const res = await fetch(`${URL_API_SERVIDOR}?action=delete`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ index })
        });
        const data = await res.json();
        if (!res.ok) {
          mostrarMensajeDeEstado('error', data.error || 'Error al eliminar.');
          return;
        }
        renderizarTablaDeUsuarios(data);
        mostrarMensajeDeEstado('ok', 'Usuario eliminado correctamente.');
      } catch (e) {
        mostrarMensajeDeEstado('error', e.message);
      }
    }

    // --- Editar ---
    if (btn.classList.contains('editar')) {
      abrirModalEdicion(index);
    }
  });

  // --- Abrir modal edición ---
  function abrirModalEdicion(index) {
    const usuario = listaUsuarios[index];
    if (!usuario) return;
    editIndex.value = index;
    editNombre.value = usuario.nombre;
    editEmail.value = usuario.email;
    modalEditar.classList.remove('hidden');
  }

  // --- Guardar cambios (editar) ---
  formEditarUsuario.addEventListener('submit', async e => {
    e.preventDefault();
    const index = editIndex.value;
    const nombre = editNombre.value.trim();
    const email = editEmail.value.trim();

    try {
      const res = await fetch(`${URL_API_SERVIDOR}?action=update`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ index, nombre, email })
      });
      const data = await res.json();

      if (!res.ok) {
        mostrarMensajeDeEstado('error', data.error || 'Error al actualizar.');
        return;
      }

      renderizarTablaDeUsuarios(data);
      modalEditar.classList.add('hidden');
      mostrarMensajeDeEstado('ok', 'Usuario actualizado correctamente.');
    } catch (e) {
      mostrarMensajeDeEstado('error', e.message);
    }
  });

  obtenerYMostrarListadoDeUsuarios();
});
