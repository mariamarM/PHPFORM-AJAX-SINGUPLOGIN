document.addEventListener('DOMContentLoaded', () => {
  const URL_API_SERVIDOR = 'auth/api.php';
  let listaUsuarios = [];

  // Elementos del DOM
  const nodoCuerpoTablaUsuarios = document.getElementById('usersTable');
  const formularioAltaUsuario = document.getElementById('userForm');
  const nodoZonaMensajesEstado = document.getElementById('msg');
  const formEditarUsuario = document.getElementById('formEditarUsuario');
  const editIndex = document.getElementById('editIndex');
  const editNombre = document.getElementById('editNombre');
  const editEmail = document.getElementById('editEmail');
  const btnVerUsuarios = document.getElementById('btnVerUsuarios');
  const modalEditar = document.getElementById('modalEditar');
  const cancelarEdicion = document.getElementById('cancelarEdicion');

  function mostrarMensajeDeEstado(tipo, texto) {
    nodoZonaMensajesEstado.className = tipo;
    nodoZonaMensajesEstado.textContent = texto;
    if (tipo) {
      setTimeout(() => {
        nodoZonaMensajesEstado.className = '';
        nodoZonaMensajesEstado.textContent = '';
      }, 3000);
    }
  }

  function convertirATextoSeguro(txt) {
    if (!txt) return '';
    return String(txt)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#39;');
  }

  function renderizarTablaDeUsuarios(arrayUsuarios) {
    nodoCuerpoTablaUsuarios.innerHTML = '';

    if (!Array.isArray(arrayUsuarios) || arrayUsuarios.length === 0) {
      nodoCuerpoTablaUsuarios.innerHTML = `
        <tr><td colspan="4" class="text-center py-3 text-gray-500">No hay usuarios registrados</td></tr>
      `;
      return;
    }

    listaUsuarios = arrayUsuarios;

    arrayUsuarios.forEach((usuario, i) => {
      const fila = document.createElement('tr');
      fila.innerHTML = `
        <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700">${convertirATextoSeguro(usuario.nombre)}</td>
        <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700">${convertirATextoSeguro(usuario.email)}</td>
        <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700">${convertirATextoSeguro(usuario.rol || 'usuario')}</td>
        <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700 flex gap-2">
          <button data-posicion="${i}" class="editar px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-500">Editar</button>
          <button data-posicion="${i}" class="eliminar px-3 py-1 bg-red-600 text-white rounded hover:bg-red-500">Eliminar</button>
        </td>
      `;
      nodoCuerpoTablaUsuarios.appendChild(fila);
    });
  }

  async function obtenerYMostrarListadoDeUsuarios() {
    try {
      const respuestaHttp = await fetch(`${URL_API_SERVIDOR}?action=list`, {
        credentials: 'include'
      });

      if (!respuestaHttp.ok) {
        throw new Error(`Error HTTP: ${respuestaHttp.status}`);
      }

      const textoRespuesta = await respuestaHttp.text();
      console.log('Respuesta RAW:', textoRespuesta);

      let cuerpoJson;
      try {
        cuerpoJson = JSON.parse(textoRespuesta);
      } catch (e) {
        console.error('Error parseando JSON:', e);
        throw new Error('La respuesta no es JSON válido: ' + textoRespuesta.substring(0, 100));
      }

      if (cuerpoJson.error) {
        throw new Error(cuerpoJson.error);
      }

      let usuarios = [];
      if (Array.isArray(cuerpoJson)) {
        usuarios = cuerpoJson;
      } else if (Array.isArray(cuerpoJson.data)) {
        usuarios = cuerpoJson.data;
      } else if (cuerpoJson.ok) {
        usuarios = cuerpoJson.data || [];
      } else {
        throw new Error('Formato de respuesta no reconocido');
      }

      renderizarTablaDeUsuarios(usuarios);
      mostrarMensajeDeEstado('ok', `Usuarios cargados: ${usuarios.length}`);

    } catch (error) {
      console.error('Error al cargar usuarios:', error);
      mostrarMensajeDeEstado('error', `Error: ${error.message}`);
    }
  }

  // Event listener para el botón "Ver Usuarios"
  btnVerUsuarios.addEventListener('click', obtenerYMostrarListadoDeUsuarios);

  // --- Create - Versión mejorada con mejor manejo de errores
  formularioAltaUsuario.addEventListener('submit', async e => {
    e.preventDefault();
    const form = new FormData(formularioAltaUsuario);
    const nombre = form.get('nombre')?.trim() || '';
    const email = form.get('email')?.trim() || '';
    const password = form.get('password')?.trim() || 'password123';
    const rol = form.get('rol')?.trim() || 'usuario';

    if (!nombre || !email) {
      mostrarMensajeDeEstado('error', 'Nombre y Email son obligatorios.');
      return;
    }

    try {
      console.log('Enviando datos a la API...');
      
      const res = await fetch(`${URL_API_SERVIDOR}?action=create`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify({ 
          nombre, 
          email, 
          password, 
          rol 
        })
      });

      // Primero obtener el texto de la respuesta
      const textoRespuesta = await res.text();
      console.log('Respuesta RAW:', textoRespuesta);

      let data;
      try {
        data = JSON.parse(textoRespuesta);
      } catch (parseError) {
        console.error('Error parseando JSON:', parseError);
        throw new Error(`El servidor devolvió HTML en lugar de JSON. Posible error PHP:\n${textoRespuesta.substring(0, 200)}`);
      }

      if (!res.ok || data.ok === false) {
        mostrarMensajeDeEstado('error', data.error || 'No se pudo crear el usuario.');
        return;
      }

      // Recargar la lista después de crear
      await obtenerYMostrarListadoDeUsuarios();
      formularioAltaUsuario.reset();
      mostrarMensajeDeEstado('ok', 'Usuario agregado correctamente.');
      
    } catch (e) {
      console.error('Error completo al crear usuario:', e);
      mostrarMensajeDeEstado('error', e.message);
    }
  });

  // ... (el resto del código se mantiene igual)
  nodoCuerpoTablaUsuarios.addEventListener('click', async e => {
    const btn = e.target.closest('button');
    if (!btn) return;
    const index = parseInt(btn.dataset.posicion, 10);
    if (isNaN(index)) return;

    if (btn.classList.contains('eliminar')) {
      if (!confirm('¿Seguro que deseas eliminar este usuario?')) return;
      
      try {
        const usuario = listaUsuarios[index];
        if (!usuario || !usuario.id) {
          throw new Error('No se pudo identificar el usuario a eliminar');
        }

        const res = await fetch(`${URL_API_SERVIDOR}?action=delete`, {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json'
          },
          credentials: 'include',
          body: JSON.stringify({ 
            id: usuario.id
          })
        });

        const textoRespuesta = await res.text();
        console.log('Respuesta eliminar RAW:', textoRespuesta);

        let data;
        try {
          data = JSON.parse(textoRespuesta);
        } catch (e) {
          throw new Error(`Respuesta no válida: ${textoRespuesta.substring(0, 100)}`);
        }

        if (!res.ok || data.ok === false) {
          mostrarMensajeDeEstado('error', data.error || 'Error al eliminar.');
          return;
        }

        await obtenerYMostrarListadoDeUsuarios();
        mostrarMensajeDeEstado('ok', 'Usuario eliminado correctamente.');
      } catch (e) {
        console.error('Error al eliminar usuario:', e);
        mostrarMensajeDeEstado('error', e.message);
      }
    }

    if (btn.classList.contains('editar')) {
      abrirModalEdicion(index);
    }
  });

  function abrirModalEdicion(index) {
    const usuario = listaUsuarios[index];
    if (!usuario) {
      mostrarMensajeDeEstado('error', 'Usuario no encontrado');
      return;
    }
    
    editIndex.value = index;
    editNombre.value = usuario.nombre || '';
    editEmail.value = usuario.email || '';
    modalEditar.classList.remove('hidden');
  }

  formEditarUsuario.addEventListener('submit', async e => {
    e.preventDefault();
    const index = parseInt(editIndex.value, 10);
    const nombre = editNombre.value.trim();
    const email = editEmail.value.trim();

    if (isNaN(index)) {
      mostrarMensajeDeEstado('error', 'Índice de usuario inválido');
      return;
    }

    const usuarioOriginal = listaUsuarios[index];
    if (!usuarioOriginal) {
      mostrarMensajeDeEstado('error', 'Usuario no encontrado');
      return;
    }

    try {
      const res = await fetch(`${URL_API_SERVIDOR}?action=update`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify({ 
          id: usuarioOriginal.id,
          nombre: nombre, 
          email: email 
        })
      });

      const textoRespuesta = await res.text();
      console.log('Respuesta actualizar RAW:', textoRespuesta);

      let data;
      try {
        data = JSON.parse(textoRespuesta);
      } catch (e) {
        throw new Error(`Respuesta no válida: ${textoRespuesta.substring(0, 100)}`);
      }

      if (!res.ok || data.ok === false) {
        mostrarMensajeDeEstado('error', data.error || 'Error al actualizar.');
        return;
      }

      await obtenerYMostrarListadoDeUsuarios();
      modalEditar.classList.add('hidden');
      mostrarMensajeDeEstado('ok', 'Usuario actualizado correctamente.');
    } catch (e) {
      console.error('Error al actualizar usuario:', e);
      mostrarMensajeDeEstado('error', e.message);
    }
  });

  // Manejo del modal
  cancelarEdicion.addEventListener('click', () => {
    modalEditar.classList.add('hidden');
  });

  window.logout = function () {
    if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
      fetch("auth/logout.php", {
        method: 'POST',
        credentials: 'include'
      })
        .then(r => r.json())
        .then(data => {
          if (data.ok || data.success) {
            window.location.href = "auth/login.php";
          } else {
            alert('Error al cerrar sesión: ' + (data.error || 'Error desconocido'));
          }
        })
        .catch(err => {
          console.error("Error al cerrar sesión:", err);
          alert('Error de conexión al cerrar sesión');
        });
    }
  };

  // Cargar usuarios al iniciar
  obtenerYMostrarListadoDeUsuarios();
});